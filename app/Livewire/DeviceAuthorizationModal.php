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
    public $sessionInfo = '';

    protected $listeners = ['checkDeviceAuthorization' => 'checkAuthorization'];

    public function mount()
    {

        if(!session('device_token_validated') && session('device_auth_required') ) {
            $this->showModal = true;
        } else {
            $this->showModal = false;
        }

        $this->sessionInfo = json_encode(session()->all(), JSON_PRETTY_PRINT);
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
        // Usar apenas User-Agent pois outros headers podem variar entre requisições
        // (Accept, Accept-Encoding mudam em AJAX vs HTML, por exemplo)
        return hash('sha256', request()->userAgent() ?? '');
    }

    public function render()
    {
        return view('livewire.device-authorization-modal');
    }
}
