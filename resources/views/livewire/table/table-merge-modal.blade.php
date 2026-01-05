<div>
    {{-- MODAL UNIÃO DE MESAS --}}
    @if($showModal)
        {{-- Backdrop escuro --}}
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40" wire:click="closeModal"></div>
        
        {{-- Modal --}}
        <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[95%] max-w-md bg-white rounded-2xl shadow-2xl border-2 border-gray-300 z-50">
            
            {{-- Header do Modal --}}
            <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-red-500 text-white px-4 py-3 rounded-t-2xl shadow-lg z-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <h3 class="text-lg font-bold">Unir Mesas</h3>
                    </div>
                    <button 
                        wire:click="closeModal" 
                        class="p-1 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- Conteúdo do Modal --}}
            <div class="p-4">
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r-lg">
                        Selecione a mesa de <strong>destino</strong>. Todos os pedidos das outras mesas selecionadas serão movidos para ela.
                    </p>

                    <div class="space-y-2">
                        @foreach($selectedTablesData as $table)
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition {{ $mergeDestinationTableId == $table['id'] ? 'bg-orange-50 border-orange-500 ring-2 ring-orange-200' : 'border-gray-300 hover:border-orange-400' }}">
                                <input type="radio" wire:model.live="mergeDestinationTableId" value="{{ $table['id'] }}" class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                <span class="ml-3 font-medium text-gray-800">
                                    Mesa {{ $table['number'] }}
                                    @if($table['name']) ({{ $table['name'] }}) @endif
                                </span>
                                <span class="ml-auto text-sm">
                                    @if($table['checkId'])
                                        <span class="inline-flex items-center gap-1">
                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            <span class="font-medium text-green-700">Check</span>
                                            @if($table['checkTotal'] > 0)
                                                <span class="text-gray-500">• R$ {{ number_format($table['checkTotal'], 2, ',', '.') }}</span>
                                            @else
                                                <span class="text-gray-500">• R$ 0,00</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1">
                                            <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                            <span class="text-gray-500">Sem Check</span>
                                        </span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-r-lg">
                        <p class="text-xs text-yellow-800">
                            <strong>Atenção:</strong> Após a união, as mesas de origem serão liberadas e suas comandas serão transferidas para a mesa de destino. Esta ação não pode ser desfeita.
                        </p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button 
                            wire:click="closeModal"
                            class="flex-1 px-4 py-2.5 bg-white border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
                            Cancelar
                        </button>
                        <button 
                            wire:click="merge"
                            wire:loading.attr="disabled"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:shadow-lg font-medium transition transform hover:scale-105 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span wire:loading.remove>Confirmar União</span>
                            <span wire:loading>Processando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
