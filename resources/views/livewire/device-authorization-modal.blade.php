<div>
    @if($showModal)
        {{-- Overlay --}}
        <div class="fixed inset-0 z-[9999] overflow-y-auto" style="backdrop-filter: blur(4px);">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/70 transition-opacity"></div>
            
            {{-- Modal Container --}}
            <div class="flex min-h-full items-center justify-center p-4">
                {{-- Modal Content --}}
                <div class="relative w-full max-w-md transform rounded-2xl bg-white shadow-2xl transition-all">
                    @if(!$authorized)
                        {{-- Header --}}
                        <div class="bg-gradient-to-r from-red-500 to-orange-600 text-white p-8 text-center rounded-t-2xl relative">
                            {{-- Botão Fechar --}}
                            <button 
                                wire:click="closeModal" 
                                type="button"
                                class="absolute top-4 right-4 text-white/80 hover:text-white hover:bg-white/20 rounded-full p-2 transition-all"
                                title="Fechar e sair"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            
                            <div class="text-5xl mb-3">🔐</div>
                            <h2 class="text-2xl font-bold mb-2">Autorização Necessária</h2>
                            <p class="text-sm opacity-90">Este dispositivo precisa ser autorizado</p>
                        </div>

                        {{-- Body --}}
                        <div class="p-8">
                            @if($errorMessage)
                                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded animate-shake">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>{{ $errorMessage }}</span>
                                    </div>
                                </div>
                            @endif

                            <form wire:submit.prevent="authorizeDevice">
                                <div class="mb-6">
                                    <label for="deviceToken" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Token de Autorização
                                    </label>
                                    <input 
                                        type="text" 
                                        id="deviceToken"
                                        wire:model.defer="deviceToken"
                                        placeholder="Digite o token do administrador"
                                        class="w-full px-4 py-3 border-2 rounded-lg focus:border-purple-500 focus:ring focus:ring-purple-200 transition-all @error('deviceToken') border-red-500 @else border-gray-300 @enderror"
                                        autofocus
                                    >
                                    @error('deviceToken')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                    <p class="text-gray-500 text-sm mt-2">
                                        💡 Solicite ao administrador do sistema
                                    </p>
                                </div>

                                <button 
                                    type="submit" 
                                    wire:loading.attr="disabled"
                                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-purple-700 hover:to-indigo-700 transform hover:-translate-y-0.5 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span wire:loading.remove>Autorizar Dispositivo</span>
                                    <span wire:loading class="flex items-center justify-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Autorizando...
                                    </span>
                                </button>
                            </form>

                            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>• Token único para cada dispositivo</li>
                                    <li>• Salvo automaticamente na sessão</li>
                                    <li>• Pode ter data de expiração</li>
                                </ul>
                            </div>

                            <div class="mt-4 p-3 bg-amber-50 border-l-4 border-amber-500 rounded">
                                <p class="text-sm text-amber-800">
                                    <strong>⚠️ Atenção:</strong> Fechar este modal sem autorizar fará logout do sistema.
                                </p>
                            </div>
                        </div>
                    @else
                        {{-- Mensagem de Sucesso --}}
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-8 text-center rounded-t-2xl">
                            <div class="text-5xl mb-3">✅</div>
                            <h2 class="text-2xl font-bold mb-2">Dispositivo Autorizado!</h2>
                            <p class="text-sm opacity-90">Seu dispositivo foi autorizado com sucesso</p>
                        </div>

                        <div class="p-8 text-center">
                            <div class="mb-4">
                                <p class="text-lg mb-2">
                                    <strong class="text-gray-700">Dispositivo:</strong> 
                                    <span class="text-gray-900">{{ $deviceName }}</span>
                                </p>
                                @if($expiresAt)
                                    <p class="text-gray-600">
                                        <strong>Válido até:</strong> {{ $expiresAt->format('d/m/Y') }}
                                    </p>
                                @else
                                    <p class="text-gray-600">
                                        <strong>Validade:</strong> Permanente
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center justify-center text-green-600 font-semibold">
                                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Recarregando...
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Estilos de animação --}}
        <style>
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }

            .animate-shake {
                animation: shake 0.5s ease-in-out;
            }
        </style>
    @endif
</div>

{{-- Script para gerenciar localStorage (FORA do @if para sempre executar) --}}
<script>
    console.log('=== Script de Device Authorization carregado ===');
    
    // Listener para salvar token no localStorage
    document.addEventListener('livewire:initialized', () => {
        console.log('Livewire inicializado');
        
        Livewire.on('save-device-token', (data) => {
            console.log('Evento save-device-token recebido:', data);
            const tokenData = data[0];
            
            // Salvar no localStorage
            const dataToStore = {
                token: tokenData.token,
                device_name: tokenData.device_name,
                expires_at: tokenData.expires_at,
                device_id: tokenData.device_id,
                fingerprint: tokenData.fingerprint,
                stored_at: new Date().toISOString()
            };
            
            localStorage.setItem('restaurant_device_token', JSON.stringify(dataToStore));
            console.log('✅ Token salvo no localStorage:', tokenData.token);
            console.log('Dados completos:', dataToStore);
        });

        // Listener para limpar token do localStorage
        Livewire.on('clear-device-token', () => {
            console.log('Evento clear-device-token recebido');
            localStorage.removeItem('restaurant_device_token');
            console.log('✅ Token removido do localStorage');
        });
    });

    // Verificar localStorage ao carregar e sincronizar com cookie
    (function() {
        console.log('Verificando localStorage...');
        const storedData = localStorage.getItem('restaurant_device_token');
        
        if (storedData) {
            alert('Token encontrado no localStorage.');
            console.log('Token encontrado no localStorage');
            
            try {
                const data = JSON.parse(storedData);
                console.log('Dados do token:', data);
                alert('Token encontrado: ' + data.token);
                
                // Verificar se não expirou
                if (data.expires_at) {
                    const expiryDate = new Date(data.expires_at);
                    console.log('Data de expiração:', expiryDate);
                    console.log('Data atual:', new Date());
                    alert('Data de expiração: ' + expiryDate + '\nData atual: ' + new Date());
                    if (expiryDate < new Date()) {
                        console.warn('⚠️ Token do localStorage expirado, removendo...');
                        alert('Token expirado, removendo do localStorage.');
                        localStorage.removeItem('restaurant_device_token');
                        document.cookie = 'device_token_ls=; path=/; max-age=0';
                        return;
                    }
                }
                
                // Salvar token em um cookie para o servidor poder ler
                if (data.token) {
                    document.cookie = `device_token_ls=${data.token}; path=/; max-age=86400; SameSite=Lax`;
                    console.log('✅ Token do localStorage salvo em cookie:', data.token);
                    alert('Token salvo em cookie: ' + data.token);
                }
            } catch (error) {
                alert('Erro ao processar token do localStorage: ' + error);
                console.error('❌ Erro ao processar token do localStorage:', error);
                localStorage.removeItem('restaurant_device_token');
                document.cookie = 'device_token_ls=; path=/; max-age=0';
            }
        } else {
            alert('Nenhum token encontrado no localStorage.');
            console.log('Nenhum token no localStorage');
        }
    })();
</script>

{{-- Script para recarregar após sucesso --}}
@if($authorized)
    <script>
        console.log('Token autorizado! Recarregando em 1.5 segundos...');
        alert('Token autorizado! Recarregando em 1.5 segundos...');
        setTimeout(function() {
            window.location.reload();
        }, 1500);
    </script>
@endif
