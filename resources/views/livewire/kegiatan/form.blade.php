<div class="flex flex-col gap-6">
    <a href="{{ $kegiatan ? route('kegiatan.show', $kegiatan) : route('kegiatan.index') }}" class="flex w-fit items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
        <x-icon name="arrow-left" /> {{ $kegiatan ? 'Kembali ke detail' : 'Semua kegiatan' }}
    </a>

    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-semibold tracking-tight text-primary">{{ $kegiatan ? 'Edit kegiatan' : 'Tambah kegiatan' }}</h1>
        <p class="text-sm text-muted-foreground">
            {{ $kegiatan ? 'Ubah data utama dan tim. Status dan progres diperbarui di halaman detail.' : 'Kode kegiatan diisi manual, mis. mengikuti nomor surat tugas.' }}
        </p>
    </div>

    <form wire:submit="simpan" class="grid items-start gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2" title="Data kegiatan">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input label="Kode kegiatan" name="kode" wire:model="kode" class="font-mono" placeholder="mis. PP-016-2026" required />
                <div class="flex flex-col gap-2">
                    <label for="jenis" class="text-sm font-medium leading-none">Jenis kegiatan</label>
                    <x-select id="jenis" wire:model="jenisKegiatanId" required>
                        <option value="">Pilih jenis</option>
                        @foreach ($daftarJenis as $j)
                            <option value="{{ $j->id }}">{{ $j->kode }} · {{ $j->nama }}</option>
                        @endforeach
                    </x-select>
                    @error('jenisKegiatanId') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            <x-input label="Judul" name="judul" wire:model="judul" required />

            <div class="flex flex-col gap-2">
                <label for="deskripsi" class="text-sm font-medium leading-none">Deskripsi</label>
                <textarea id="deskripsi" wire:model="deskripsi" rows="4"
                    class="w-full rounded-md border border-input bg-card px-3 py-2 text-sm outline-none focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30"></textarea>
                @error('deskripsi') <p class="text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="flex flex-col gap-2">
                    <label for="prioritas" class="text-sm font-medium leading-none">Prioritas</label>
                    <x-select id="prioritas" wire:model="prioritas">
                        @foreach (App\Enums\Prioritas::cases() as $p)
                            <option value="{{ $p->value }}">{{ $p->label() }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-input label="Tanggal mulai" name="tanggalMulai" type="date" wire:model="tanggalMulai" />
                <x-input label="Target selesai" name="tanggalTargetSelesai" type="date" wire:model="tanggalTargetSelesai" />
            </div>
        </x-card>

        <x-card title="Tim" description="Staff yang ditugaskan dapat melihat dan memperbarui kegiatan ini." x-data="{ cari: '' }">
            <input type="search" x-model="cari" placeholder="Cari staff" aria-label="Cari staff"
                class="h-9 w-full rounded-md border border-input bg-card px-3 text-sm outline-none focus-visible:border-khaki-dark">
            <ul class="flex max-h-80 flex-col gap-1 overflow-y-auto">
                @forelse ($daftarStaff as $staff)
                    <li x-show="{{ Js::from(Str::lower($staff->name.' '.$staff->username)) }}.includes(cari.toLowerCase())">
                        <label class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-1.5 hover:bg-muted has-checked:bg-navy-soft">
                            <input type="checkbox" wire:model="tim" value="{{ $staff->id }}" class="size-4 accent-primary">
                            <x-avatar :user="$staff" />
                            <span class="flex flex-col leading-tight">
                                <span class="text-sm">{{ $staff->name }}</span>
                                <span class="font-mono text-xs text-muted-foreground">{{ $staff->username }}{{ $staff->is_active ? '' : ' · nonaktif' }}</span>
                            </span>
                        </label>
                    </li>
                @empty
                    <li class="text-sm text-muted-foreground">Belum ada staff aktif.</li>
                @endforelse
            </ul>
            <p class="text-xs text-muted-foreground"><span x-text="$wire.tim.length"></span> staff dipilih</p>
        </x-card>

        <div class="flex gap-2 lg:col-span-3">
            <x-button size="lg" wire:loading.attr="disabled">{{ $kegiatan ? 'Simpan perubahan' : 'Buat kegiatan' }}</x-button>
            <x-button variant="ghost" size="lg" :href="$kegiatan ? route('kegiatan.show', $kegiatan) : route('kegiatan.index')">Batal</x-button>
        </div>
    </form>
</div>
