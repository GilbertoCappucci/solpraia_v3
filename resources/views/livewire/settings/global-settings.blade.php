<section class="w-full">

    @include('partials.settings-heading')
    <x-settings.layout :heading="__('Global Settings')" :subheading="__('Manage your application global settings')">
        {{-- Saved Indicator --}}
        @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" x-transition
            class="fixed top-5 right-5 z-50 text-sm font-semibold bg-green-500/90 backdrop-blur-sm text-white rounded-full px-4 py-2">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Tabs de Navegação --}}
            <div class="flex border-b border-gray-200 overflow-x-auto no-scrollbar">
                <button
                    wire:click="$set('activeTab', 'alerts')"
                    class="shrink-0 flex-1 min-w-[120px] py-4 px-6 font-semibold text-sm md:text-base transition border-b-4 whitespace-nowrap {{ $activeTab === 'alerts' ? 'text-orange-600 border-orange-600 bg-orange-50' : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50' }}">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span>Alertas</span>
                    </div>
                </button>
                <button
                    wire:click="$set('activeTab', 'pix')"
                    class="shrink-0 flex-1 min-w-[120px] py-4 px-6 font-semibold text-sm md:text-base transition border-b-4 whitespace-nowrap {{ $activeTab === 'pix' ? 'text-orange-600 border-orange-600 bg-orange-50' : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50' }}">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>PIX</span>
                    </div>
                </button>

            </div>

            {{-- Conteúdo da Aba Alertas --}}
            @if($activeTab === 'alerts')
            <div class="p-6 md:p-8">
                <div class="space-y-4">
                    {{-- Pedido Pendente --}}
                    <div class="bg-gray-50 rounded-xl p-4 shadow-sm border-2 border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label class="text-sm font-bold text-gray-800">Pedido Pendente</label>
                                <p class="text-xs text-gray-500 mt-0.5">Aguardando início do preparo</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="$set('timeLimitPending', {{ max(1, $timeLimitPending - 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                                    </svg></button>
                                <div class="w-20 text-center">
                                    <input type="number" wire:model="timeLimitPending" min="1" max="120" class="w-full px-3 py-2 bg-white border-2 border-gray-300 rounded-lg text-center text-lg font-bold text-gray-800 focus:border-gray-400 focus:ring-0">
                                    <span class="text-xs text-gray-500">min</span>
                                    @error('timeLimitPending') <span class="block text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                                </div>
                                <button wire:click="$set('timeLimitPending', {{ min(120, $timeLimitPending + 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                                    </svg></button>
                            </div>
                        </div>
                    </div>

                    {{-- Pedido Em Produção --}}
                    <div class="bg-gray-50 rounded-xl p-4 shadow-sm border-2 border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label class="text-sm font-bold text-gray-800">Pedido Em Produção</label>
                                <p class="text-xs text-gray-500 mt-0.5">Em preparo na cozinha</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="$set('timeLimitInProduction', {{ max(1, $timeLimitInProduction - 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                                    </svg></button>
                                <div class="w-20 text-center">
                                    <input type="number" wire:model="timeLimitInProduction" min="1" max="120" class="w-full px-3 py-2 bg-white border-2 border-gray-300 rounded-lg text-center text-lg font-bold text-gray-800 focus:border-gray-400 focus:ring-0">
                                    <span class="text-xs text-gray-500">min</span>
                                    @error('timeLimitInProduction') <span class="block text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                                </div>
                                <button wire:click="$set('timeLimitInProduction', {{ min(120, $timeLimitInProduction + 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                                    </svg></button>
                            </div>
                        </div>
                    </div>

                    {{-- Pedido Em Trânsito --}}
                    <div class="bg-gray-50 rounded-xl p-4 shadow-sm border-2 border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label class="text-sm font-bold text-gray-800">Pedido Em Trânsito</label>
                                <p class="text-xs text-gray-500 mt-0.5">A caminho da mesa</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="$set('timeLimitInTransit', {{ max(1, $timeLimitInTransit - 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                                    </svg></button>
                                <div class="w-20 text-center">
                                    <input type="number" wire:model="timeLimitInTransit" min="1" max="120" class="w-full px-3 py-2 bg-white border-2 border-gray-300 rounded-lg text-center text-lg font-bold text-gray-800 focus:border-gray-400 focus:ring-0">
                                    <span class="text-xs text-gray-500">min</span>
                                    @error('timeLimitInTransit') <span class="block text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                                </div>
                                <button wire:click="$set('timeLimitInTransit', {{ min(120, $timeLimitInTransit + 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                                    </svg></button>
                            </div>
                        </div>
                    </div>

                    {{-- Check Fechado --}}
                    <div class="bg-gray-50 rounded-xl p-4 shadow-sm border-2 border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label class="text-sm font-bold text-gray-800">Check Fechado</label>
                                <p class="text-xs text-gray-500 mt-0.5">Aguardando pagamento</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="$set('timeLimitClosed', {{ max(1, $timeLimitClosed - 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                                    </svg></button>
                                <div class="w-20 text-center">
                                    <input type="number" wire:model="timeLimitClosed" min="1" max="120" class="w-full px-3 py-2 bg-white border-2 border-gray-300 rounded-lg text-center text-lg font-bold text-gray-800 focus:border-gray-400 focus:ring-0">
                                    <span class="text-xs text-gray-500">min</span>
                                    @error('timeLimitClosed') <span class="block text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                                </div>
                                <button wire:click="$set('timeLimitClosed', {{ min(120, $timeLimitClosed + 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                                    </svg></button>
                            </div>
                        </div>
                    </div>

                    {{-- Mesa Liberando --}}
                    <div class="bg-gray-50 rounded-xl p-4 shadow-sm border-2 border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label class="text-sm font-bold text-gray-800">Mesa Liberando</label>
                                <p class="text-xs text-gray-500 mt-0.5">Aguardando limpeza</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="$set('timeLimitReleasing', {{ max(1, $timeLimitReleasing - 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                                    </svg></button>
                                <div class="w-20 text-center">
                                    <input type="number" wire:model="timeLimitReleasing" min="1" max="120" class="w-full px-3 py-2 bg-white border-2 border-gray-300 rounded-lg text-center text-lg font-bold text-gray-800 focus:border-gray-400 focus:ring-0">
                                    <span class="text-xs text-gray-500">min</span>
                                    @error('timeLimitReleasing') <span class="block text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                                </div>
                                <button wire:click="$set('timeLimitReleasing', {{ min(120, $timeLimitReleasing + 1) }})" class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition shadow-sm active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                                    </svg></button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button wire:click="save" wire:loading.attr="disabled" class="w-full flex items-center justify-center gap-2 bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-xl transition duration-200 shadow-lg active:scale-95 disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">Salvar Alertas</span>
                            <span wire:loading wire:target="save">Salvando...</span>
                            <svg wire:loading.remove wire:target="save" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @if($activeTab === 'pix')
            <div class="p-6 md:p-8">
                <div class="space-y-4">
                    {{-- Tipo de Chave --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Chave</label>
                        <select wire:model="pixKeyType" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg text-gray-900 font-medium focus:border-orange-500 focus:ring-0">
                            <option default value="">Selecione</option>
                            <option value="CPF">CPF</option>
                            <option value="CNPJ">CNPJ</option>
                            <option value="PHONE">Telefone</option>
                            <option value="EMAIL">E-mail</option>
                            <option value="RANDOM">Chave Aleatória</option>
                        </select>
                        @error('pixKeyType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Chave PIX --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Chave PIX</label>
                        <input type="text" wire:model="pixKey" placeholder="Digite sua chave PIX" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg text-gray-900 font-medium placeholder-gray-400 focus:border-orange-500 focus:ring-0">
                        @error('pixKey') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Nome do Beneficiário --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nome do Beneficiário</label>
                        <input type="text" wire:model="pixName" placeholder="Nome que aparecerá no banco" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg text-gray-900 font-medium placeholder-gray-400 focus:border-orange-500 focus:ring-0">
                        @error('pixName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Cidade do Beneficiário --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Cidade do Beneficiário</label>
                        <input type="text" wire:model="pixCity" placeholder="Cidade da conta bancária" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg text-gray-900 font-medium placeholder-gray-400 focus:border-orange-500 focus:ring-0">
                        @error('pixCity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-6">
                        <button wire:click="save" wire:loading.attr="disabled" class="w-full flex items-center justify-center gap-2 bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-xl transition duration-200 shadow-lg active:scale-95 disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">Salvar Configurações PIX</span>
                            <span wire:loading wire:target="save">Salvando...</span>
                            <svg wire:loading.remove wire:target="save" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Conteúdo da Aba Visualização --}}
            @if($activeTab === 'display')
            <div class="p-6 md:p-8">
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <p class="font-semibold text-xl text-gray-700 mb-2">Configurações de Visualização</p>
                    <p class="text-sm">Em breve: Opções de layout, tamanho dos cards,<br>modo escuro e muito mais...</p>
                </div>
            </div>
            @endif
        </div>

    </x-settings.layout>
</section>