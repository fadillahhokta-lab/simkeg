<?php

namespace App\Rekap;

use App\Enums\Role;
use App\Enums\StatusKegiatan;
use App\Models\DinasLuarKota;
use App\Models\JenisKegiatan;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Sumber tunggal data rekap. Pratinjau, Excel, dan PDF memakai hasil yang sama.
 * Lingkup data mengikuti akses user (Admin: semua, Staff: kegiatan/dinas timnya).
 *
 * Hasil: ['judul' => string, 'tabel' => list<array{judul: string, kolom: list<string>, baris: list<list<string|int|null>>}>]
 */
class Rekap
{
    public const JENIS = [
        'kegiatan' => ['Rekap kegiatan', 'Ringkasan per jenis dan daftar kegiatan', 'clipboard-list'],
        'dinas' => ['Rekap dinas luar kota', 'Perjalanan dinas beserta durasi', 'plane'],
        'beban' => ['Beban kerja staff', 'Jumlah kegiatan, progres, dan hari dinas per staff', 'users'],
    ];

    public function __construct(
        private User $user,
        private Carbon $dari,
        private Carbon $sampai,
    ) {}

    public function buat(string $jenis): array
    {
        return match ($jenis) {
            'kegiatan' => $this->kegiatan(),
            'dinas' => $this->dinas(),
            'beban' => $this->bebanKerja(),
        };
    }

    private function kegiatan(): array
    {
        $kegiatan = $this->queryKegiatan()->with(['jenisKegiatan', 'tim'])->orderBy('kode')->get();

        $ringkasan = JenisKegiatan::orderBy('id')->get()->map(function (JenisKegiatan $jenis) use ($kegiatan) {
            $milik = $kegiatan->where('jenis_kegiatan_id', $jenis->id);
            $perStatus = fn (StatusKegiatan $s) => $milik->filter(fn (Kegiatan $k) => $k->status === $s)->count();

            return [
                $jenis->kode,
                $jenis->nama,
                $milik->count(),
                $perStatus(StatusKegiatan::BelumMulai),
                $perStatus(StatusKegiatan::Berjalan),
                $perStatus(StatusKegiatan::Ditunda),
                $perStatus(StatusKegiatan::Selesai),
                $milik->filter->isTerlambat()->count(),
                $milik->isEmpty() ? '–' : round($milik->avg('progres')).'%',
            ];
        })->all();

        return [
            'judul' => self::JENIS['kegiatan'][0],
            'tabel' => [
                [
                    'judul' => 'Ringkasan per jenis',
                    'kolom' => ['Kode', 'Jenis', 'Total', 'Belum Mulai', 'Berjalan', 'Ditunda', 'Selesai', 'Terlambat', 'Rata-rata progres'],
                    'baris' => $ringkasan,
                ],
                [
                    'judul' => 'Daftar kegiatan',
                    'kolom' => ['Kode', 'Judul', 'Jenis', 'Prioritas', 'Status', 'Progres', 'Mulai', 'Target', 'Selesai aktual', 'Tim', 'Hasil', 'Rekomendasi'],
                    'baris' => $kegiatan->map(fn (Kegiatan $k) => [
                        $k->kode,
                        $k->judul,
                        $k->jenisKegiatan->kode,
                        $k->prioritas->label(),
                        $k->status->label().($k->isTerlambat() ? ' (Terlambat)' : ''),
                        $k->progres.'%',
                        $this->tanggal($k->tanggal_mulai),
                        $this->tanggal($k->tanggal_target_selesai),
                        $this->tanggal($k->tanggal_selesai_aktual),
                        $k->tim->pluck('name')->implode(', '),
                        $k->hasil,
                        $k->rekomendasi,
                    ])->all(),
                ],
            ],
        ];
    }

    private function dinas(): array
    {
        $dinas = $this->queryDinas()->with(['kegiatan', 'staff'])->orderBy('tanggal_berangkat')->get();

        return [
            'judul' => self::JENIS['dinas'][0],
            'tabel' => [[
                'judul' => 'Daftar perjalanan dinas',
                'kolom' => ['Berangkat', 'Kembali', 'Durasi (hari)', 'Kota tujuan', 'Kegiatan', 'Staff', 'Keperluan', 'Status', 'Lampiran'],
                'baris' => $dinas->map(fn (DinasLuarKota $d) => [
                    $this->tanggal($d->tanggal_berangkat),
                    $this->tanggal($d->tanggal_kembali),
                    $d->durasiHari(),
                    $d->kota_tujuan,
                    $d->kegiatan->kode,
                    $d->staff->pluck('name')->implode(', '),
                    $d->keperluan,
                    $d->statusLabel(),
                    $d->lampiran_surat_tugas ? 'Ada' : '–',
                ])->all(),
            ]],
        ];
    }

    private function bebanKerja(): array
    {
        $kegiatan = $this->queryKegiatan()->with('tim:id')->get(['id', 'status', 'progres', 'tanggal_target_selesai']);
        $dinas = $this->queryDinas()->with('staff:id')->get(['id', 'tanggal_berangkat', 'tanggal_kembali']);

        // Staff: semua staff (Admin) atau rekan yang muncul di data yang boleh dilihat (Staff).
        $staff = User::role(Role::Staff->value)
            ->when(! $this->user->isAdmin(), fn (Builder $q) => $q->whereIn('id', $kegiatan->flatMap->tim->pluck('id')->push($this->user->id)))
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'is_active']);

        return [
            'judul' => self::JENIS['beban'][0],
            'tabel' => [[
                'judul' => 'Beban kerja per staff',
                'kolom' => ['Nama', 'Username', 'Total kegiatan', 'Berjalan', 'Selesai', 'Terlambat', 'Rata-rata progres', 'Perjalanan dinas', 'Hari dinas'],
                'baris' => $staff->map(function (User $s) use ($kegiatan, $dinas) {
                    /** @var Collection<int, Kegiatan> $milik */
                    $milik = $kegiatan->filter(fn (Kegiatan $k) => $k->tim->contains('id', $s->id));
                    $berangkat = $dinas->filter(fn (DinasLuarKota $d) => $d->staff->contains('id', $s->id));

                    return [
                        $s->name.($s->is_active ? '' : ' (nonaktif)'),
                        $s->username,
                        $milik->count(),
                        $milik->filter(fn (Kegiatan $k) => $k->status === StatusKegiatan::Berjalan)->count(),
                        $milik->filter(fn (Kegiatan $k) => $k->status === StatusKegiatan::Selesai)->count(),
                        $milik->filter->isTerlambat()->count(),
                        $milik->isEmpty() ? '–' : round($milik->avg('progres')).'%',
                        $berangkat->count(),
                        $berangkat->sum(fn (DinasLuarKota $d) => $d->durasiHari()),
                    ];
                })->all(),
            ]],
        ];
    }

    /** Kegiatan yang periodenya bersinggungan dengan rentang rekap. */
    private function queryKegiatan(): Builder
    {
        return Kegiatan::query()
            ->visibleTo($this->user)
            ->where(fn (Builder $q) => $q->whereNull('tanggal_mulai')->orWhereDate('tanggal_mulai', '<=', $this->sampai))
            ->where(fn (Builder $q) => $q->whereNull('tanggal_target_selesai')->orWhereDate('tanggal_target_selesai', '>=', $this->dari));
    }

    /** Dinas yang perjalanannya bersinggungan dengan rentang rekap. */
    private function queryDinas(): Builder
    {
        return DinasLuarKota::query()
            ->visibleTo($this->user)
            ->whereDate('tanggal_berangkat', '<=', $this->sampai)
            ->whereDate('tanggal_kembali', '>=', $this->dari);
    }

    private function tanggal(?Carbon $tanggal): string
    {
        return $tanggal?->translatedFormat('j M Y') ?? '–';
    }
}
