<div class="min-h-screen bg-gray-50">
    <x-flash-message />

    {{-- Header --}}
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-4 flex items-center justify-between sticky top-0 z-10 shadow-md">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Configurações
        </h2>
    </div>

    {{-- Container Principal --}}
    <div class="max-w-4xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            
            {{-- Tabs de Navegação --}}
            <div class="flex border-b border-gray-200">
                <button 
                    wire:click="$set('activeTab', 'alerts')"
                    class="flex-1 py-4 px-6 font-semibold text-sm md:text-base transition border-b-4 {{ $activeTab === 'alerts' ? 'text-orange-600 border-orange-600 bg-orange-50' : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50' }}">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span>Alertas</span>
                    </div>
                </button>
                <button 
                    wire:click="$set('activeTab', 'display')"
                    class="flex-1 py-4 px-6 font-semibold text-sm md:text-base transition border-b-4 {{ $activeTab === 'display' ? 'text-orange-600 border-orange-600 bg-orange-50' : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50' }}">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>Visualização</span>
                    </div>
                </button>
            </div>

            {{-- Conteúdo da Aba Alertas --}}
            @if($activeTab === 'alerts')
            <div class="p-6 md:p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Tempos Limite para Alertas</h3>
                    <p class="text-sm text-gray-600">Configure quando os alertas visuais devem aparecer para pedidos e mesas</p>
                </div>

                <div class="space-y-8">
                    {{-- Pedido Pendente --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="text-base font-semibold text-gray-700">Pedido Pendente</label>
                                <p class="text-xs text-gray-500 mt-0.5">Aguardando início do preparo</p>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">{{ $timeLimitPending }} min</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="timeLimitPending"
                            min="1"
                            max="120"
                            class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-blue">
                    </div>

                    {{-- Pedido Em Produção --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="text-base font-semibold text-gray-700">Pedido Em Produção</label>
                                <p class="text-xs text-gray-500 mt-0.5">Em preparo na cozinha</p>
                            </div>
                            <span class="text-2xl font-bold text-yellow-600">{{ $timeLimitInProduction }} min</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="timeLimitInProduction"
                            min="1"
                            max="120"
                            class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-yellow">
                    </div>

                    {{-- Pedido Em Trânsito --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="text-base font-semibold text-gray-700">Pedido Em Trânsito</label>
                                <p class="text-xs text-gray-500 mt-0.5">A caminho da mesa</p>
                            </div>
                            <span class="text-2xl font-bold text-purple-600">{{ $timeLimitInTransit }} min</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="timeLimitInTransit"
                            min="1"
                            max="120"
                            class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-purple">
                    </div>

                    {{-- Check Fechado --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="text-base font-semibold text-gray-700">Check Fechado</label>
                                <p class="text-xs text-gray-500 mt-0.5">Aguardando pagamento</p>
                            </div>
                            <span class="text-2xl font-bold text-green-600">{{ $timeLimitClosed }} min</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="timeLimitClosed"
                            min="1"
                            max="120"
                            class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-green">
                    </div>

                    {{-- Mesa Liberando --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="text-base font-semibold text-gray-700">Mesa Liberando</label>
                                <p class="text-xs text-gray-500 mt-0.5">Aguardando limpeza</p>
                            </div>
                            <span class="text-2xl font-bold text-teal-600">{{ $timeLimitReleasing }} min</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="timeLimitReleasing"
                            min="1"
                            max="120"
                            class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-teal">
                    </div>
                </div>

                {{-- Botão Salvar --}}
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button 
                        wire:click="saveSettings"
                        class="w-full px-6 py-4 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg font-bold text-lg transition shadow-lg hover:shadow-xl">
                        Salvar Configurações
                    </button>
                </div>
            </div>
            @endif

            {{-- Conteúdo da Aba Visualização --}}
            @if($activeTab === 'display')
            <div class="p-6 md:p-8">
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <p class="font-semibold text-xl text-gray-700 mb-2">Configurações de Visualização</p>
                    <p class="text-sm">Em breve: Opções de layout, tamanho dos cards,<br>modo escuro e muito mais...</p>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
