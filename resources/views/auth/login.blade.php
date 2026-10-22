<x-layouts.guest title="Masuk">
    <main class="grid min-h-dvh lg:grid-cols-2">
        <section class="hidden flex-col justify-between bg-primary p-10 text-primary-foreground lg:flex">
            <x-logo class="h-10" />

            <div class="flex max-w-md flex-col gap-4">
                <p class="text-sm font-semibold uppercase tracking-widest text-khaki">Aplikasi internal</p>
                <h1 class="text-4xl font-semibold leading-tight text-balance">Satu tempat untuk kegiatan, penugasan tim, dan dinas luar kota.</h1>
                <p class="leading-relaxed text-pretty text-primary-foreground/70">Pantau progres per periode, lihat beban kerja staff, dan hasilkan rekap dalam hitungan menit.</p>
            </div>

            <dl class="grid grid-cols-3 gap-4 border-t border-sidebar-border pt-6">
                @foreach (['Jenis kegiatan' => $jumlahJenis, 'Kegiatan aktif' => $kegiatanAktif, 'Dinas tercatat' => $dinasTercatat] as $label => $angka)
                    <div class="flex flex-col gap-1">
                        <dt class="text-xs text-primary-foreground/60">{{ $label }}</dt>
                        <dd class="text-2xl font-semibold tabular-nums text-khaki">{{ $angka }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="flex items-center justify-center px-6 py-12">
            <div class="flex w-full max-w-sm flex-col gap-8">
                <div class="flex flex-col gap-2">
                    <x-logo class="h-12 self-start rounded-lg bg-primary px-3 py-2 lg:hidden" />
                    <h2 class="text-2xl font-semibold tracking-tight text-primary">Masuk ke akun Anda</h2>
                    <p class="text-sm text-muted-foreground">Akun dibuat oleh Admin. Hubungi Admin jika lupa kata sandi.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
                    @csrf
                    <x-input label="Username" name="username" autocomplete="username" required autofocus class="h-11" />

                    <x-input label="Kata sandi" name="password" type="password" autocomplete="current-password" required class="h-11 pr-11">
                        <button type="button" data-toggle-password="password" aria-label="Tampilkan kata sandi"
                            class="absolute top-1/2 right-1 flex size-9 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground hover:bg-muted">
                            <x-icon name="eye" />
                        </button>
                    </x-input>

                    <x-button size="lg">Masuk</x-button>
                </form>
            </div>
        </section>
    </main>
</x-layouts.guest>
