<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'status' => 'pending', // pending, production, transit
    'count' => 0,
    'minutes' => 0,
    'dotSize' => 'w-4 h-4',
    'textSize' => 'text-lg',
    'padding' => 'py-3'
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
    'status' => 'pending', // pending, production, transit
    'count' => 0,
    'minutes' => 0,
    'dotSize' => 'w-4 h-4',
    'textSize' => 'text-lg',
    'padding' => 'py-3'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $config = match($status) {
        'pending' => [
            'color' => 'bg-yellow-500',
            'textColor' => 'text-yellow-700',
            'label' => 'aguardando'
        ],
        'production' => [
            'color' => 'bg-blue-500',
            'textColor' => 'text-blue-700',
            'label' => 'em preparo'
        ],
        'transit' => [
            'color' => 'bg-purple-500',
            'textColor' => 'text-purple-700',
            'label' => 'em trânsito'
        ],
        default => [
            'color' => 'bg-gray-500',
            'textColor' => 'text-gray-700',
            'label' => ''
        ]
    };
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
    <div class="flex flex-col items-center justify-center <?php echo e($padding); ?>">
        <span class="<?php echo e($dotSize); ?> <?php echo e($config['color']); ?> rounded-full mb-1" 
              title="<?php echo e($count); ?> <?php echo e($config['label']); ?>"></span>
        <span class="<?php echo e($textSize); ?> font-semibold <?php echo e($config['textColor']); ?>"><?php echo e($minutes); ?>m</span>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH E:\Projects\solpraia\resources\views/components/order-status-indicator.blade.php ENDPATH**/ ?>