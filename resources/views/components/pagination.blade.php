{{-- Paginasi Livewire sederhana: $paginator->links('components.pagination') --}}
@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3 text-sm text-muted-foreground" aria-label="Paginasi">
        <span>Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        <span class="flex gap-2">
            <x-button type="button" variant="outline" size="sm" icon="chevron-left" wire:click="previousPage" :disabled="$paginator->onFirstPage()">Sebelumnya</x-button>
            <x-button type="button" variant="outline" size="sm" wire:click="nextPage" :disabled="! $paginator->hasMorePages()">Berikutnya <x-icon name="chevron-right" /></x-button>
        </span>
    </nav>
@endif
