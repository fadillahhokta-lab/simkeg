<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold tracking-tight text-primary">Jenis Kegiatan</h1>
            <p class="text-sm text-muted-foreground">Master data kategori kegiatan</p>
        </div>
        <x-button type="button" wire:click="tambah" icon="plus">Tambah Jenis</x-button>
    </div>

    <x-flash />

    <x-table>
        <x-slot:head>
            <th>Kode</th>
            <th>Nama</th>
            <th>Deskripsi</th>
            <th>Dipakai</th>
            <th class="text-right">Aksi</th>
        </x-slot:head>

        @forelse ($daftarJenis as $jenis)
            <tr wire:key="jenis-{{ $jenis->id }}">
                <td><x-badge mono>{{ $jenis->kode }}</x-badge></td>
                <td class="font-medium">{{ $jenis->nama }}</td>
                <td class="text-muted-foreground">{{ $jenis->deskripsi ?: '–' }}</td>
                <td class="whitespace-nowrap">
                    <a href="{{ route('kegiatan.index', ['jenis' => $jenis->id]) }}" class="hover:underline">{{ $jenis->kegiatan_count }} kegiatan</a>
                </td>
                <td class="text-right whitespace-nowrap">
                    <button type="button" wire:click="edit({{ $jenis->id }})" class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="Edit {{ $jenis->kode }}"><x-icon name="pencil" /></button>
                    <button type="button" wire:click="hapus({{ $jenis->id }})" wire:confirm="Hapus jenis {{ $jenis->kode }}?"
                        @disabled($jenis->kegiatan_count > 0) title="{{ $jenis->kegiatan_count > 0 ? 'Masih dipakai kegiatan' : 'Hapus' }}"
                        class="inline-flex size-8 items-center justify-center rounded-md text-danger hover:bg-danger-soft disabled:cursor-not-allowed disabled:opacity-30" aria-label="Hapus {{ $jenis->kode }}"><x-icon name="trash" /></button>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="py-10 text-center text-muted-foreground">Belum ada jenis kegiatan.</td></tr>
        @endforelse
    </x-table>

    <x-modal :show="$formTerbuka" :title="$editId ? 'Edit jenis kegiatan' : 'Tambah jenis kegiatan'" close="tutupForm">
        <form wire:submit="simpan" class="flex flex-col gap-4">
            <x-input label="Kode" name="kode" wire:model="kode" class="font-mono uppercase" placeholder="mis. PP" maxlength="20" required autofocus />
            <x-input label="Nama" name="nama" wire:model="nama" required />
            <div class="flex flex-col gap-2">
                <label for="deskripsi" class="text-sm font-medium leading-none">Deskripsi <span class="font-normal text-muted-foreground">(opsional)</span></label>
                <textarea id="deskripsi" wire:model="deskripsi" rows="3"
                    class="w-full rounded-md border border-input bg-card px-3 py-2 text-sm outline-none focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <x-button type="button" variant="ghost" wire:click="tutupForm">Batal</x-button>
                <x-button>{{ $editId ? 'Simpan' : 'Tambah' }}</x-button>
            </div>
        </form>
    </x-modal>
</div>
