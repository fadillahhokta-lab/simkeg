<?php

namespace App\Livewire;

use App\Enums\StatusKegiatan;
use App\Models\DinasLuarKota;
use App\Models\JenisKegiatan;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    private const STATUS_COLOR = [
        'belum_mulai' => 'chart-4',
        'berjalan' => 'chart-1',
        'ditunda' => 'chart-2',
        'selesai' => 'success',
    ];

    #[Url]
    public int $tahun;

    /** Filter per staff, hanya untuk Admin. */
    #[Url(as: 'staff')]
    public ?int $staffId = null;

    public function mount(): void
    {
        $this->tahun ??= now()->year;

        if (! auth()->user()->isAdmin()) {
            $this->staffId = null;
        }
    }

    public function render()
    {
        $user = auth()->user();
        $awal = Carbon::create($this->tahun)->startOfYear();
        $akhir = $awal->copy()->endOfYear();

        // Kegiatan yang periodenya bersinggungan dengan tahun terpilih, sesuai lingkup akses.
        $kegiatan = fn (): Builder => Kegiatan::query()
            ->visibleTo($user)
            ->when($this->staffId && $user->isAdmin(), fn (Builder $q) => $q->whereHas('tim', fn (Builder $t) => $t->whereKey($this->staffId)))
            ->where(fn (Builder $q) => $q->whereNull('tanggal_mulai')->orWhereDate('tanggal_mulai', '<=', $akhir))
            ->where(fn (Builder $q) => $q->whereNull('tanggal_target_selesai')->orWhereDate('tanggal_target_selesai', '>=', $awal));

        $perStatus = $kegiatan()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('livewire.dashboard', [
            'user' => $user,
            'metrik' => [
                'userAktif' => User::active()->count(),
                'userTotal' => User::count(),
                'totalKegiatan' => $perStatus->sum(),
                'berjalan' => $perStatus['berjalan'] ?? 0,
                'selesai' => $perStatus['selesai'] ?? 0,
                'terlambat' => $kegiatan()->terlambat()->count(),
            ],
            'chartJenis' => $this->chartJenis($kegiatan()),
            'chartStatus' => $this->chartStatus($perStatus),
            'chartDinas' => $this->chartDinas($user, $awal),
            'perluPerhatian' => $kegiatan()
                ->with('jenisKegiatan')
                ->where('status', '!=', StatusKegiatan::Selesai)
                ->whereDate('tanggal_target_selesai', '<=', today()->addDays(7))
                ->orderBy('tanggal_target_selesai')
                ->limit(6)
                ->get(),
            'daftarStaff' => $user->isAdmin() ? User::role('staff')->orderBy('name')->get(['id', 'name']) : collect(),
            'daftarTahun' => range(now()->year + 1, now()->year - 4),
        ]);
    }

    /** Batang bertumpuk: jumlah kegiatan per jenis, dipecah per status. */
    private function chartJenis(Builder $kegiatan): array
    {
        $jenis = JenisKegiatan::orderBy('id')->pluck('kode', 'id');
        $hitung = $kegiatan->toBase()
            ->selectRaw('jenis_kegiatan_id, status, count(*) as total')
            ->groupBy('jenis_kegiatan_id', 'status')
            ->get();

        return [
            'type' => 'bar',
            'stacked' => true,
            'labels' => $jenis->values(),
            'datasets' => collect(StatusKegiatan::cases())->map(fn (StatusKegiatan $status) => [
                'label' => $status->label(),
                'color' => self::STATUS_COLOR[$status->value],
                'data' => $jenis->keys()->map(fn (int $id) => (int) $hitung
                    ->where('jenis_kegiatan_id', $id)->where('status', $status->value)->sum('total')),
            ]),
        ];
    }

    private function chartStatus($perStatus): array
    {
        $cases = collect(StatusKegiatan::cases());

        return [
            'type' => 'doughnut',
            'labels' => $cases->map->label(),
            'datasets' => [[
                'label' => 'Kegiatan',
                'color' => $cases->map(fn (StatusKegiatan $s) => self::STATUS_COLOR[$s->value]),
                'data' => $cases->map(fn (StatusKegiatan $s) => (int) ($perStatus[$s->value] ?? 0)),
            ]],
        ];
    }

    /** Jumlah perjalanan dinas per bulan berdasarkan tanggal berangkat. */
    private function chartDinas(User $user, Carbon $awal): array
    {
        $bulanTerakhir = $awal->isSameYear(now()) ? now()->month : 12;

        $perBulan = DinasLuarKota::query()
            ->visibleTo($user)
            ->when($this->staffId && $user->isAdmin(), fn (Builder $q) => $q->whereHas('staff', fn (Builder $s) => $s->whereKey($this->staffId)))
            ->whereYear('tanggal_berangkat', $awal->year)
            ->get(['tanggal_berangkat'])
            ->countBy(fn (DinasLuarKota $d) => $d->tanggal_berangkat->month);

        $bulan = collect(range(1, $bulanTerakhir));

        return [
            'type' => 'bar',
            'stacked' => false,
            'labels' => $bulan->map(fn (int $b) => Carbon::create(null, $b)->translatedFormat('M')),
            'datasets' => [[
                'label' => 'Perjalanan dinas',
                'color' => 'chart-1',
                'data' => $bulan->map(fn (int $b) => $perBulan[$b] ?? 0),
            ]],
            'total' => $perBulan->sum(),
            'rentang' => Carbon::create(null, 1)->translatedFormat('M').'–'.Carbon::create(null, $bulanTerakhir)->translatedFormat('M').' '.$awal->year,
        ];
    }
}
