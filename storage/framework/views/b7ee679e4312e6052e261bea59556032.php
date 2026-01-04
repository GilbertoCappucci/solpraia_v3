<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
'table',
'newTableStatus',
'hasActiveCheck' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
'table',
'newTableStatus',
'hasActiveCheck' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">Status da Mesa</label>

    
    <div class="flex flex-wrap gap-2">
        <?php
        $statuses = [
        ['value' => 'free', 'label' => 'Livre', 'activeColor' => 'bg-gray-500'],
        ['value' => 'occupied', 'label' => 'Ocupada', 'activeColor' => 'bg-green-600'],
        ['value' => 'reserved', 'label' => 'Reservada', 'activeColor' => 'bg-purple-500'],
        ['value' => 'releasing', 'label' => 'Liberando', 'activeColor' => 'bg-teal-500'],
        ['value' => 'close', 'label' => 'Fechada', 'activeColor' => 'bg-red-600'],
        ];

        // A lógica de bloqueio: se houver check ativo (Open/Closed), não pode mudar status da mesa.
        // Mas no momento do modal, exibimos o status atual.
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
        $isCurrent = $newTableStatus === $status['value'];
        $isDisabled = $hasActiveCheck && !$isCurrent;
        // Exceção: Se o status for 'close', e houver check, o OrderService bloqueia.
        if ($status['value'] === 'close' && $hasActiveCheck) {
        $isDisabled = true;
        }
        ?>
        <button
            wire:click="$set('newTableStatus', '<?php echo e($status['value']); ?>')"
            <?php if($isDisabled): ?> disabled <?php endif; ?>
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                    <?php echo e($isDisabled ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($isCurrent ? $status['activeColor'] . ' text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
            <?php echo e($status['label']); ?>

        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasActiveCheck): ?>
    <p class="mt-2 text-xs text-orange-600 font-medium">
        <i class="fas fa-info-circle mr-1"></i>
        Status da mesa bloqueado enquanto houver um check ativo.
    </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH E:\Projects\solpraia\resources\views/components/table-status-selector.blade.php ENDPATH**/ ?>