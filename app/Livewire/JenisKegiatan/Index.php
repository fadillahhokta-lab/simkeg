<?php

namespace App\Livewire\JenisKegiatan;

use App\Models\JenisKegiatan;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Jenis Kegiatan')]
class Index extends Component
{
    public bool $formTerbuka = false;

    public ?int $editId = null;

    public string $kode = '';

    public string $nama = '';

    public string $deskripsi = '';

    public function mount(): void
    {
        $this->authorize('kelola-master');
    }

    public function tambah(): void
    {
        $this->authorize('kelola-master');
        $this->reset('editId', 'kode', 'nama', 'deskripsi');
        $this->resetValidation();
        $this->formTerbuka = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('kelola-master');
        $jenis = JenisKegiatan::findOrFail($id);
        $this->fill(['editId' => $jenis->id, 'kode' => $jenis->kode, 'nama' => $jenis->nama, 'deskripsi' => (string) $jenis->deskripsi]);
        $this->resetValidation();
        $this->formTerbuka = true;
    }

    public function tutupForm(): void
    {
        $this->formTerbuka = false;
    }

    public function simpan(): void
    {
        $this->authorize('kelola-master');

        $this->kode = strtoupper(trim($this->kode));
        $data = $this->validate([
            'kode' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/', Rule::unique('jenis_kegiatan', 'kode')->ignore($this->editId)],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ], [
            'kode.regex' => 'Kode hanya boleh huruf dan angka, tanpa spasi.',
            'kode.unique' => 'Kode jenis sudah dipakai.',
        ]);

        JenisKegiatan::updateOrCreate(['id' => $this->editId], [...$data, 'deskripsi' => $data['deskripsi'] ?: null]);

        session()->flash('status', $this->editId ? "Jenis {$data['kode']} diperbarui." : "Jenis {$data['kode']} ditambahkan.");
        $this->formTerbuka = false;
    }

    public function hapus(int $id): void
    {
        $this->authorize('kelola-master');
        $jenis = JenisKegiatan::withCount('kegiatan')->findOrFail($id);

        if ($jenis->kegiatan_count > 0) {
            session()->flash('error', "Jenis {$jenis->kode} masih dipakai {$jenis->kegiatan_count} kegiatan, jadi tidak bisa dihapus.");

            return;
        }

        $jenis->delete();
        session()->flash('status', "Jenis {$jenis->kode} dihapus.");
    }

    public function render()
    {
        return view('livewire.jenis-kegiatan.index', [
            'daftarJenis' => JenisKegiatan::withCount('kegiatan')->orderBy('id')->get(),
        ]);
    }
}
