
<div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-3 flex items-center justify-between sticky top-0 z-10 shadow-md">
    <div class="flex items-center gap-2">
        <h2 class="text-1xl font-bold">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectionMode): ?>
                Selecionar Locais
            <?php else: ?>
                <?php echo e($title); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </h2>
    </div>
    
    <div class="flex items-center gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectionMode): ?>
            
            <button 
                wire:click="cancelSelection"
                class="flex items-center gap-2 px-3 py-1.5 bg-gray-600/50 hover:bg-gray-700/50 border-2 border-white/30 text-white rounded-lg text-sm font-medium transition">
                Cancelar
            </button>
            <button 
                wire:click="openMergeModal"
                <?php if(count($selectedTables) < 2): ?> disabled <?php endif; ?>
                class="flex items-center gap-2 px-3 py-1.5 <?php echo e(count($selectedTables) >= 2 ? 'bg-green-500 hover:bg-green-600' : 'bg-white/20 hover:bg-white/30'); ?> border-2 border-white text-white rounded-lg text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                <?php if(count($selectedTables) >= 2): ?>
                    ✓ Finalizar União (<?php echo e(count($selectedTables)); ?>)
                <?php else: ?>
                    Unir (<?php echo e(count($selectedTables)); ?>)
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
        <?php else: ?>
            
            <button 
                wire:click="toggleSelectionMode"
                <?php if(!$canMerge): ?> disabled <?php endif; ?>
                class="flex items-center gap-2 px-3 py-1.5 border-2 border-white/30 bg-white/10 text-white hover:bg-white/20 rounded-lg text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Unir
            </button>

            <button 
                wire:click="toggleFilters"
                class="flex items-center gap-1 px-3 py-1.5 border-2 rounded-lg text-sm font-medium transition
                    <?php echo e($hasActiveFilters ? 'border-white bg-white/20 text-white' : 'border-white/30 bg-white/10 text-white hover:bg-white/20'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtros
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasActiveFilters): ?>
                    <span class="ml-1 px-1.5 py-0.5 bg-white text-orange-600 rounded-full text-xs font-bold">!</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
            
            <button 
                wire:click="openNewTableModal"
                class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 border-2 border-white/30 text-white rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH E:\Projects\solpraia\resources\views/livewire/table-header.blade.php ENDPATH**/ ?>