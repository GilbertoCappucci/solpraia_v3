<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
'check',
'newCheckStatus',
'pendingCount' => 0,
'inProductionCount' => 0,
'inTransitCount' => 0,
'checkStatusAllowed' => [],
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
'check',
'newCheckStatus',
'pendingCount' => 0,
'inProductionCount' => 0,
'inTransitCount' => 0,
'checkStatusAllowed' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
// Verifica se há pedidos não entregues (excluindo cancelados)
$hasIncompleteOrders = ($pendingCount > 0 ||
$inProductionCount > 0 ||
$inTransitCount > 0);

// Verifica se pode cancelar (total zero)
$canCancelCheck = $check->total == 0;

// Regras de bloqueio baseadas no status atual do check
// Open: pode ir para Fechado (se pedidos entregues) ou Cancelado
// Closed: pode ir para Paid ou voltar para Open
// Paid: livre navegação
$blockClosedButton = match($check->status) {
'Open' => $hasIncompleteOrders,
default => false
};
$blockPaidButton = ($check->status === 'Open'); // Bloqueado quando Open, liberado quando Closed
?>

<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">Status do Check</label>

    
    <div class="flex flex-wrap gap-2">
        <?php
        $isOpenAllowed = in_array('Open', $checkStatusAllowed) || $newCheckStatus === 'Open';
        $isClosedAllowed = in_array('Closed', $checkStatusAllowed) || $newCheckStatus === 'Closed';
        $isPaidAllowed = in_array('Paid', $checkStatusAllowed) || $newCheckStatus === 'Paid';
        $isCanceledAllowed = in_array('Canceled', $checkStatusAllowed) || $newCheckStatus === 'Canceled';
        ?>

        <button
            wire:click="$set('newCheckStatus', 'Open')"
            <?php if(!$isOpenAllowed): ?> disabled <?php endif; ?>
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                <?php echo e(!$isOpenAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Open' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
            Aberto
        </button>
        <button
            wire:click="$set('newCheckStatus', 'Closed')"
            <?php if(!$isClosedAllowed): ?> disabled <?php endif; ?>
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                <?php echo e(!$isClosedAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Closed' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
            Fechado
        </button>
        <button
            wire:click="$set('newCheckStatus', 'Paid')"
            <?php if(!$isPaidAllowed): ?> disabled <?php endif; ?>
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                <?php echo e(!$isPaidAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Paid' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
            Pago
        </button>
        <button
            wire:click="$set('newCheckStatus', 'Canceled')"
            <?php if(!$isCanceledAllowed): ?> disabled <?php endif; ?>
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                <?php echo e(!$isCanceledAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Canceled' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')); ?>">
            Cancelar
        </button>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($checkStatusAllowed) === 0): ?>
    <p class="mt-2 text-xs text-orange-600 font-medium">
        <i class="fas fa-info-circle mr-1"></i>
        Status do check bloqueado, verifique pedidos pendentes.
    </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH E:\Projects\solpraia\resources\views/components/check-status-selector.blade.php ENDPATH**/ ?>