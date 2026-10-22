{{-- Placeholder; halaman dashboard dikerjakan di sesi 2. --}}
<x-layouts.guest title="Dashboard">
    <main class="flex min-h-dvh flex-col items-center justify-center gap-4">
        <p class="text-sm text-muted-foreground">Masuk sebagai <span class="font-medium text-foreground">{{ auth()->user()->name }}</span></p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button variant="outline" icon="log-out">Keluar</x-button>
        </form>
    </main>
</x-layouts.guest>
