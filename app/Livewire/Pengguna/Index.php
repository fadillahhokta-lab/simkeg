<?php

namespace App\Livewire\Pengguna;

use App\Enums\Role;
use App\Enums\StatusKegiatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Manajemen User')]
class Index extends Component
{
    #[Url(except: '')]
    public string $q = '';

    // Form tambah/edit
    public bool $formTerbuka = false;

    public ?int $editId = null;

    public string $name = '';

    public string $username = '';

    public string $role = 'staff';

    public string $password = '';

    // Form reset sandi
    public ?int $resetId = null;

    public string $sandiBaru = '';

    public function mount(): void
    {
        $this->authorize('kelola-master');
    }

    public function tambah(): void
    {
        $this->authorize('kelola-master');
        $this->reset('editId', 'name', 'username', 'role', 'password');
        $this->resetValidation();
        $this->formTerbuka = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('kelola-master');
        $user = User::findOrFail($id);
        $this->fill([
            'editId' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->isAdmin() ? Role::Admin->value : Role::Staff->value,
            'password' => '',
        ]);
        $this->resetValidation();
        $this->formTerbuka = true;
    }

    public function tutupForm(): void
    {
        $this->formTerbuka = false;
        $this->resetId = null;
        $this->sandiBaru = '';
    }

    public function simpan(): void
    {
        $this->authorize('kelola-master');

        $this->username = Str::lower(trim($this->username));
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($this->editId)],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => [$this->editId ? 'nullable' : 'required', Password::min(8)],
        ], [
            'username.regex' => 'Username hanya huruf kecil, angka, titik, garis bawah, atau strip.',
            'username.unique' => 'Username sudah dipakai.',
        ], ['name' => 'nama', 'password' => 'kata sandi']);

        $user = $this->editId ? User::findOrFail($this->editId) : null;

        if ($user && $user->isAdmin() && $data['role'] !== Role::Admin->value) {
            if ($user->is(auth()->user())) {
                $this->addError('role', 'Anda tidak bisa menurunkan role akun Anda sendiri.');

                return;
            }
            if ($this->jumlahAdminAktif() <= 1 && $user->is_active) {
                $this->addError('role', 'Harus ada minimal satu Admin aktif.');

                return;
            }
        }

        DB::transaction(function () use ($user, $data) {
            $user ??= new User(['is_active' => true]);
            $user->fill(['name' => $data['name'], 'username' => $data['username']]);
            if (! $user->exists) {
                $user->password = $data['password'];
            }
            $user->save();
            $user->syncRoles($data['role']);
        });

        session()->flash('status', $this->editId ? "Data {$data['name']} diperbarui." : "Akun {$data['username']} dibuat.");
        $this->formTerbuka = false;
    }

    public function toggleAktif(int $id): void
    {
        $this->authorize('kelola-master');
        $user = User::findOrFail($id);

        if ($user->is(auth()->user())) {
            session()->flash('error', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');

            return;
        }
        if ($user->is_active && $user->isAdmin() && $this->jumlahAdminAktif() <= 1) {
            session()->flash('error', 'Harus ada minimal satu Admin aktif.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        if (! $user->is_active) {
            $this->akhiriSesi($user);
        }

        session()->flash('status', $user->is_active ? "{$user->name} diaktifkan." : "{$user->name} dinonaktifkan. Riwayat datanya tetap tersimpan.");
    }

    public function bukaReset(int $id): void
    {
        $this->authorize('kelola-master');
        $this->resetId = User::findOrFail($id)->id;
        $this->sandiBaru = '';
        $this->resetValidation();
    }

    public function sandiAcak(): void
    {
        // Mudah dibacakan: tanpa karakter yang mirip (0/O, 1/l).
        $this->sandiBaru = collect(str_split('abcdefghjkmnpqrstuvwxyz23456789'))->random(10)->implode('');
    }

    public function simpanReset(): void
    {
        $this->authorize('kelola-master');
        $this->validate(['sandiBaru' => ['required', Password::min(8)]], [], ['sandiBaru' => 'kata sandi baru']);

        $user = User::findOrFail($this->resetId);
        $user->update(['password' => $this->sandiBaru]);
        if (! $user->is(auth()->user())) {
            $this->akhiriSesi($user);
        }

        session()->flash('status', "Kata sandi {$user->username} direset. Sampaikan sandi baru ke yang bersangkutan.");
        $this->tutupForm();
    }

    private function jumlahAdminAktif(): int
    {
        return User::role(Role::Admin->value)->active()->count();
    }

    /** Keluarkan user dari semua perangkat (sesi disimpan di database). */
    private function akhiriSesi(User $user): void
    {
        DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        $user->forceFill(['remember_token' => Str::random(60)])->save();
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->withCount(['kegiatan as berjalan_count' => fn (Builder $q) => $q->where('status', StatusKegiatan::Berjalan)])
            ->when($this->q !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('name', 'like', "%{$this->q}%")
                ->orWhere('username', 'like', "%{$this->q}%")))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('livewire.pengguna.index', [
            'users' => $users,
            'jumlahAktif' => User::active()->count(),
            'jumlahTotal' => User::count(),
            'userReset' => $this->resetId ? User::find($this->resetId) : null,
        ]);
    }
}
