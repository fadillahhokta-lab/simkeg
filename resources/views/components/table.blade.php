{{-- Pakai: <x-table><x-slot:head><th>..</th></x-slot:head> <tr>..</tr> </x-table> --}}
<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-lg border border-border bg-card']) }}>
    <table class="w-full text-left text-sm">
        <thead class="border-b border-border text-muted-foreground [&_th]:px-4 [&_th]:py-3 [&_th]:font-medium">
            <tr>{{ $head }}</tr>
        </thead>
        <tbody class="divide-y divide-border [&_td]:px-4 [&_td]:py-3">
            {{ $slot }}
        </tbody>
    </table>
</div>
