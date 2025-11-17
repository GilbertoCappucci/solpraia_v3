<?php

namespace App\Livewire;

use App\Models\Device;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DeviceAuthorizationModal extends Component
{
    public $deviceToken = '';
    public $showModal = false;
    public $authorized = false;
    public $errorMessage = '';
    public $deviceName = '';
    public $expiresAt = null;
    public $canClose = false;

    protected $listeners = ['checkDeviceAuthorization' => 'checkAuthorization'];

    public function mount()
    {
        $this->checkAuthorization();
    }

    public function checkAuthorization()
    {
        // Verificar se há flag de autenticação pendente
        if (session('device_auth_required')) {
            $this->showModal = true;
        } else {
            $this->showModal = false;
        }
    }

    public function closeModal()
    {
        // Limpar localStorage
        $this->dispatch('clear-device-token');
        
        // Limpar sessão
        session()->invalidate();
        session()->regenerateToken();
        
        // Redirecionar para home
        return redirect()->route('home');
    }

    public function authorizeDevice()
    {
        $this->validate([
            'deviceToken' => 'required|string'
        ], [
            'deviceToken.required' => 'Por favor, digite o token de autorização',
        ]);

        $this->errorMessage = '';

        // Buscar device pelo token
        $device = Device::findByToken($this->deviceToken);

        if (!$device) {
            $this->errorMessage = 'Token inválido ou não encontrado';
            return;
        }

        // Verificar se device está válido (ativo e não expirado)
        if (!$device->isValid()) {
            if (!$device->active) {
                $this->errorMessage = 'Este device está inativo';
            } else {
                $this->errorMessage = 'Token expirado';
            }
            return;
        }

        // Gerar fingerprint do device atual
        $fingerprint = $this->generateFingerprint();

        // Validar fingerprint
        if (!$device->validateFingerprint($fingerprint)) {
            $this->errorMessage = 'Este token já está vinculado a outro dispositivo';
            return;
        }

        // Se é o primeiro uso, registrar fingerprint
        if (!$device->device_fingerprint) {
            $device->registerFingerprint($fingerprint);
        }

        // Atualizar uso
        $device->updateUsage(request()->ip());

        // Armazenar token na sessão
        session([
            'device_token' => $this->deviceToken,
            'device_token_validated' => true,
            'device_info' => [
                'id' => $device->id,
                'nickname' => $device->nickname,
                'fingerprint' => $device->device_fingerprint,
                'expires_at' => $device->expires_at?->format('Y-m-d H:i:s'),
            ]
        ]);

        // Remover flag de autenticação pendente
        session()->forget('device_auth_required');

        // Mostrar sucesso
        $this->authorized = true;
        $this->deviceName = $device->nickname;
        $this->expiresAt = $device->expires_at;
        $this->showModal = false;

        // Enviar dados para salvar no localStorage via JavaScript
        $this->dispatch('save-device-token', [
            'token' => $this->deviceToken,
            'device_name' => $device->nickname,
            'expires_at' => $device->expires_at?->toISOString(),
            'device_id' => $device->id,
            'fingerprint' => $fingerprint
        ]);

        // Recarregar página após 1.5 segundos
        $this->dispatch('device-authorized');
    }

    private function generateFingerprint(): string
    {
        $components = [
            request()->userAgent(),                      // Navegador + OS + versões
            request()->header('Accept-Language'),        // Idioma configurado
            request()->header('Accept-Encoding'),        // Encodings suportados
            request()->header('Accept'),                 // MIME types aceitos
            request()->header('DNT'),                    // Do Not Track
            request()->header('Sec-Ch-Ua-Platform'),     // Platform do Client Hints
            request()->header('Sec-Ch-Ua-Mobile'),       // Se é mobile
        ];

        return hash('sha256', implode('|', array_filter($components)));
    }

    public function render()
    {
        return view('livewire.device-authorization-modal');
    }
}
