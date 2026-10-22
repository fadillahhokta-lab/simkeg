<?php

namespace App\Livewire\Dinas;

use App\Models\DinasLuarKota;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public DinasLuarKota $dinas;

    public function mount(DinasLuarKota $dinas): void
    {
        $this->authorize('view', $dinas);
    }

    public function hapus()
    {
        $this->authorize('delete', $this->dinas);

        if ($this->dinas->lampiran_surat_tugas) {
            Storage::disk(DinasLuarKota::DISK_LAMPIRAN)->delete($this->dinas->lampiran_surat_tugas);
        }
        $this->dinas->delete();
        session()->flash('status', "Dinas ke {$this->dinas->kota_tujuan} dihapus.");

        return $this->redirectRoute('dinas.index');
    }

    public function render()
    {
        $this->dinas->load(['kegiatan.jenisKegiatan', 'staff', 'dicatatOleh', 'pengubah']);

        return view('livewire.dinas.show')->title('Dinas '.$this->dinas->kota_tujuan);
    }
}
