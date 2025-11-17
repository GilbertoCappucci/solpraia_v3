<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDeviceToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se usuário está autenticado via sessão (Admin), permitir acesso
        if ($request->user() && $request->user()->isAdmin()) {
            return $next($request);
        }

        // Tentar obter token do localStorage via cookie
        $token = $request->cookie('device_token_ls');

        // Se não tiver cookie, verificar se tem na sessão
        if (!$token) {
            $token = session('device_token');
        }

        // Se não tem token, marcar para exibir modal
        if (!$token) {
            session(['device_auth_required' => true]);
            return $next($request);
        }

        // Buscar device pelo token
        $device = Device::findByToken($token);

        if (!$device) {
            // Token inválido, limpar e solicitar novo
            session()->forget('device_token');
            session(['device_auth_required' => true]);
            return $next($request);
        }

        // Verificar se device está válido (ativo e não expirado)
        if (!$device->isValid()) {
            session()->forget('device_token');
            session(['device_auth_required' => true]);
            return $next($request);
        }

        // Gerar fingerprint do device atual (baseado nos headers ATUAIS da requisição)
        $currentFingerprint = $this->generateDeviceFingerprint($request);
        
        //dd($device, $currentFingerprint);
        
        // Validar fingerprint do banco vs fingerprint atual
        if (!$device->validateFingerprint($currentFingerprint)) {
            // Device diferente, token não pode ser usado
            session()->forget(['device_token', 'device_token_validated', 'device_info']);
            session(['device_auth_required' => true]);
            cookie()->queue(cookie()->forget('device_token_ls'));
            return $next($request);
        }

        // Validação adicional: verificar se fingerprint da sessão também corresponde ao atual
        $sessionFingerprint = session('device_info.fingerprint');
        if ($sessionFingerprint && $sessionFingerprint !== $currentFingerprint) {
            // Fingerprint mudou desde a última validação - possível adulteração
            session()->forget(['device_token', 'device_token_validated', 'device_info']);
            session(['device_auth_required' => true]);
            cookie()->queue(cookie()->forget('device_token_ls'));
            return $next($request);
        }

        // Se é o primeiro uso, registrar fingerprint
        if (!$device->device_fingerprint) {
            $device->registerFingerprint($currentFingerprint);
        }

        // Token válido, salvar/atualizar na sessão com fingerprint atual
        session([
            'device_token' => $token,
            'device_token_validated' => true,
            'device_info' => [
                'id' => $device->id,
                'nickname' => $device->nickname,
                'fingerprint' => $currentFingerprint, // Sempre o fingerprint atual
                'last_used' => now(),
            ]
        ]);

        // Remover flag de autenticação pendente
        session()->forget('device_auth_required');

        // Renovar cookie
        cookie()->queue('device_token_ls', $token, 60 * 24 * 365); // 1 ano

        // Atualizar uso do device
        $device->updateUsage($request->ip());

        return $next($request);
    }

    /**
     * Gerar fingerprint único do device
     * Baseado apenas no User-Agent (mais estável entre requisições)
     */
    protected function generateDeviceFingerprint(Request $request): string
    {
        // Usar apenas User-Agent pois outros headers podem variar entre requisições
        // (Accept, Accept-Encoding mudam em AJAX vs HTML, por exemplo)
        return hash('sha256', $request->userAgent() ?? '');
    }
}
