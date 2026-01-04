<div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFilters): ?>
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40" wire:click="toggleFilters"></div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFilters): ?>
    <div class="fixed top-2 left-1/2 -translate-x-1/2 w-[95%] max-w-2xl max-h-[calc(100vh-2.5rem)] overflow-y-auto bg-white rounded-2xl shadow-2xl border-2 border-gray-300 z-50">
        
        
        <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-red-500 text-white px-4 py-3 rounded-t-2xl shadow-lg z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <h3 class="text-lg font-bold">Filtros</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="clearFilters" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Limpar
                    </button>
                    <button wire:click="toggleFilters" class="p-1 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-3 space-y-2">
            
            <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <h3 class="text-sm font-bold text-gray-800">Modo de Filtragem</h3>
                    </div>
                    <button wire:click="toggleGlobalFilterMode" 
                        class="relative inline-flex h-8 w-16 items-center rounded-full transition-colors duration-200 focus:outline-none shadow-inner border-2
                            <?php echo e($globalFilterMode === 'AND' ? 'bg-gray-700 border-gray-800' : 'bg-gray-300 border-gray-400'); ?>">
                        <span class="inline-block h-6 w-6 transform rounded-full bg-white shadow-md transition-transform duration-200 
                            <?php echo e($globalFilterMode === 'AND' ? 'translate-x-9' : 'translate-x-1'); ?>">
                        </span>
                    </button>
                </div>
            </div>

            
            <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-800">Status da Mesa</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="toggleTableStatusFilter('free')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('free', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Livre
                    </button>
                    <button wire:click="toggleTableStatusFilter('occupied')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('occupied', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Ocupada
                    </button>
                    <button wire:click="toggleTableStatusFilter('reserved')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('reserved', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Reservada
                    </button>
                    <button wire:click="toggleTableStatusFilter('releasing')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('releasing', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Liberando
                    </button>
                    <button wire:click="toggleTableStatusFilter('close')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('close', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Fechada
                    </button>
                </div>
            </div>
            
            
            <div class="bg-gray-100 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-800">Status do Check</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="toggleCheckStatusFilter('Open')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('Open', $filterCheckStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Aberto
                    </button>
                    <button wire:click="toggleCheckStatusFilter('Closed')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('Closed', $filterCheckStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Fechado
                    </button>
                    <button wire:click="toggleCheckStatusFilter('Paid')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('Paid', $filterCheckStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Pago
                    </button>
                    <button wire:click="toggleCheckStatusFilter('delayed_closed')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('delayed_closed', $filterCheckStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Atrasado
                    </button>
                </div>
            </div>
            
            
            <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-800">Status dos Pedidos</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="toggleOrderStatusFilter('pending')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('pending', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Aguardando
                    </button>
                    <button wire:click="toggleOrderStatusFilter('in_production')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('in_production', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Em Preparo
                    </button>
                    <button wire:click="toggleOrderStatusFilter('in_transit')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('in_transit', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Em Trânsito
                    </button>
                    <button wire:click="toggleOrderStatusFilter('completed')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('completed', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Pronto
                    </button>
                    <button wire:click="toggleOrderStatusFilter('delayed')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('delayed', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Atrasado
                    </button>
                </div>
            </div>

            
            <div class="bg-gray-100 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-800">Departamentos</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="toggleDepartamentFilter('Administration')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('Administration', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Administração
                    </button>
                    <button wire:click="toggleDepartamentFilter('Expedition')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('Expedition', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Expedição
                    </button>
                    <button wire:click="toggleDepartamentFilter('Bar')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('Bar', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Bar
                    </button>
                    <button wire:click="toggleDepartamentFilter('Kitchen')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('Kitchen', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Cozinha
                    </button>
                    <button wire:click="toggleDepartamentFilter('Finance')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('Finance', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Financeiro
                    </button>
                    <button wire:click="toggleDepartamentFilter('Service')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            <?php echo e(in_array('Service', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Atendimento
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH E:\Projects\solpraia\resources\views/livewire/table-filters.blade.php ENDPATH**/ ?>