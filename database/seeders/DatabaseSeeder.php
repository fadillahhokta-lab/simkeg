<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\JenisKegiatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as RoleModel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed data wajib: role, Admin pertama (dari env), dan jenis kegiatan awal.
     * Aman dijalankan berulang.
     */
    public function run(): void
    {
        foreach (Role::cases() as $role) {
            RoleModel::findOrCreate($role->value, 'web');
        }

        $password = env('ADMIN_PASSWORD');
        if (! $password) {
            $this->command?->warn('ADMIN_PASSWORD kosong, Admin pertama tidak dibuat.');
        } elseif (! User::where('username', env('ADMIN_USERNAME', 'admin'))->exists()) {
            User::create([
                'name' => 'Administrator',
                'username' => env('ADMIN_USERNAME', 'admin'),
                'password' => $password,
            ])->assignRole(Role::Admin->value);
        }

        $jenis = [
            ['PP', 'Pengaduan & Pengawasan Reguler', 'Pengawasan rutin dan penanganan pengaduan masyarakat.'],
            ['SA', 'Supervisi & Audit', 'Audit kinerja, keuangan, dan supervisi internal.'],
            ['PHP', 'Penanganan Hasil Pemeriksaan', 'Tindak lanjut rekomendasi hasil pemeriksaan.'],
            ['PSLH', 'Pengawasan Sarana Lingkungan Hidup', 'Inspeksi dan verifikasi sarana lingkungan hidup.'],
        ];
        foreach ($jenis as [$kode, $nama, $deskripsi]) {
            JenisKegiatan::firstOrCreate(['kode' => $kode], ['nama' => $nama, 'deskripsi' => $deskripsi]);
        }
    }
}
