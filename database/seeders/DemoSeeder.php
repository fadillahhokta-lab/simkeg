<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\DinasLuarKota;
use App\Models\JenisKegiatan;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Data contoh mengikuti prototipe v0. Hanya untuk development:
 * php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DemoSeeder tidak boleh dijalankan di production.');

            return;
        }

        $this->call(DatabaseSeeder::class);

        $users = collect([
            ['hendra.w', 'Hendra Wijaya', Role::Admin, true],
            ['rina.k', 'Rina Kartika', Role::Staff, true],
            ['budi.s', 'Budi Santoso', Role::Staff, true],
            ['dewi.a', 'Dewi Anggraini', Role::Staff, true],
            ['agus.p', 'Agus Pratama', Role::Staff, true],
            ['maya.p', 'Maya Puspita', Role::Staff, true],
            ['fajar.n', 'Fajar Nugroho', Role::Staff, true],
            ['lestari.w', 'Lestari Wulandari', Role::Staff, true],
            ['yoga.p', 'Yoga Permana', Role::Staff, false],
            ['sari.h', 'Sari Handayani', Role::Staff, true],
        ])->mapWithKeys(function (array $row) {
            [$username, $name, $role, $active] = $row;
            $user = User::firstOrCreate(
                ['username' => $username],
                ['name' => $name, 'password' => 'demo1234', 'is_active' => $active],
            );
            $user->syncRoles($role->value);

            return [$name => $user];
        });

        $jenis = JenisKegiatan::pluck('id', 'kode');
        $tim = fn (string $names) => $users->only(explode(', ', $names))->pluck('id')->all();

        $kegiatan = [
            ['PSLH-009-2026', 'Verifikasi Dokumen AMDAL Proyek Jalan Tol', 'sedang', 'Agus Pratama, Budi Santoso', 30, 'ditunda', '2026-09-10'],
            ['PP-014-2026', 'Pengawasan Reguler Pasar Tradisional Wilayah Timur', 'tinggi', 'Rina Kartika, Budi Santoso, Agus Pratama', 60, 'berjalan', '2026-09-18'],
            ['PSLH-010-2026', 'Pemantauan Kualitas Udara Kawasan Perkotaan', 'rendah', 'Agus Pratama, Sari Handayani', 100, 'selesai', '2026-06-30'],
            ['PP-012-2026', 'Penanganan Pengaduan Masyarakat Sektor Perizinan', 'sedang', 'Rina Kartika, Lestari Wulandari', 100, 'selesai', '2026-07-31'],
            ['SA-005-2026', 'Supervisi Penyusunan Laporan Keuangan Semester I', 'rendah', 'Fajar Nugroho, Maya Puspita', 100, 'selesai', '2026-08-15'],
            ['PHP-003-2026', 'Tindak Lanjut Rekomendasi Pemeriksaan BPK', 'sedang', 'Dewi Anggraini, Maya Puspita', 80, 'berjalan', '2026-09-30'],
            ['PHP-004-2026', 'Pemantauan Tindak Lanjut Temuan Inspektorat', 'rendah', 'Rina Kartika, Maya Puspita, Lestari Wulandari', 25, 'berjalan', '2026-10-02'],
            ['PP-013-2026', 'Evaluasi Layanan Pengaduan Daring', 'sedang', 'Rina Kartika, Lestari Wulandari, Maya Puspita', 70, 'berjalan', '2026-10-09'],
            ['SA-007-2026', 'Audit Kinerja Pengelolaan Anggaran Triwulan III', 'tinggi', 'Dewi Anggraini, Fajar Nugroho, Rina Kartika', 45, 'berjalan', '2026-10-15'],
            ['PP-015-2026', 'Pengawasan Distribusi Pupuk Bersubsidi', 'tinggi', 'Budi Santoso, Fajar Nugroho, Sari Handayani', 15, 'berjalan', '2026-10-30'],
            ['PSLH-011-2026', 'Inspeksi IPAL Kawasan Industri Cikarang', 'sedang', 'Budi Santoso, Agus Pratama, Sari Handayani', 0, 'belum_mulai', '2026-11-20'],
            ['SA-008-2026', 'Audit Pengadaan Barang dan Jasa TA 2026', 'tinggi', 'Dewi Anggraini, Fajar Nugroho', 0, 'belum_mulai', '2026-12-15'],
        ];

        foreach ($kegiatan as [$kode, $judul, $prioritas, $anggota, $progres, $status, $target]) {
            $target = Carbon::parse($target);
            $item = Kegiatan::updateOrCreate(['kode' => $kode], [
                'judul' => $judul,
                'jenis_kegiatan_id' => $jenis[explode('-', $kode)[0]],
                'prioritas' => $prioritas,
                'progres' => $progres,
                'status' => $status,
                'tanggal_mulai' => $target->copy()->subMonths(2),
                'tanggal_target_selesai' => $target,
                'tanggal_selesai_aktual' => $status === 'selesai' ? $target : null,
            ]);
            $item->tim()->sync($tim($anggota));
        }

        // Kolom terakhir: nama file lampiran contoh (null = tanpa lampiran).
        $dinas = [
            ['PHP-004-2026', 'Denpasar', 'Pemantauan tindak lanjut temuan di satker Bali.', 'Rina Kartika, Maya Puspita', '2026-09-29', '2026-10-01', 'ST-PHP-004-01'],
            ['PP-015-2026', 'Bandar Lampung', 'Pengawasan kios pengecer pupuk bersubsidi.', 'Budi Santoso, Sari Handayani', '2026-09-22', '2026-09-25', 'ST-PP-015-01'],
            ['SA-007-2026', 'Medan', 'Pengumpulan data dan wawancara satker wilayah Sumatera Utara.', 'Dewi Anggraini, Fajar Nugroho', '2026-09-14', '2026-09-17', 'ST-SA-007-01'],
            ['PP-014-2026', 'Malang', 'Pengawasan lapangan pasar 3 dan 4.', 'Budi Santoso, Agus Pratama', '2026-09-08', '2026-09-10', 'ST-PP-014-02'],
            ['PP-014-2026', 'Surabaya', 'Pengawasan lapangan pasar 1 dan 2 serta koordinasi dengan dinas perdagangan.', 'Rina Kartika, Budi Santoso', '2026-08-11', '2026-08-13', 'ST-PP-014-01'],
            ['PHP-003-2026', 'Makassar', 'Verifikasi bukti tindak lanjut rekomendasi.', 'Dewi Anggraini', '2026-08-03', '2026-08-05', null],
            ['PSLH-009-2026', 'Semarang', 'Peninjauan lokasi proyek jalan tol.', 'Agus Pratama', '2026-07-20', '2026-07-22', 'ST-PSLH-009-01'],
            ['SA-005-2026', 'Bandung', 'Reviu laporan keuangan satker Jawa Barat.', 'Maya Puspita, Fajar Nugroho', '2026-07-07', '2026-07-08', 'ST-SA-005-01'],
            ['PP-012-2026', 'Yogyakarta', 'Klarifikasi pengaduan dengan dinas perizinan.', 'Rina Kartika, Lestari Wulandari', '2026-06-16', '2026-06-18', null],
            ['PSLH-010-2026', 'Samarinda', 'Pengambilan sampel lanjutan.', 'Agus Pratama', '2026-05-25', '2026-05-27', null],
            ['PSLH-010-2026', 'Balikpapan', 'Pengambilan sampel kualitas udara.', 'Agus Pratama, Sari Handayani', '2026-05-18', '2026-05-21', null],
        ];

        $kegiatanId = Kegiatan::pluck('id', 'kode');
        foreach ($dinas as [$kode, $kota, $keperluan, $staff, $berangkat, $kembali, $lampiran]) {
            $path = null;
            if ($lampiran) {
                $path = "surat-tugas/demo/{$lampiran}.pdf";
                Storage::disk(DinasLuarKota::DISK_LAMPIRAN)->put($path, $this->pdfContoh("Surat tugas {$lampiran}"));
            }

            $item = DinasLuarKota::updateOrCreate(
                ['kegiatan_id' => $kegiatanId[$kode], 'kota_tujuan' => $kota, 'tanggal_berangkat' => $berangkat],
                [
                    'tanggal_kembali' => $kembali,
                    'keperluan' => $keperluan,
                    'lampiran_surat_tugas' => $path,
                    'created_by' => $users['Hendra Wijaya']->id,
                ],
            );
            $item->staff()->sync($tim($staff));
        }
    }

    /** PDF satu halaman berisi satu baris teks, cukup untuk mencoba unduhan. */
    private function pdfContoh(string $teks): string
    {
        $stream = "BT /F1 18 Tf 72 720 Td ({$teks}) Tj ET";
        $objek = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offset = [];
        foreach ($objek as $i => $isi) {
            $offset[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$isi}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objek) + 1)."\n0000000000 65535 f \n";
        foreach ($offset as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }

        return $pdf."trailer\n<< /Size ".(count($objek) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
