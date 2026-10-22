<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value']));

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

foreach (array_filter((['value']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<span <?php echo e($attributes->merge(['class' => 'flex items-center gap-3'])); ?>>
    <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-muted" role="progressbar" aria-valuenow="<?php echo e($value); ?>" aria-valuemin="0" aria-valuemax="100">
        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['block h-full rounded-full', 'bg-success' => $value >= 100, 'bg-primary' => $value < 100]); ?>" style="width: <?php echo e($value); ?>%"></span>
    </span>
    <span class="w-10 text-right text-xs tabular-nums text-muted-foreground"><?php echo e($value); ?>%</span>
</span>
<?php /**PATH /Users/ibnuzaman/Project/dila-aris/resources/views/components/progress.blade.php ENDPATH**/ ?>