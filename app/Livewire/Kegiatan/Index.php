<?php

namespace App\Livewire\Kegiatan;

use App\Enums\StatusKegiatan;
use App\Models\JenisKegiatan;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Kegiatan')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $jenis = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $staff = '';

    #[Url(except: '')]
    public string $dari = '';

    #[Url(except: '')]
    public string $sampai = '';

    #[Url(except: false)]
    public bool $terlambat = false;

    public function mount()
    {
        // Pencarian global dengan kode yang persis cocok langsung membuka detail.
        if ($this->q !== '') {
            $match = Kegiatan::visibleTo(auth()->user())->where('kode', $this->q)->first();
            if ($match) {
                return $this->redirectRoute('kegiatan.show', $match);
            }
        }
    }

    public function updated(string $property): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset('q', 'jenis', 'status', 'staff', 'dari', 'sampai', 'terlambat');
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $kegiatan = Kegiatan::query()
            ->visibleTo($user)
            ->with(['jenisKegiatan', 'tim'])
            ->when($this->q !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('kode', 'like', "%{$this->q}%")
                ->orWhere('judul', 'like', "%{$this->q}%")))
            ->when($this->jenis !== '', fn (Builder $q) => $q->where('jenis_kegiatan_id', $this->jenis))
            ->when(StatusKegiatan::tryFrom($this->status), fn (Builder $q, StatusKegiatan $s) => $q->where('status', $s))
            ->when($this->staff !== '', fn (Builder $q) => $q->whereHas('tim', fn (Builder $t) => $t->whereKey($this->staff)))
            // Rentang periode: kegiatan yang periodenya bersinggungan dengan rentang filter.
            ->when($this->dari !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w->whereNull('tanggal_target_selesai')->orWhereDate('tanggal_target_selesai', '>=', $this->dari)))
            ->when($this->sampai !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w->whereNull('tanggal_mulai')->orWhereDate('tanggal_mulai', '<=', $this->sampai)))
            ->when($this->terlambat, fn (Builder $q) => $q->terlambat())
            ->orderBy('tanggal_target_selesai')
            ->paginate(15);

        return view('livewire.kegiatan.index', [
            'kegiatan' => $kegiatan,
            'totalTerlihat' => Kegiatan::visibleTo($user)->count(),
            'daftarJenis' => JenisKegiatan::orderBy('id')->get(['id', 'kode', 'nama']),
            // Staff hanya bisa memfilter dengan rekan yang ada di tim kegiatannya.
            'daftarStaff' => User::role('staff')
                ->when(! $user->isAdmin(), fn (Builder $q) => $q->whereHas('kegiatan', fn (Builder $k) => $k->visibleTo($user)))
                ->orderBy('name')->get(['id', 'name']),
            'adaFilter' => $this->q.$this->jenis.$this->status.$this->staff.$this->dari.$this->sampai !== '' || $this->terlambat,
        ]);
    }
}
