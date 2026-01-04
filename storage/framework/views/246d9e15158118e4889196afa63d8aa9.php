<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Alterar Status da Mesa</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status da Mesa</label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasActiveCheck): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">
                            <p class="text-sm text-yellow-800">
                                <span class="font-semibold">⚠️ Atenção:</span> Não é possível alterar o status da mesa enquanto houver um check em andamento. Para alterar o status da mesa, finalize ou cancele o check primeiro.
                            </p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            wire:click="setStatus('free')"
                            <?php if($hasActiveCheck): ?> disabled <?php endif; ?>
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                <?php echo e($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'free' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
                            Livre
                        </button>
                        <button 
                            wire:click="setStatus('occupied')"
                            <?php if($hasActiveCheck): ?> disabled <?php endif; ?>
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                <?php echo e($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'occupied' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
                            Ocupada
                        </button>
                        <button 
                            wire:click="setStatus('reserved')"
                            <?php if($hasActiveCheck): ?> disabled <?php endif; ?>
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                <?php echo e($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'reserved' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
                            Reservada
                        </button>
                        <button 
                            wire:click="setStatus('releasing')"
                            <?php if($hasActiveCheck): ?> disabled <?php endif; ?>
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                <?php echo e($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'releasing' ? 'bg-teal-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
                            Liberando
                        </button>
                        <button 
                            wire:click="setStatus('close')"
                            <?php if($hasActiveCheck): ?> disabled <?php endif; ?>
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                <?php echo e($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'close' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
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
                        wire:click="closeModal"
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH E:\Projects\solpraia\resources\views/livewire/table-status-modal.blade.php ENDPATH**/ ?>