<?php

namespace App\Livewire\Dinas;

use App\Models\DinasLuarKota;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Dinas Luar Kota')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $kegiatan = '';

    #[Url(except: '')]
    public string $staff = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $dari = '';

    #[Url(except: '')]
    public string $sampai = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset('q', 'kegiatan', 'staff', 'status', 'dari', 'sampai');
        $this->resetPage();
    }

    public function hapus(int $id): void
    {
        $dinas = DinasLuarKota::findOrFail($id);
        $this->authorize('delete', $dinas);

        if ($dinas->lampiran_surat_tugas) {
            Storage::disk(DinasLuarKota::DISK_LAMPIRAN)->delete($dinas->lampiran_surat_tugas);
        }
        $dinas->delete();

        session()->flash('status', "Dinas ke {$dinas->kota_tujuan} dihapus.");
    }

    public function render()
    {
        $user = auth()->user();
        $base = fn (): Builder => DinasLuarKota::query()->visibleTo($user);

        $dinas = $base()
            ->with(['kegiatan', 'staff'])
            ->when($this->q !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('kota_tujuan', 'like', "%{$this->q}%")
                ->orWhere('keperluan', 'like', "%{$this->q}%")
                ->orWhereHas('kegiatan', fn (Builder $k) => $k->where('kode', 'like', "%{$this->q}%"))))
            ->when($this->kegiatan !== '', fn (Builder $q) => $q->where('kegiatan_id', $this->kegiatan))
            ->when($this->staff !== '', fn (Builder $q) => $q->whereHas('staff', fn (Builder $s) => $s->whereKey($this->staff)))
            ->when(isset(DinasLuarKota::STATUS[$this->status]), fn (Builder $q) => $q->denganStatus($this->status))
            // Periode: perjalanan yang bersinggungan dengan rentang filter.
            ->when($this->dari !== '', fn (Builder $q) => $q->whereDate('tanggal_kembali', '>=', $this->dari))
            ->when($this->sampai !== '', fn (Builder $q) => $q->whereDate('tanggal_berangkat', '<=', $this->sampai))
            ->latest('tanggal_berangkat')
            ->paginate(15);

        return view('livewire.dinas.index', [
            'dinas' => $dinas,
            // Staff yang ikut dinas di luar timnya tidak boleh membuka detail kegiatannya.
            'kegiatanTerbuka' => $user->isAdmin() ? null : Kegiatan::visibleTo($user)->pluck('id')->all(),
            'jumlahBerjalan' => $base()->denganStatus('berjalan')->count(),
            'jumlahTerjadwal' => $base()->denganStatus('terjadwal')->count(),
            'daftarKegiatan' => Kegiatan::visibleTo($user)->orderBy('kode')->get(['id', 'kode', 'judul']),
            'daftarStaff' => User::role('staff')
                ->when(! $user->isAdmin(), fn (Builder $q) => $q->whereHas('kegiatan', fn (Builder $k) => $k->visibleTo($user)))
                ->orderBy('name')->get(['id', 'name']),
            'adaFilter' => $this->q.$this->kegiatan.$this->staff.$this->status.$this->dari.$this->sampai !== '',
        ]);
    }
}
