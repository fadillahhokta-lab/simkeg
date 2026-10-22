<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold tracking-tight text-primary">Halo, {{ Str::before($user->name, ' ') }}</h1>
            <p class="text-sm text-muted-foreground">
                {{ $user->isAdmin() ? 'Ringkasan seluruh kegiatan unit' : 'Ringkasan kegiatan tim Anda' }}
                · data per {{ today()->translatedFormat('j F Y') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($user->isAdmin())
                <x-select label="Staff" wire:model.live="staffId" class="w-48">
                    <option value="">Semua staff</option>
                    @foreach ($daftarStaff as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </x-select>
            @endif
            <x-select label="Periode" wire:model.live="tahun" class="w-40">
                @foreach ($daftarTahun as $t)
                    <option value="{{ $t }}">Tahun {{ $t }}</option>
                @endforeach
            </x-select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
        @if ($user->isAdmin())
            <x-metric label="User aktif" :value="$metrik['userAktif']" :hint="'dari '.$metrik['userTotal'].' akun'" icon="users" />
        @else
            <x-metric label="Kegiatan tim" :value="$metrik['totalKegiatan']" hint="pada periode ini" icon="clipboard-list" />
        @endif
        <x-metric label="Kegiatan berjalan" :value="$metrik['berjalan']" hint="sedang dikerjakan" icon="circle-play"
            :href="route('kegiatan.index', ['status' => 'berjalan'])" />
        <x-metric label="Selesai" :value="$metrik['selesai']" hint="pada periode ini" icon="circle-check" tone="success"
            :href="route('kegiatan.index', ['status' => 'selesai'])" />
        <x-metric label="Terlambat" :value="$metrik['terlambat']" hint="melewati tanggal target" icon="triangle-alert" tone="danger"
            :href="route('kegiatan.index', ['terlambat' => 1])" />
    </div>

    {{-- wire:key berubah saat filter berubah agar grafik dibuat ulang dengan data baru --}}
    @php $chartKey = $tahun.'-'.$staffId; @endphp

    <div class="grid gap-4 lg:grid-cols-5">
        <x-card title="Kegiatan per jenis" description="Jumlah kegiatan tiap jenis, dipecah berdasarkan status" class="lg:col-span-3">
            <div class="h-64" wire:key="jenis-{{ $chartKey }}"><canvas x-data="chart(@js($chartJenis))"></canvas></div>
        </x-card>

        <x-card title="Status kegiatan" description="Proporsi status pada periode terpilih" class="lg:col-span-2">
            <div class="relative h-64" wire:key="status-{{ $chartKey }}">
                <canvas x-data="chart(@js($chartStatus))"></canvas>
                <div class="pointer-events-none absolute inset-x-0 top-[38%] flex -translate-y-1/2 flex-col items-center">
                    <span class="text-3xl font-semibold tabular-nums text-primary">{{ $metrik['totalKegiatan'] }}</span>
                    <span class="text-xs text-muted-foreground">kegiatan</span>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid gap-4 lg:grid-cols-5">
        <x-card title="Dinas luar kota per bulan" class="lg:col-span-3"
            :description="'Jumlah perjalanan dinas berdasarkan tanggal berangkat ('.$chartDinas['rentang'].') · total '.$chartDinas['total'].' perjalanan'">
            <x-slot:actions>
                <a href="{{ route('dinas.index') }}" class="whitespace-nowrap text-sm font-medium text-primary hover:underline">Lihat semua</a>
            </x-slot:actions>
            <div class="h-64" wire:key="dinas-{{ $chartKey }}"><canvas x-data="chart(@js($chartDinas))"></canvas></div>
        </x-card>

        <x-card title="Perlu perhatian" description="Terlambat atau tenggat dalam 7 hari" class="lg:col-span-2">
            @if ($perluPerhatian->isEmpty())
                <p class="py-6 text-center text-sm text-muted-foreground">Tidak ada kegiatan yang mendekati atau melewati target.</p>
            @else
                <ul class="flex flex-col divide-y divide-border">
                    @foreach ($perluPerhatian as $item)
                        @php $hari = $item->hariTerlambat(); @endphp
                        <li>
                            <a href="{{ route('kegiatan.index', ['q' => $item->kode]) }}" class="flex items-start gap-3 rounded-md py-3 hover:bg-muted/50">
                                <span @class([
                                    'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-md',
                                    'bg-danger-soft text-danger' => $hari > 0,
                                    'bg-khaki-soft text-khaki-dark' => $hari <= 0,
                                ])><x-icon name="calendar-clock" /></span>
                                <span class="flex min-w-0 flex-1 flex-col gap-1">
                                    <span class="flex items-center gap-2">
                                        <span class="rounded bg-navy-soft px-1.5 py-0.5 font-mono text-[11px] font-semibold text-primary">{{ $item->jenisKegiatan->kode }}</span>
                                        <span class="font-mono text-xs text-muted-foreground">{{ $item->kode }}</span>
                                    </span>
                                    <span class="truncate text-sm font-medium">{{ $item->judul }}</span>
                                    <span class="text-xs text-muted-foreground">Target {{ $item->tanggal_target_selesai->translatedFormat('j M Y') }}</span>
                                </span>
                                @if ($hari > 0)
                                    <x-badge tone="danger" icon="triangle-alert">Terlambat {{ $hari }} hari</x-badge>
                                @elseif ($hari === 0)
                                    <x-badge tone="khaki">Hari ini</x-badge>
                                @else
                                    <span class="shrink-0 text-xs font-medium text-khaki-dark">{{ abs($hari) }} hari lagi</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
</div>
