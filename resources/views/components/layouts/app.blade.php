@props(['title' => null])

@php
    $user = auth()->user();
    $menu = [
        ['dashboard', 'Dashboard', 'layout-dashboard'],
        ['kegiatan.index', 'Kegiatan', 'clipboard-list'],
        ['dinas.index', 'Dinas Luar Kota', 'plane'],
        ['rekap.index', 'Rekap & Export', 'file-spreadsheet'],
    ];
    $pengaturan = [
        ['jenis-kegiatan.index', 'Jenis Kegiatan', 'tag'],
        ['pengguna.index', 'Manajemen User', 'users'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-dvh">
        <div class="flex min-h-dvh">
            <aside class="sticky top-0 hidden h-dvh w-64 shrink-0 flex-col gap-8 bg-sidebar px-3 py-5 lg:flex">
                <a href="{{ route('dashboard') }}" class="px-2">
                    <x-logo class="h-9" />
                </a>

                <nav aria-label="Navigasi utama" class="flex flex-1 flex-col gap-6">
                    <ul class="flex flex-col gap-1">
                        @foreach ($menu as [$route, $label, $icon])
                            <li><x-nav-link :href="route($route)" :icon="$icon" :active="request()->routeIs(Str::before($route, '.').'*')">{{ $label }}</x-nav-link></li>
                        @endforeach
                    </ul>

                    @if ($user->isAdmin())
                        <div class="flex flex-col gap-1">
                            <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-sidebar-foreground/60">Pengaturan</p>
                            <ul class="flex flex-col gap-1">
                                @foreach ($pengaturan as [$route, $label, $icon])
                                    <li><x-nav-link :href="route($route)" :icon="$icon" :active="request()->routeIs(Str::before($route, '.').'*')">{{ $label }}</x-nav-link></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </nav>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-border bg-card/95 px-4 backdrop-blur md:px-6 lg:px-8">
                    {{-- Menu mobile --}}
                    <details class="relative lg:hidden">
                        <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-md text-primary hover:bg-muted" aria-label="Buka menu">
                            <x-icon name="layout-dashboard" class="size-5" />
                        </summary>
                        <nav class="absolute top-12 left-0 z-40 flex w-64 flex-col gap-1 rounded-lg bg-sidebar p-3 shadow-lg">
                            @foreach ($user->isAdmin() ? [...$menu, ...$pengaturan] : $menu as [$route, $label, $icon])
                                <x-nav-link :href="route($route)" :icon="$icon" :active="request()->routeIs(Str::before($route, '.').'*')">{{ $label }}</x-nav-link>
                            @endforeach
                        </nav>
                    </details>

                    <form action="{{ route('kegiatan.index') }}" method="GET" role="search" class="relative w-full max-w-sm">
                        <label for="global-search" class="sr-only">Cari kode atau judul kegiatan</label>
                        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground" />
                        <input id="global-search" name="q" value="{{ request('q') }}" placeholder="Cari kode kegiatan, mis. PP-014-2026"
                            class="h-10 w-full rounded-md border border-border bg-background pr-3 pl-9 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30">
                    </form>

                    <details class="relative ml-auto">
                        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-md px-1.5 py-1 hover:bg-muted">
                            <span class="flex size-8 items-center justify-center rounded-full bg-navy-soft text-xs font-semibold text-primary">{{ $user->initials() }}</span>
                            <span class="hidden flex-col items-start leading-tight sm:flex">
                                <span class="text-sm font-medium">{{ $user->name }}</span>
                                <span class="rounded bg-khaki px-1 text-[10px] font-semibold uppercase tracking-wide text-primary">{{ $user->isAdmin() ? 'Admin' : 'Staff' }}</span>
                            </span>
                            <x-icon name="chevron-down" class="text-muted-foreground" />
                        </summary>
                        <div class="absolute right-0 z-40 mt-2 w-48 rounded-lg border border-border bg-card p-1 shadow-lg">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-muted">
                                    <x-icon name="log-out" /> Keluar
                                </button>
                            </form>
                        </div>
                    </details>
                </header>

                <main class="flex-1 px-4 py-6 md:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScriptConfig
    </body>
</html>
