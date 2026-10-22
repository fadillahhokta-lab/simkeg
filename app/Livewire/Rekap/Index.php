<?php

namespace App\Livewire\Rekap;

use App\Rekap\Rekap;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Rekap & Export')]
class Index extends Component
{
    #[Url]
    public string $jenis = 'kegiatan';

    #[Url]
    public string $dari = '';

    #[Url]
    public string $sampai = '';

    public function mount(): void
    {
        if (! array_key_exists($this->jenis, Rekap::JENIS)) {
            $this->jenis = 'kegiatan';
        }
        if ($this->dari === '' || $this->sampai === '') {
            $this->pilihPeriode('bulan');
        }
    }

    public function updatedJenis(): void
    {
        if (! array_key_exists($this->jenis, Rekap::JENIS)) {
            $this->jenis = 'kegiatan';
        }
    }

    public function pilihPeriode(string $preset): void
    {
        [$dari, $sampai] = match ($preset) {
            'minggu' => [now()->startOfWeek(), now()->endOfWeek()],
            'bulan' => [now()->startOfMonth(), now()->endOfMonth()],
            'kuartal' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'tahun' => [now()->startOfYear(), now()->endOfYear()],
        };
        $this->dari = $dari->toDateString();
        $this->sampai = $sampai->toDateString();
    }

    public function render()
    {
        $dari = rescue(fn () => Carbon::parse($this->dari)->startOfDay(), report: false);
        $sampai = rescue(fn () => Carbon::parse($this->sampai)->endOfDay(), report: false);
        $periodeValid = $dari && $sampai && $sampai->gte($dari);
        $query = ['jenis' => $this->jenis, 'dari' => $this->dari, 'sampai' => $this->sampai];

        return view('livewire.rekap.index', [
            'rekap' => $periodeValid ? (new Rekap(auth()->user(), $dari, $sampai))->buat($this->jenis) : null,
            'periode' => $periodeValid ? $dari->translatedFormat('j M Y').' – '.$sampai->translatedFormat('j M Y') : null,
            'urlExcel' => route('rekap.export', ['format' => 'xlsx', ...$query]),
            'urlPdf' => route('rekap.export', ['format' => 'pdf', ...$query]),
            'presets' => [
                'minggu' => ['Minggu ini', now()->startOfWeek(), now()->endOfWeek()],
                'bulan' => ['Bulan ini', now()->startOfMonth(), now()->endOfMonth()],
                'kuartal' => ['Kuartal ini', now()->startOfQuarter(), now()->endOfQuarter()],
                'tahun' => ['Tahun '.now()->year, now()->startOfYear(), now()->endOfYear()],
            ],
        ]);
    }
}
