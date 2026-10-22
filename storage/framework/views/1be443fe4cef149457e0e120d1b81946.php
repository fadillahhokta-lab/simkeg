<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label' => null, 'name', 'id' => null, 'type' => 'text']));

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

foreach (array_filter((['label' => null, 'name', 'id' => null, 'type' => 'text']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $id ??= $name;
    $hasError = $errors->has($name);
?>

<div class="flex flex-col gap-2">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <label for="<?php echo e($id); ?>" class="text-sm font-medium leading-none"><?php echo e($label); ?></label>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="relative">
        <input
            id="<?php echo e($id); ?>"
            name="<?php echo e($name); ?>"
            type="<?php echo e($type); ?>"
            <?php if($type !== 'password'): ?> value="<?php echo e(old($name, $attributes->get('value'))); ?>" <?php endif; ?>
            <?php if($hasError): ?> aria-invalid="true" aria-describedby="<?php echo e($id); ?>-error" <?php endif; ?>
            <?php echo e($attributes->except('value')->class([
                'h-10 w-full min-w-0 rounded-md border bg-card px-3 text-sm outline-none transition-colors placeholder:text-muted-foreground',
                'focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30',
                'border-input' => ! $hasError,
                'border-danger ring-3 ring-danger/15' => $hasError,
            ])); ?>

        >
        <?php echo e($slot); ?>

    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p id="<?php echo e($id); ?>-error" class="text-xs text-danger"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/ibnuzaman/Project/dila-aris/resources/views/components/input.blade.php ENDPATH**/ ?>