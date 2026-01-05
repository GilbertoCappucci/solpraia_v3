<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="close">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.stop>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Unir</h3>
            <button wire:click="close" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="space-y-4">
            <p class="text-sm text-gray-600">
                Selecione a mesa de <strong>destino</strong>. Todos os pedidos das outras mesas selecionadas serão movidos para ela.
            </p>

            <div class="space-y-2">
                @foreach($tables->whereIn('id', $selectedTables) as $table)
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer transition {{ $mergeDestinationTableId == $table->id ? 'bg-orange-50 border-orange-500 ring-2 ring-orange-200' : 'border-gray-300 hover:border-orange-400' }}">
                        <input type="radio" wire:model="mergeDestinationTableId" value="{{ $table->id }}" class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                        <span class="ml-3 font-medium text-gray-800">
                            Mesa {{ $table->number }}
                            @if($table->name) ({{ $table->name }}) @endif
                        </span>
                        <span class="ml-auto text-sm">
                            @if($table->checkId)
                                <span class="inline-flex items-center gap-1">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <span class="font-medium text-green-700">Check Aberto</span>
                                    @if($table->checkTotal > 0)
                                        <span class="text-gray-500">• R$ {{ number_format($table->checkTotal, 2, ',', '.') }}</span>
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

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mt-4 rounded-r-lg">
                <p class="text-xs text-yellow-800">
                    <strong>Atenção:</strong> Após a união, as mesas de origem serão liberadas e suas comandas atuais serão canceladas. Esta ação não pode ser desfeita.
                </p>
            </div>

            <div class="flex gap-3 pt-2">
                <button 
                    wire:click="close"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button 
                    wire:click="confirmMerge"
                    wire:loading.attr="disabled"
                    class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:shadow-lg transition disabled:opacity-70">
                    Confirmar União
                </button>
            </div>
        </div>
    </div>
</div>
