<?php

namespace App\Livewire\Kegiatan;

use App\Enums\StatusKegiatan;
use App\Models\Kegiatan;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Kegiatan $kegiatan;

    // Form update progres (Admin + anggota tim)
    public string $status = '';

    public int $progres = 0;

    public string $detailStatus = '';

    /** Catatan baru; dikosongkan setelah disimpan. */
    public string $catatan = '';

    /** Hasil dan rekomendasi bersifat tetap (bisa disunting), wajib saat status Selesai. */
    public string $hasil = '';

    public string $rekomendasi = '';

    public function mount(Kegiatan $kegiatan): void
    {
        $this->authorize('view', $kegiatan);
        $this->isiForm();
    }

    public function simpanProgres(): void
    {
        $this->authorize('updateProgres', $this->kegiatan);

        $data = $this->validate([
            'status' => ['required', Rule::enum(StatusKegiatan::class)],
            'progres' => ['required', 'integer', 'between:0,100'],
            'detailStatus' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'hasil' => ['nullable', 'required_if:status,'.StatusKegiatan::Selesai->value, 'string', 'max:5000'],
            'rekomendasi' => ['nullable', 'required_if:status,'.StatusKegiatan::Selesai->value, 'string', 'max:5000'],
        ], [
            'hasil.required_if' => 'Isi hasil kegiatan sebelum menandai Selesai.',
            'rekomendasi.required_if' => 'Isi rekomendasi sebelum menandai Selesai.',
        ]);

        $status = StatusKegiatan::from($data['status']);
        $this->kegiatan->fill([
            'status' => $status,
            'progres' => $data['progres'],
            'detail_status' => $data['detailStatus'] ?: null,
            'hasil' => $data['hasil'] ?: null,
            'rekomendasi' => $data['rekomendasi'] ?: null,
            'tanggal_selesai_aktual' => $status === StatusKegiatan::Selesai
                ? ($this->kegiatan->tanggal_selesai_aktual ?? today())
                : null,
        ]);
        if (filled($data['catatan'])) {
            $this->kegiatan->catatan = $data['catatan'];
        }

        if ($this->kegiatan->isDirty()) {
            $this->kegiatan->save();
            session()->flash('status', 'Pembaruan disimpan.');
        }

        $this->isiForm();
    }

    public function hapus()
    {
        $this->authorize('delete', $this->kegiatan);

        if ($this->kegiatan->dinasLuarKota()->exists()) {
            session()->flash('error', 'Kegiatan tidak bisa dihapus karena masih punya data dinas luar kota. Hapus data dinasnya dulu.');

            return null;
        }

        $kode = $this->kegiatan->kode;
        $this->kegiatan->delete();
        session()->flash('status', "Kegiatan {$kode} dihapus.");

        return $this->redirectRoute('kegiatan.index');
    }

    private function isiForm(): void
    {
        $this->status = $this->kegiatan->status->value;
        $this->progres = $this->kegiatan->progres;
        $this->detailStatus = (string) $this->kegiatan->detail_status;
        $this->hasil = (string) $this->kegiatan->hasil;
        $this->rekomendasi = (string) $this->kegiatan->rekomendasi;
        $this->catatan = '';
    }

    public function render()
    {
        $this->kegiatan->load(['jenisKegiatan', 'tim', 'logs.user', 'dinasLuarKota.staff', 'pembuat', 'pengubah']);

        return view('livewire.kegiatan.show', [
            'logTerakhir' => $this->kegiatan->logs->first(),
        ])->title($this->kegiatan->kode);
    }
}
