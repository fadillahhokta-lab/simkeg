<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['user', 'size' => 'sm']));

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

foreach (array_filter((['user', 'size' => 'sm']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<span title="<?php echo e($user->name); ?>" <?php echo e($attributes->class([
    'flex shrink-0 items-center justify-center rounded-full bg-navy-soft font-semibold text-primary ring-2 ring-card',
    'size-7 text-[10px]' => $size === 'sm',
    'size-9 text-xs' => $size === 'md',
])); ?>><?php echo e($user->initials()); ?></span>
<?php /**PATH /Users/ibnuzaman/Project/dila-aris/resources/views/components/avatar.blade.php ENDPATH**/ ?>