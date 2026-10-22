<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold tracking-tight text-primary">Manajemen User</h1>
            <p class="text-sm text-muted-foreground">{{ $jumlahAktif }} aktif dari {{ $jumlahTotal }} akun</p>
        </div>
        <x-button type="button" wire:click="tambah" icon="plus">Tambah User</x-button>
    </div>

    <x-flash />

    <div class="relative max-w-sm">
        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground" />
        <input type="search" wire:model.live.debounce.300ms="q" placeholder="Cari nama atau username" aria-label="Cari nama atau username"
            class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30">
    </div>

    <x-table>
        <x-slot:head>
            <th>Nama</th>
            <th>Username</th>
            <th>Role</th>
            <th>Kegiatan berjalan</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
        </x-slot:head>

        @forelse ($users as $user)
            @php $diriSendiri = $user->is(auth()->user()); @endphp
            <tr wire:key="user-{{ $user->id }}" @class(['text-muted-foreground' => ! $user->is_active])>
                <td>
                    <span class="flex items-center gap-3">
                        <x-avatar :user="$user" size="md" @class(['opacity-50' => ! $user->is_active]) />
                        <span class="font-medium">{{ $user->name }}@if ($diriSendiri) <span class="text-xs font-normal text-muted-foreground">(Anda)</span>@endif</span>
                    </span>
                </td>
                <td class="font-mono">{{ $user->username }}</td>
                <td>
                    @if ($user->isAdmin())
                        <span class="rounded bg-khaki px-1.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-primary">Admin</span>
                    @else
                        <span class="rounded bg-navy-soft px-1.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-primary">Staff</span>
                    @endif
                </td>
                <td class="tabular-nums">{{ $user->isAdmin() ? '–' : $user->berjalan_count }}</td>
                <td>
                    <button type="button" role="switch" aria-checked="{{ $user->is_active ? 'true' : 'false' }}" aria-label="Status {{ $user->name }}"
                        wire:click="toggleAktif({{ $user->id }})" @disabled($diriSendiri)
                        @if ($user->is_active && ! $diriSendiri) wire:confirm="Nonaktifkan {{ $user->name }}? Ia langsung keluar dan tidak bisa login, tapi riwayat datanya tetap tersimpan." @endif
                        class="group flex items-center gap-2 disabled:cursor-not-allowed disabled:opacity-60">
                        <span @class(['relative h-5 w-9 rounded-full transition-colors', 'bg-success' => $user->is_active, 'bg-input' => ! $user->is_active])>
                            <span @class(['absolute top-0.5 size-4 rounded-full bg-card shadow transition-all', 'left-[18px]' => $user->is_active, 'left-0.5' => ! $user->is_active])></span>
                        </span>
                        <span class="text-sm">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </button>
                </td>
                <td class="text-right whitespace-nowrap">
                    <button type="button" wire:click="edit({{ $user->id }})" class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="Edit {{ $user->name }}"><x-icon name="pencil" /></button>
                    <button type="button" wire:click="bukaReset({{ $user->id }})" class="inline-flex h-8 items-center gap-1.5 rounded-md px-2 text-sm text-muted-foreground hover:bg-muted hover:text-foreground"><x-icon name="lock" /> Reset sandi</button>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="py-10 text-center text-muted-foreground">Tidak ada user yang cocok.</td></tr>
        @endforelse
    </x-table>

    <x-modal :show="$formTerbuka" :title="$editId ? 'Edit user' : 'Tambah user'" close="tutupForm">
        <form wire:submit="simpan" class="flex flex-col gap-4">
            <x-input label="Nama" name="name" wire:model="name" required autofocus />
            <x-input label="Username" name="username" wire:model="username" class="font-mono" placeholder="mis. rina.k" required />
            <div class="flex flex-col gap-2">
                <label for="role" class="text-sm font-medium leading-none">Role</label>
                <x-select id="role" wire:model="role">
                    @foreach (App\Enums\Role::cases() as $r)
                        <option value="{{ $r->value }}">{{ $r->label() }}</option>
                    @endforeach
                </x-select>
                @error('role') <p class="text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            @unless ($editId)
                <x-input label="Kata sandi awal" name="password" type="password" wire:model="password" autocomplete="new-password" required />
                <p class="-mt-2 text-xs text-muted-foreground">Minimal 8 karakter. Sampaikan ke yang bersangkutan.</p>
            @endunless
            <div class="flex justify-end gap-2">
                <x-button type="button" variant="ghost" wire:click="tutupForm">Batal</x-button>
                <x-button>{{ $editId ? 'Simpan' : 'Buat akun' }}</x-button>
            </div>
        </form>
    </x-modal>

    <x-modal :show="(bool) $userReset" :title="'Reset sandi '.($userReset?->username ?? '')" close="tutupForm">
        <form wire:submit="simpanReset" class="flex flex-col gap-4">
            <p class="text-sm text-muted-foreground">
                Sandi lama tidak berlaku lagi{{ $userReset?->is(auth()->user()) ? '' : ', dan '.$userReset?->name.' keluar dari semua perangkat' }}.
            </p>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <x-input label="Kata sandi baru" name="sandiBaru" wire:model="sandiBaru" class="font-mono" autocomplete="new-password" required />
                </div>
                <x-button type="button" variant="outline" wire:click="sandiAcak">Buat acak</x-button>
            </div>
            <div class="flex justify-end gap-2">
                <x-button type="button" variant="ghost" wire:click="tutupForm">Batal</x-button>
                <x-button>Simpan sandi</x-button>
            </div>
        </form>
    </x-modal>
</div>
