<?php

namespace App\Livewire\Kegiatan;

use App\Enums\Prioritas;
use App\Models\JenisKegiatan;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Tambah dan edit data utama + tim kegiatan (Admin). Status/progres diubah di halaman detail. */
#[Layout('components.layouts.app')]
class Form extends Component
{
    public ?Kegiatan $kegiatan = null;

    public string $kode = '';

    public string $judul = '';

    public string $jenisKegiatanId = '';

    public string $deskripsi = '';

    public string $prioritas = 'sedang';

    public string $tanggalMulai = '';

    public string $tanggalTargetSelesai = '';

    /** @var list<int|string> */
    public array $tim = [];

    public function mount(?Kegiatan $kegiatan = null): void
    {
        if ($kegiatan?->exists) {
            $this->authorize('update', $kegiatan);
            $this->kegiatan = $kegiatan;
            $this->fill([
                'kode' => $kegiatan->kode,
                'judul' => $kegiatan->judul,
                'jenisKegiatanId' => (string) $kegiatan->jenis_kegiatan_id,
                'deskripsi' => (string) $kegiatan->deskripsi,
                'prioritas' => $kegiatan->prioritas->value,
                'tanggalMulai' => (string) $kegiatan->tanggal_mulai?->toDateString(),
                'tanggalTargetSelesai' => (string) $kegiatan->tanggal_target_selesai?->toDateString(),
                'tim' => $kegiatan->tim()->pluck('users.id')->map(fn ($id) => (string) $id)->all(),
            ]);
        } else {
            $this->authorize('create', Kegiatan::class);
            $this->kegiatan = null;
        }
    }

    public function simpan()
    {
        $this->authorize($this->kegiatan ? 'update' : 'create', $this->kegiatan ?? Kegiatan::class);

        $this->kode = trim($this->kode);
        $data = $this->validate([
            'kode' => ['required', 'string', 'max:50', Rule::unique('kegiatan', 'kode')->ignore($this->kegiatan?->id)],
            'judul' => ['required', 'string', 'max:255'],
            'jenisKegiatanId' => ['required', Rule::exists('jenis_kegiatan', 'id')],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'prioritas' => ['required', Rule::enum(Prioritas::class)],
            'tanggalMulai' => ['nullable', 'date'],
            'tanggalTargetSelesai' => ['nullable', 'date', 'after_or_equal:tanggalMulai'],
            'tim' => ['array'],
            'tim.*' => [Rule::exists('users', 'id')],
        ], [
            'kode.unique' => 'Kode kegiatan sudah dipakai. Gunakan kode lain.',
            'tanggalTargetSelesai.after_or_equal' => 'Target selesai tidak boleh sebelum tanggal mulai.',
        ], [
            'jenisKegiatanId' => 'jenis kegiatan',
            'tanggalMulai' => 'tanggal mulai',
            'tanggalTargetSelesai' => 'target selesai',
        ]);

        $kegiatan = DB::transaction(function () use ($data) {
            $kegiatan = $this->kegiatan ?? new Kegiatan;
            $kegiatan->fill([
                'kode' => $data['kode'],
                'judul' => $data['judul'],
                'jenis_kegiatan_id' => $data['jenisKegiatanId'],
                'deskripsi' => $data['deskripsi'] ?: null,
                'prioritas' => $data['prioritas'],
                'tanggal_mulai' => $data['tanggalMulai'] ?: null,
                'tanggal_target_selesai' => $data['tanggalTargetSelesai'] ?: null,
            ])->save();
            $kegiatan->tim()->sync($data['tim']);

            return $kegiatan;
        });

        session()->flash('status', $this->kegiatan ? 'Perubahan kegiatan disimpan.' : "Kegiatan {$kegiatan->kode} dibuat.");

        return $this->redirectRoute('kegiatan.show', $kegiatan);
    }

    public function render()
    {
        return view('livewire.kegiatan.form', [
            'daftarJenis' => JenisKegiatan::orderBy('id')->get(['id', 'kode', 'nama']),
            // Staff aktif, plus anggota tim yang sudah nonaktif agar tidak hilang diam-diam.
            'daftarStaff' => User::role('staff')
                ->where(fn ($q) => $q->where('is_active', true)->orWhereIn('id', $this->tim ?: [0]))
                ->orderBy('name')->get(['id', 'name', 'username', 'is_active']),
        ])->title($this->kegiatan ? 'Edit '.$this->kegiatan->kode : 'Tambah Kegiatan');
    }
}
