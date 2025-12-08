<div wire:poll.{{ $pollingInterval }}ms>
    <x-flash-message />

    <div class="p-4 relative">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Local</h2>
            
            <div class="flex items-center gap-2">
                {{-- Botão Filtros --}}
                <button 
                    wire:click="toggleFilters"
                    class="flex items-center gap-1 px-3 py-1.5 border-2 rounded-lg text-sm font-medium transition
                        {{ $filterTableStatus || $filterCheckStatus || !empty($filterOrderStatuses) ? 'border-orange-500 bg-orange-50 text-orange-700' : 'border-gray-300 hover:border-gray-400 text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtros
                    @if($filterTableStatus || $filterCheckStatus || !empty($filterOrderStatuses))
                        <span class="ml-1 px-1.5 py-0.5 bg-orange-500 text-white rounded-full text-xs">!</span>
                    @endif
                </button>
                
                <button 
                    wire:click="openNewTableModal"
                    class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg text-sm font-medium hover:shadow-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Criar Novo
                </button>
            </div>
        </div>
        
        {{-- Dropdown de Filtros --}}
        @if($showFilters)
            <div class="absolute top-16 left-4 right-4 lg:left-4 lg:right-auto lg:w-80 bg-white rounded-lg shadow-xl border-2 border-gray-200 z-50 p-4">
                {{-- Filtro Status da Mesa --}}
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Status da Mesa</h3>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="setTableStatusFilter('free')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterTableStatus === 'free' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Livre
                        </button>
                        <button wire:click="setTableStatusFilter('occupied')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterTableStatus === 'occupied' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Ocupada
                        </button>
                        <button wire:click="setTableStatusFilter('reserved')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterTableStatus === 'reserved' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Reservada
                        </button>
                        <button wire:click="setTableStatusFilter('releasing')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterTableStatus === 'releasing' ? 'bg-teal-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Liberando
                        </button>
                        <button wire:click="setTableStatusFilter('close')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterTableStatus === 'close' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Fechada
                        </button>
                    </div>
                </div>
                
                {{-- Filtro Status do Check --}}
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Status do Check</h3>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="setCheckStatusFilter('Open')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterCheckStatus === 'Open' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Aberto
                        </button>
                        <button wire:click="setCheckStatusFilter('Closing')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterCheckStatus === 'Closing' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Fechando
                        </button>
                        <button wire:click="setCheckStatusFilter('Closed')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterCheckStatus === 'Closed' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Fechado
                        </button>
                        <button wire:click="setCheckStatusFilter('Paid')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterCheckStatus === 'Paid' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Pago
                        </button>
                    </div>
                </div>
                
                {{-- Filtro Status dos Pedidos --}}
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Status dos Pedidos</h3>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="toggleOrderStatusFilter('pending')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ in_array('pending', $filterOrderStatuses) ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Aguardando
                        </button>
                        <button wire:click="toggleOrderStatusFilter('in_production')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ in_array('in_production', $filterOrderStatuses) ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Em Preparo
                        </button>
                        
                        {{-- Em transito --}}
                        <button wire:click="toggleOrderStatusFilter('in_transit')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ in_array('in_transit', $filterOrderStatuses) ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Em Trânsito
                        </button>

                        <button wire:click="toggleOrderStatusFilter('completed')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ in_array('completed', $filterOrderStatuses) ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Pronto
                        </button>
                    </div>
                </div>
                
                {{-- Botões de Ação --}}
                <div class="flex gap-2 pt-3 border-t">
                    <button wire:click="clearFilters" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Limpar
                    </button>
                    <button wire:click="toggleFilters" class="flex-1 px-3 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 transition">
                        Aplicar
                    </button>
                </div>
            </div>
        @endif
        
        {{-- Grid de Locais - Responsivo --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            @foreach($tables as $table)
                <x-table-card :table="$table" />
            @endforeach
        </div>
    </div>

    {{-- Modal Criar Novo Local --}}
    @if($showNewTableModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeNewTableModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Criar Novo Local</h3>
                    <button wire:click="closeNewTableModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                        <input 
                            type="number" 
                            wire:model="newTableNumber"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="Ex: 1">
                        @error('newTableNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <input 
                            type="text" 
                            wire:model="newTableName"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="Ex: Varanda">
                        @error('newTableName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex gap-3 pt-2">
                        <button 
                            wire:click="closeNewTableModal"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button 
                            wire:click="createNewTable"
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:shadow-lg transition">
                            Criar Local
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Alterar Status da Mesa --}}
    @if($showTableStatusModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeTableStatusModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Alterar Status da Mesa</h3>
                    <button wire:click="closeTableStatusModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Status da Mesa --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status da Mesa</label>
                        @if($hasActiveCheck)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">
                                <p class="text-sm text-yellow-800">
                                    <span class="font-semibold">⚠️ Atenção:</span> Não é possível alterar o status da mesa enquanto houver um check em andamento. Para alterar o status da mesa, finalize ou cancele o check primeiro.
                                </p>
                            </div>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            <button 
                                wire:click="$set('newTableStatus', 'free')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'free' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Livre
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'occupied')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'occupied' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Ocupada
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'reserved')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'reserved' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Reservada
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'releasing')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'releasing' ? 'bg-teal-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Liberando
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'close')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'close' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Fechada
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            • <strong>Livre:</strong> Disponível para uso<br>
                            • <strong>Ocupada:</strong> Em uso por clientes<br>
                            • <strong>Reservada:</strong> Reservada para clientes<br>
                            • <strong>Liberando:</strong> Aguardando limpeza/preparação<br>
                            • <strong>Fechada:</strong> Não aceita novos pedidos
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button 
                            wire:click="closeTableStatusModal"
                            class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                            Cancelar
                        </button>
                        <button 
                            wire:click="updateTableStatus"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg font-bold transition shadow-lg">
                            Salvar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
