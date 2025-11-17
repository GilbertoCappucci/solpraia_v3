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

        // Gerar fingerprint do device atual
        $currentFingerprint = $this->generateDeviceFingerprint($request);

        // Validar fingerprint
        if (!$device->validateFingerprint($currentFingerprint)) {
            // Device diferente, token não pode ser usado
            session()->forget('device_token');
            session(['device_auth_required' => true]);
            return $next($request);
        }

        // Se é o primeiro uso, registrar fingerprint
        if (!$device->device_fingerprint) {
            $device->registerFingerprint($currentFingerprint);
        }

        // Token válido, salvar na sessão
        session([
            'device_token' => $token,
            'device_token_validated' => true,
            'device_info' => [
                'id' => $device->id,
                'nickname' => $device->nickname,
                'fingerprint' => $device->device_fingerprint,
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
     * Baseado em características do navegador/hardware (não usa IP por ser dinâmico)
     */
    protected function generateDeviceFingerprint(Request $request): string
    {
        $components = [
            $request->userAgent(),                      // Navegador + OS + versões
            $request->header('Accept-Language'),        // Idioma configurado
            $request->header('Accept-Encoding'),        // Encodings suportados
            $request->header('Accept'),                 // MIME types aceitos
            $request->header('DNT'),                    // Do Not Track
            $request->header('Sec-Ch-Ua-Platform'),     // Platform do Client Hints
            $request->header('Sec-Ch-Ua-Mobile'),       // Se é mobile
        ];

        return hash('sha256', implode('|', array_filter($components)));
    }
}
