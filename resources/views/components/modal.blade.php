{{-- Dialog sederhana yang dikendalikan state Livewire: <x-modal :show="$formTerbuka" title=".." close="tutupForm"> --}}
@props(['show' => false, 'title', 'close'])

@if ($show)
    <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-primary/40 p-4 sm:items-center"
        x-data x-on:keydown.escape.window="$wire.{{ $close }}()" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div {{ $attributes->merge(['class' => 'flex w-full max-w-lg flex-col gap-4 rounded-lg bg-card p-5 shadow-xl']) }}>
            <div class="flex items-start justify-between gap-3">
                <h2 id="modal-title" class="text-lg font-semibold text-primary">{{ $title }}</h2>
                <button type="button" wire:click="{{ $close }}" class="flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-muted" aria-label="Tutup">
                    <x-icon name="x" />
                </button>
            </div>
            {{ $slot }}
        </div>
    </div>
@endif
