<div class="flex flex-col gap-6">
    <a href="{{ $dinas ? route('dinas.show', $dinas) : route('dinas.index') }}" class="flex w-fit items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
        <x-icon name="arrow-left" /> {{ $dinas ? 'Kembali ke detail' : 'Semua dinas luar kota' }}
    </a>

    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-semibold tracking-tight text-primary">{{ $dinas ? 'Edit dinas luar kota' : 'Catat dinas luar kota' }}</h1>
        <p class="text-sm text-muted-foreground">Data yang disimpan langsung tercatat sebagai final.</p>
    </div>

    <form wire:submit="simpan" class="grid items-start gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2" title="Data perjalanan">
            <div class="flex flex-col gap-2">
                <label for="kegiatan" class="text-sm font-medium leading-none">Kegiatan</label>
                <x-select id="kegiatan" wire:model.live="kegiatanId" required>
                    <option value="">Pilih kegiatan</option>
                    @foreach ($daftarKegiatan as $k)
                        <option value="{{ $k->id }}">{{ $k->kode }} · {{ Str::limit($k->judul, 60) }}</option>
                    @endforeach
                </x-select>
                @error('kegiatanId') <p class="text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <x-input label="Kota tujuan" name="kotaTujuan" wire:model="kotaTujuan" required />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-input label="Tanggal berangkat" name="tanggalBerangkat" type="date" wire:model="tanggalBerangkat" required />
                <x-input label="Tanggal kembali" name="tanggalKembali" type="date" wire:model="tanggalKembali" required />
            </div>

            <div class="flex flex-col gap-2">
                <label for="keperluan" class="text-sm font-medium leading-none">Keperluan</label>
                <textarea id="keperluan" wire:model="keperluan" rows="3" required
                    class="w-full rounded-md border border-input bg-card px-3 py-2 text-sm outline-none focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30"></textarea>
                @error('keperluan') <p class="text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="lampiran" class="text-sm font-medium leading-none">Lampiran surat tugas <span class="font-normal text-muted-foreground">(opsional, PDF/JPG/PNG, maks. 5 MB)</span></label>
                @if ($dinas?->lampiran_surat_tugas && ! $lampiran)
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <a href="{{ route('dinas.lampiran', $dinas) }}" @class(['flex items-center gap-1.5 hover:underline', 'line-through text-muted-foreground' => $hapusLampiran])>
                            <x-icon name="paperclip" />{{ $dinas->namaLampiran() }}
                        </a>
                        <label class="flex items-center gap-1.5 text-danger"><input type="checkbox" wire:model.live="hapusLampiran" class="accent-danger"> Hapus lampiran</label>
                    </div>
                @endif
                <input id="lampiran" type="file" wire:model="lampiran" accept=".pdf,.jpg,.jpeg,.png"
                    class="text-sm file:mr-3 file:h-9 file:rounded-md file:border file:border-border file:bg-card file:px-3 file:text-sm file:font-medium hover:file:bg-muted">
                <p wire:loading wire:target="lampiran" class="text-xs text-muted-foreground">Mengunggah…</p>
                @error('lampiran') <p class="text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </x-card>

        <x-card title="Staff yang berangkat" description="Dinas ini terlihat oleh tim kegiatan terkait dan staff yang berangkat." x-data="{ cari: '' }">
            <input type="search" x-model="cari" placeholder="Cari staff" aria-label="Cari staff"
                class="h-9 w-full rounded-md border border-input bg-card px-3 text-sm outline-none focus-visible:border-khaki-dark">
            <ul class="flex max-h-80 flex-col gap-1 overflow-y-auto">
                @foreach ($daftarStaff as $s)
                    <li wire:key="staff-{{ $s->id }}" x-show="{{ Js::from(Str::lower($s->name.' '.$s->username)) }}.includes(cari.toLowerCase())">
                        <label class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-1.5 hover:bg-muted has-checked:bg-navy-soft">
                            <input type="checkbox" wire:model="staff" value="{{ $s->id }}" class="size-4 accent-primary">
                            <x-avatar :user="$s" />
                            <span class="flex flex-1 flex-col leading-tight">
                                <span class="text-sm">{{ $s->name }}</span>
                                <span class="font-mono text-xs text-muted-foreground">{{ $s->username }}{{ $s->is_active ? '' : ' · nonaktif' }}</span>
                            </span>
                            @if (in_array($s->id, $idTim))
                                <x-badge tone="khaki">Tim</x-badge>
                            @endif
                        </label>
                    </li>
                @endforeach
            </ul>
            @error('staff') <p class="text-xs text-danger">{{ $message }}</p> @enderror
        </x-card>

        <div class="flex gap-2 lg:col-span-3">
            <x-button size="lg" wire:loading.attr="disabled">{{ $dinas ? 'Simpan perubahan' : 'Catat dinas' }}</x-button>
            <x-button variant="ghost" size="lg" :href="$dinas ? route('dinas.show', $dinas) : route('dinas.index')">Batal</x-button>
        </div>
    </form>
</div>
