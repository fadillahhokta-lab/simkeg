<?php

namespace App\Livewire\Dinas;

use App\Models\DinasLuarKota;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/** Catat dan edit dinas luar kota (Admin). Data langsung final, tanpa approval. */
#[Layout('components.layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public ?DinasLuarKota $dinas = null;

    public string $kegiatanId = '';

    public string $kotaTujuan = '';

    public string $tanggalBerangkat = '';

    public string $tanggalKembali = '';

    public string $keperluan = '';

    /** @var list<int|string> */
    public array $staff = [];

    public ?TemporaryUploadedFile $lampiran = null;

    public bool $hapusLampiran = false;

    public function mount(?DinasLuarKota $dinas = null): void
    {
        if ($dinas?->exists) {
            $this->authorize('update', $dinas);
            $this->dinas = $dinas;
            $this->fill([
                'kegiatanId' => (string) $dinas->kegiatan_id,
                'kotaTujuan' => $dinas->kota_tujuan,
                'tanggalBerangkat' => $dinas->tanggal_berangkat->toDateString(),
                'tanggalKembali' => $dinas->tanggal_kembali->toDateString(),
                'keperluan' => $dinas->keperluan,
                'staff' => $dinas->staff()->pluck('users.id')->map(fn ($id) => (string) $id)->all(),
            ]);
        } else {
            $this->authorize('create', DinasLuarKota::class);
            $this->dinas = null;
            $this->kegiatanId = (string) request()->query('kegiatan', '');
        }
    }

    public function simpan()
    {
        $this->authorize($this->dinas ? 'update' : 'create', $this->dinas ?? DinasLuarKota::class);

        $data = $this->validate([
            'kegiatanId' => ['required', Rule::exists('kegiatan', 'id')],
            'kotaTujuan' => ['required', 'string', 'max:255'],
            'tanggalBerangkat' => ['required', 'date'],
            'tanggalKembali' => ['required', 'date', 'after_or_equal:tanggalBerangkat'],
            'keperluan' => ['required', 'string', 'max:2000'],
            'staff' => ['required', 'array', 'min:1'],
            'staff.*' => [Rule::exists('users', 'id')],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'staff.required' => 'Pilih minimal satu staff yang berangkat.',
            'tanggalKembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal berangkat.',
            'lampiran.mimes' => 'Lampiran harus PDF, JPG, atau PNG.',
            'lampiran.max' => 'Ukuran lampiran maksimal 5 MB.',
        ], [
            'kegiatanId' => 'kegiatan',
            'kotaTujuan' => 'kota tujuan',
            'tanggalBerangkat' => 'tanggal berangkat',
            'tanggalKembali' => 'tanggal kembali',
        ]);

        $disk = Storage::disk(DinasLuarKota::DISK_LAMPIRAN);
        $lampiranLama = $this->dinas?->lampiran_surat_tugas;
        $lampiranBaru = $this->lampiran
            ? $this->lampiran->storeAs('surat-tugas/'.Str::uuid(), $this->namaFileAman($this->lampiran), DinasLuarKota::DISK_LAMPIRAN)
            : null;

        $dinas = DB::transaction(function () use ($data, $lampiranBaru, $lampiranLama) {
            $dinas = $this->dinas ?? new DinasLuarKota;
            $dinas->fill([
                'kegiatan_id' => $data['kegiatanId'],
                'kota_tujuan' => $data['kotaTujuan'],
                'tanggal_berangkat' => $data['tanggalBerangkat'],
                'tanggal_kembali' => $data['tanggalKembali'],
                'keperluan' => $data['keperluan'],
                'lampiran_surat_tugas' => $lampiranBaru ?? ($this->hapusLampiran ? null : $lampiranLama),
            ])->save();
            $dinas->staff()->sync($data['staff']);

            return $dinas;
        });

        // File lama dibuang hanya setelah data tersimpan.
        if ($lampiranLama && $lampiranLama !== $dinas->lampiran_surat_tugas) {
            $disk->delete($lampiranLama);
        }

        session()->flash('status', $this->dinas ? 'Perubahan dinas disimpan.' : "Dinas ke {$dinas->kota_tujuan} dicatat.");

        return $this->redirectRoute('dinas.show', $dinas);
    }

    private function namaFileAman(TemporaryUploadedFile $file): string
    {
        $nama = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'surat-tugas';

        return Str::limit($nama, 100, '').'.'.$file->guessExtension();
    }

    public function render()
    {
        $kegiatanTerpilih = $this->kegiatanId !== '' ? Kegiatan::with('tim:id')->find($this->kegiatanId) : null;

        return view('livewire.dinas.form', [
            'daftarKegiatan' => Kegiatan::orderBy('kode')->get(['id', 'kode', 'judul']),
            'daftarStaff' => User::role('staff')
                ->where(fn ($q) => $q->where('is_active', true)->orWhereIn('id', $this->staff ?: [0]))
                ->orderBy('name')->get(['id', 'name', 'username', 'is_active']),
            'idTim' => $kegiatanTerpilih?->tim->pluck('id')->all() ?? [],
        ])->title($this->dinas ? 'Edit Dinas' : 'Catat Dinas');
    }
}
