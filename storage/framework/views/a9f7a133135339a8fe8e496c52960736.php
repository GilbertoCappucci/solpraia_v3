<div>

    <?php if (isset($component)) { $__componentOriginalbb0843bd48625210e6e530f88101357e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb0843bd48625210e6e530f88101357e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash-message','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash-message'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb0843bd48625210e6e530f88101357e)): ?>
<?php $attributes = $__attributesOriginalbb0843bd48625210e6e530f88101357e; ?>
<?php unset($__attributesOriginalbb0843bd48625210e6e530f88101357e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb0843bd48625210e6e530f88101357e)): ?>
<?php $component = $__componentOriginalbb0843bd48625210e6e530f88101357e; ?>
<?php unset($__componentOriginalbb0843bd48625210e6e530f88101357e); ?>
<?php endif; ?>

    
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-3 flex items-center justify-between sticky top-0 z-10 shadow-md">
        
        <div class="flex items-center gap-2">
            
            <button
                wire:click="backToTables"
                class="p-1.5 hover:bg-white/20 rounded-lg transition"
                title="Voltar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            
            <span class="text-2xl font-bold"><?php echo e($selectedTable->number); ?></span>

        </div>

        
        <div class="flex items-center gap-2">
            
            <button
                wire:click="openFilterModal"
                class="flex items-center gap-1 px-3 py-1.5 border-2 rounded-lg text-sm font-medium transition
                    <?php echo e(count($statusFilters) < 5 ? 'border-white bg-white/20 text-white' : 'border-white/30 bg-white/10 text-white hover:bg-white/20'); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filtros
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($statusFilters) < 5): ?>
                    <span class="ml-1 px-1.5 py-0.5 bg-white text-orange-600 rounded-full text-xs font-bold">!</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>

            
            <button
                wire:click="openStatusModal"
                class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 border-2 border-white/30 text-white rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>
    </div>

    
    <div class="bg-white">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($groupedOrders->isEmpty()): ?>
        <div class="p-8 text-center text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($statusFilters) < 5): ?>
                <p class="text-lg font-medium">Nenhum pedido encontrado</p>
                <p class="text-sm mt-1">Não há pedidos com os filtros selecionados</p>
                <?php else: ?>
                <p class="text-lg font-medium">Nenhum pedido ativo</p>
                <p class="text-sm mt-1">Clique em "Adicionar Pedidos" para começar</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php else: ?>
        <div class="divide-y divide-gray-200">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groupedOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
            $statusConfig = match($group->status) {
            'pending' => ['label' => 'Aguardando', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
            'in_production' => ['label' => 'Em Preparo', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
            'in_transit' => ['label' => 'Em Trânsito', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
            'completed' => ['label' => 'Entregue', 'color' => 'bg-green-100 text-green-800 border-green-200'],
            'canceled' => ['label' => 'Cancelado', 'color' => 'bg-red-100 text-red-800 border-red-200'],
            default => ['label' => 'Desconhecido', 'color' => 'bg-gray-100 text-gray-800 border-gray-200']
            };

            // Verifica se o grupo está atrasado (usando valores do banco de dados via helper)
            $isDelayed = false;

            if ($group->status_changed_at) {
            $minutes = abs((int) now()->diffInMinutes($group->status_changed_at));

            $isDelayed = match($group->status) {
            'pending' => $minutes > $timeLimits['pending'],
            'in_production' => $minutes > $timeLimits['in_production'],
            'in_transit' => $minutes > $timeLimits['in_transit'],
            default => false
            };
            }

            $delayAnimation = ($isDelayed) ? 'animate-pulse-warning' : '';
            ?>

            <div wire:click="<?php echo e($group->order_count === 1 ? 'openDetailsModal(' . $group->orders->first()->id . ')' : 'openGroupModal(' . $group->product_id . ', \'' . $group->status . '\')'); ?>" class="p-4 hover:bg-gray-50 transition flex items-center gap-4 cursor-pointer <?php echo e($delayAnimation); ?>">
                
                <div class="flex-shrink-0 w-14 text-center">
                    <span class="text-xl font-bold text-gray-900"><?php echo e($group->total_quantity); ?></span>
                </div>

                
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-900 truncate"><?php echo e($group->product_name); ?></h4>
                    <p class="text-sm text-gray-500">R$ <?php echo e(number_format($group->total_price, 2, ',', '.')); ?></p>
                </div>

                
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border <?php echo e($statusConfig['color']); ?>">
                        <?php echo e($statusConfig['label']); ?>

                    </span>
                </div>

                
                <div class="flex-shrink-0 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="flex items-center justify-between">
                <span class="text-lg font-semibold text-gray-700">Total Geral:</span>
                <span class="text-2xl font-bold text-gray-700">R$ <?php echo e(number_format($checkTotal, 2, ',', '.')); ?></span>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-4 sticky bottom-0 z-10 shadow-lg">
        <div class="flex items-center justify-between gap-4">
            
            <div class="flex-shrink-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedTable->status === 'close'): ?>
                <div class="bg-red-100 border-2 border-red-300 text-red-700 py-3 px-6 rounded-xl font-bold text-center text-sm">
                    Mesa Fechada
                </div>
                <?php elseif($selectedTable->status === 'releasing'): ?>
                <div class="bg-teal-100 border-2 border-teal-300 text-teal-800 py-3 px-6 rounded-xl font-bold text-center text-sm">
                    Mesa em Liberação
                </div>
                <?php elseif($selectedTable->status === 'reserved'): ?>
                <div class="bg-purple-100 border-2 border-purple-300 text-purple-800 py-3 px-6 rounded-xl font-bold text-center text-sm">
                    Mesa Reservada
                </div>
                <?php elseif(!$isCheckOpen): ?>
                <div class="bg-yellow-100 border-2 border-yellow-300 text-yellow-800 py-3 px-6 rounded-xl font-bold text-center text-sm">
                    Check Fechado
                </div>
                <?php else: ?>
                <button
                    wire:click="goToMenu"
                    class="bg-gradient-to-r from-orange-500 to-red-500 text-white py-3 px-6 rounded-xl font-bold text-lg flex items-center justify-center gap-3 hover:shadow-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width-2" d="M12 4v16m8-8H4" />
                    </svg>
                    Adicionar Pedidos
                </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div wire:loading wire:target="refreshData, updateOrderStatus, updateAllOrderStatus" class="w-full text-center py-2 mt-3">
            <span class="text-sm text-gray-500 animate-pulse">Atualizando totais...</span>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showStatusCheckModal): ?>
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeStatusModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.stop>
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Alterar Status</h3>
                <button wire:click="closeStatusModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            
            <div class="space-y-4 mb-6">
                <?php if (isset($component)) { $__componentOriginal8aefaa5bcbd301f40e4c90ef2f8a660c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8aefaa5bcbd301f40e4c90ef2f8a660c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-status-selector','data' => ['table' => $selectedTable,'newTableStatus' => $newTableStatus,'hasActiveCheck' => $hasActiveCheck]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-status-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['table' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedTable),'newTableStatus' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($newTableStatus),'hasActiveCheck' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasActiveCheck)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8aefaa5bcbd301f40e4c90ef2f8a660c)): ?>
<?php $attributes = $__attributesOriginal8aefaa5bcbd301f40e4c90ef2f8a660c; ?>
<?php unset($__attributesOriginal8aefaa5bcbd301f40e4c90ef2f8a660c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8aefaa5bcbd301f40e4c90ef2f8a660c)): ?>
<?php $component = $__componentOriginal8aefaa5bcbd301f40e4c90ef2f8a660c; ?>
<?php unset($__componentOriginal8aefaa5bcbd301f40e4c90ef2f8a660c); ?>
<?php endif; ?>
            </div>

            
            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCheck): ?>
                <?php
                $pendingCount = $orders->where('status', 'pending')->count();
                $inProductionCount = $orders->where('status', 'in_production')->count();
                $inTransitCount = $orders->where('status', 'in_transit')->count();
                ?>
                <?php if (isset($component)) { $__componentOriginale4e3c5469243210dbe37ba170d1df0d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4e3c5469243210dbe37ba170d1df0d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.check-status-selector','data' => ['check' => $currentCheck,'newCheckStatus' => $newCheckStatus,'pendingCount' => $pendingCount,'inProductionCount' => $inProductionCount,'inTransitCount' => $inTransitCount,'checkStatusAllowed' => $checkStatusAllowed]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('check-status-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['check' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentCheck),'newCheckStatus' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($newCheckStatus),'pendingCount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingCount),'inProductionCount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inProductionCount),'inTransitCount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inTransitCount),'checkStatusAllowed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($checkStatusAllowed)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale4e3c5469243210dbe37ba170d1df0d8)): ?>
<?php $attributes = $__attributesOriginale4e3c5469243210dbe37ba170d1df0d8; ?>
<?php unset($__attributesOriginale4e3c5469243210dbe37ba170d1df0d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale4e3c5469243210dbe37ba170d1df0d8)): ?>
<?php $component = $__componentOriginale4e3c5469243210dbe37ba170d1df0d8; ?>
<?php unset($__componentOriginale4e3c5469243210dbe37ba170d1df0d8); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="flex gap-3 mt-6">
                <button
                    wire:click="closeStatusModal"
                    class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Cancelar
                </button>
                <button
                    wire:click="updateStatuses"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-bold hover:shadow-lg transition">
                    Salvar
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showGroupModal): ?>
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeGroupModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col" @click.stop>
            
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold">Pedidos do Grupo</h3>
                        <p class="text-sm text-white/90 mt-1">
                            <?php echo e(count($groupOrders)); ?> pedido(s)
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedOrderIds) > 0): ?>
                            | <strong><?php echo e(count($selectedOrderIds)); ?> selecionado(s)</strong>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </div>
                    <button wire:click="closeGroupModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            
            <div class="flex-1 overflow-y-auto p-6 space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groupOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                $orderObj = (object) $order;
                $product = isset($order['product']) ? (object) $order['product'] : (object)['name' => 'Produto', 'price' => 0];
                $isSelected = in_array($orderObj->id, $selectedOrderIds);
                ?>
                <div class="bg-gray-50 rounded-lg p-4 transition border-2 <?php echo e($isSelected ? 'border-orange-400 bg-orange-50' : 'border-gray-200'); ?>">
                    <div class="flex items-start gap-3">
                        
                        <input
                            type="checkbox"
                            wire:click="toggleOrderSelection(<?php echo e($orderObj->id); ?>)"
                            <?php echo e($isSelected ? 'checked' : ''); ?>

                            class="mt-1 w-5 h-5 text-orange-500 border-gray-300 rounded focus:ring-orange-400 cursor-pointer">

                        
                        <div class="flex-1 cursor-pointer" wire:click.stop="openDetailsFromGroup(<?php echo e($orderObj->id); ?>)">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-lg font-bold text-gray-900"><?php echo e($orderObj->quantity); ?> <?php echo e($product->name); ?></span>
                                <span class="text-sm font-semibold text-orange-600">R$ <?php echo e(number_format($orderObj->price * $orderObj->quantity, 2, ',', '.')); ?></span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>Pedido #<?php echo e($orderObj->id); ?></span>
                                <span><?php echo e(\Carbon\Carbon::parse($orderObj->created_at)->format('H:i')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="p-6 pt-0 flex-shrink-0 border-t space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedOrderIds) > 0): ?>
                <button
                    wire:click="openGroupActionsModal"
                    class="w-full px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-bold hover:shadow-lg transition">
                    Ações em Grupo (<?php echo e(count($selectedOrderIds)); ?>)
                </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button
                    wire:click="closeGroupModal"
                    class="w-full px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showGroupActionsModal && $groupActionData): ?>
    <div class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden" @click.stop>
            
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white">
                <h3 class="text-2xl font-bold">Ações em Grupo</h3>
                <p class="text-sm text-white/90 mt-1"><?php echo e($groupActionData['count']); ?> pedido(s) selecionado(s)</p>
            </div>

            
            <div class="p-6 space-y-4">
                
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Produto</span>
                        <span class="font-bold text-gray-900"><?php echo e($groupActionData['product_name']); ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Quantidade Total</span>
                        <span class="font-bold text-gray-900"><?php echo e($groupActionData['total_quantity']); ?></span>
                    </div>
                    <div class="flex items-center justify-between border-t pt-2">
                        <span class="text-sm font-semibold text-gray-700">Valor Total</span>
                        <span class="text-xl font-bold text-orange-600">R$ <?php echo e(number_format($groupActionData['total_price'], 2, ',', '.')); ?></span>
                    </div>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCheckOpen): ?>
                <?php
                // Permite transição para qualquer status (sem restrições)
                $allStatuses = ['pending', 'in_production', 'in_transit', 'completed'];
                $allowedTransitions = $allStatuses; // Permite todos os status
                ?>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Alterar Status de Todos</label>
                    <div class="grid grid-cols-2 gap-2">
                        <?php $canGoToPending = in_array('pending', $allowedTransitions); ?>
                        <button
                            wire:click="updateGroupStatus('pending')"
                            <?php echo e(!$canGoToPending ? 'disabled' : ''); ?>

                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                <?php echo e($canGoToPending ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'); ?>">
                            ⏳ Aguardando
                        </button>

                        <?php $canGoToProduction = in_array('in_production', $allowedTransitions); ?>
                        <button
                            wire:click="updateGroupStatus('in_production')"
                            <?php echo e(!$canGoToProduction ? 'disabled' : ''); ?>

                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                <?php echo e($canGoToProduction ? 'bg-blue-500 hover:bg-blue-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'); ?>">
                            🍳 Em Preparo
                        </button>

                        <?php $canGoToTransit = in_array('in_transit', $allowedTransitions); ?>
                        <button
                            wire:click="updateGroupStatus('in_transit')"
                            <?php echo e(!$canGoToTransit ? 'disabled' : ''); ?>

                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                <?php echo e($canGoToTransit ? 'bg-purple-500 hover:bg-purple-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'); ?>">
                            🚶 Em Trânsito
                        </button>

                        <?php $canGoToCompleted = in_array('completed', $allowedTransitions); ?>
                        <button
                            wire:click="updateGroupStatus('completed')"
                            <?php echo e(!$canGoToCompleted ? 'disabled' : ''); ?>

                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                <?php echo e($canGoToCompleted ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'); ?>">
                            ✓ Entregue
                        </button>
                    </div>
                </div>

                
                <div class="pt-4 border-t">
                    <button
                        wire:click="cancelGroupOrders"
                        class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold transition shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Remover Todos os Selecionados
                    </button>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="p-6 pt-0 border-t">
                <button
                    wire:click="closeGroupActionsModal"
                    class="w-full px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition">
                    Voltar
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFilterModal): ?>
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40" wire:click="closeFilterModal"></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFilterModal): ?>
    <div class="fixed top-2 left-1/2 -translate-x-1/2 w-[95%] max-w-2xl max-h-[calc(100vh-2.5rem)] overflow-y-auto bg-white rounded-2xl shadow-2xl border-2 border-gray-300 z-50">

        
        <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-red-500 text-white px-4 py-3 rounded-t-2xl shadow-lg z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <h3 class="text-lg font-bold">Filtros</h3>
                </div>
                <button wire:click="resetFilters" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Limpar
                </button>
                <button wire:click="closeFilterModal" class="p-1 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-3 space-y-2">
            
            <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="text-sm font-bold text-gray-800">Status dos Pedidos</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="toggleStatusFilter('pending')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                <?php echo e(in_array('pending', $statusFilters) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Aguardando
                    </button>
                    <button wire:click="toggleStatusFilter('in_production')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                <?php echo e(in_array('in_production', $statusFilters) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Em Preparo
                    </button>
                    <button wire:click="toggleStatusFilter('in_transit')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                <?php echo e(in_array('in_transit', $statusFilters) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Em Trânsito
                    </button>
                    <button wire:click="toggleStatusFilter('completed')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                <?php echo e(in_array('completed', $statusFilters) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Entregue
                    </button>
                    <button wire:click="toggleStatusFilter('canceled')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                <?php echo e(in_array('canceled', $statusFilters) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500'); ?>">
                        Cancelado
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDetailsModal && $orderDetails): ?>
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeDetailsModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden" @click.stop>
            
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-4 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold">Detalhes do Pedido</h3>
                    <button wire:click="closeDetailsModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            
            <div class="p-4 space-y-4">
                
                <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Produto</span>
                        <span class="font-bold text-gray-900"><?php echo e($orderDetails['product_name']); ?></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Preço Unitário</span>
                        <span class="font-semibold text-gray-900">R$ <?php echo e(number_format($orderDetails['price'], 2, ',', '.')); ?></span>
                    </div>

                    <div class="flex items-center justify-between border-t pt-3">
                        <span class="text-sm font-semibold text-gray-700">Total</span>
                        <span class="text-xl font-bold text-orange-600">R$ <?php echo e(number_format($orderDetails['total'], 2, ',', '.')); ?></span>
                    </div>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCheckOpen && $orderDetails['status'] === 'pending'): ?>
                <div class="space-y-1">

                    <div class="flex items-center gap-4">
                        <button
                            wire:click.stop="decrementQuantity"
                            <?php echo e($orderDetails['quantity'] <= 1 ? 'disabled' : ''); ?>

                            class="w-12 h-12 flex items-center justify-center rounded-lg font-bold text-xl transition shadow-md
                                <?php echo e($orderDetails['quantity'] > 1 ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'); ?>">
                            −
                        </button>

                        <div class="flex-1 text-center">
                            <span class="text-4xl font-bold text-gray-900"><?php echo e($orderDetails['quantity']); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orderDetails['available_stock'] > 0): ?>
                            <p class="text-xs text-gray-500 mt-1">Estoque: <?php echo e($orderDetails['available_stock']); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orderDetails['quantity'] <= 1): ?>
                                <p class="text-xs text-gray-500 mt-1">Use "Cancelar" para remover</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <button
                            wire:click.stop="incrementQuantity"
                            <?php echo e($orderDetails['available_stock'] == 0 ? 'disabled' : ''); ?>

                            class="w-12 h-12 flex items-center justify-center rounded-lg font-bold text-xl transition shadow-md
                                <?php echo e($orderDetails['available_stock'] != 0 ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'); ?>">
                            +
                        </button>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="space-y-2">

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCheckOpen): ?>
                    <?php
                    // Permite transição para qualquer status (sem restrições)
                    $allStatuses = ['pending', 'in_production', 'in_transit', 'completed'];
                    $allowedTransitions = array_diff($allStatuses, [$orderDetails['status']]); // Permite todos exceto o atual
                    ?>
                    <div>
                        <div class="grid grid-cols-2 gap-2">
                            
                            <?php $canGoToPending = in_array('pending', $allowedTransitions); ?>
                            <button
                                wire:click="updateOrderStatusFromModal('pending')"
                                <?php echo e(!$canGoToPending ? 'disabled' : ''); ?>

                                class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                    <?php echo e($canGoToPending ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'); ?>">
                                ⏳ Aguardando
                            </button>

                            
                            <?php $canGoToProduction = in_array('in_production', $allowedTransitions); ?>
                            <button
                                wire:click="updateOrderStatusFromModal('in_production')"
                                <?php echo e(!$canGoToProduction ? 'disabled' : ''); ?>

                                class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                    <?php echo e($canGoToProduction ? 'bg-blue-500 hover:bg-blue-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'); ?>">
                                🍳 Em Preparo
                            </button>

                            
                            <?php $canGoToTransit = in_array('in_transit', $allowedTransitions); ?>
                            <button
                                wire:click="updateOrderStatusFromModal('in_transit')"
                                <?php echo e(!$canGoToTransit ? 'disabled' : ''); ?>

                                class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                    <?php echo e($canGoToTransit ? 'bg-purple-500 hover:bg-purple-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'); ?>">
                                🚶 Em Trânsito
                            </button>

                            
                            <?php $canGoToCompleted = in_array('completed', $allowedTransitions); ?>
                            <button
                                wire:click="updateOrderStatusFromModal('completed')"
                                <?php echo e(!$canGoToCompleted ? 'disabled' : ''); ?>

                                class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                    <?php echo e($canGoToCompleted ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'); ?>">
                                ✓ Entregue
                            </button>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orderDetails['status'] !== 'canceled'): ?>
                <div class="pt-2 border-t">
                    <button
                        wire:click="cancelOrderFromModal"
                        class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold transition shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Remover
                    </button>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCancelModal): ?>
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeCancelModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden" @click.stop>
            
            <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 text-white">
                <div class="flex items-center justify-center mb-3">
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-center">
                    Remover Item?
                </h3>
            </div>

            
            <div class="p-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orderToCancelData): ?>
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-600 text-sm">Produto</span>
                        <span class="font-bold text-gray-900"><?php echo e($orderToCancelData['product_name']); ?></span>
                    </div>

                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-600 text-sm">Quantidade Atual</span>
                        <span class="font-bold text-gray-900"><?php echo e($orderToCancelData['quantity']); ?>x</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Valor Unitário</span>
                        <span class="font-bold text-gray-700">R$ <?php echo e(number_format($orderToCancelData['price'], 2, ',', '.')); ?></span>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <p class="text-gray-600 text-center mb-6">
                    Deseja remover este item do pedido?
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orderToCancelData && $orderToCancelData['quantity'] > 1): ?>
                    <br><span class="text-sm">Você pode remover apenas 1 unidade ou todas.</span>
                    <?php else: ?>
                    <br><span class="text-sm">Esta ação não pode ser desfeita.</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orderToCancelData && $orderToCancelData['quantity'] > 1): ?>
                <div class="flex flex-col gap-3">
                    <div class="flex gap-3">
                        <button
                            wire:click="confirmCancelOrder(<?php echo e($orderToCancelData['quantity']); ?>)"
                            class="flex-1 border-2 border-red-500 text-red-600 hover:bg-red-50 font-bold py-3 px-4 rounded-lg transition">
                            Remover Todos
                        </button>
                        <button
                            wire:click="confirmCancelOrder(1)"
                            class="flex-1 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold py-3 px-4 rounded-lg transition shadow-md">
                            Remover 1 Unidade
                        </button>
                    </div>
                    <button
                        wire:click="closeCancelModal"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-lg transition">
                        Cancelar
                    </button>
                </div>
                <?php else: ?>
                <div class="flex gap-3">
                    <button
                        wire:click="closeCancelModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition">
                        Cancelar
                    </button>
                    <button
                        wire:click="confirmCancelOrder"
                        class="flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-lg">
                        Sim, Remover
                    </button>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH E:\Projects\solpraia\resources\views/livewire/orders.blade.php ENDPATH**/ ?>