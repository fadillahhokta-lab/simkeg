<?php

namespace Tests\Feature;

use App\Models\JenisKegiatan;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasangTest extends TestCase
{
    use RefreshDatabase;

    public function test_pasang_membuat_admin_jenis_dan_data_contoh(): void
    {
        $this->artisan('simkeg:pasang', ['--sandi-admin' => 'sandiAdmin123', '--data-contoh' => true])
            ->expectsOutputToContain('Instalasi selesai')
            ->assertSuccessful();

        $admin = User::where('username', 'admin')->sole();
        $this->assertTrue($admin->isAdmin());
        $this->assertSame(4, JenisKegiatan::count());
        $this->assertSame(12, Kegiatan::count());

        $this->post('/login', ['username' => 'admin', 'password' => 'sandiAdmin123'])->assertRedirect('/dashboard');
    }

    public function test_pasang_ulang_tidak_mengubah_admin_yang_sudah_ada(): void
    {
        $this->artisan('simkeg:pasang', ['--sandi-admin' => 'sandiAdmin123', '--tanpa-data-contoh' => true])->assertSuccessful();
        $this->artisan('simkeg:pasang', ['--sandi-admin' => 'sandiLainLagi9', '--tanpa-data-contoh' => true])
            ->expectsOutputToContain('sudah ada, dilewati')
            ->assertSuccessful();

        $this->assertSame(1, User::where('username', 'admin')->count());
        $this->assertSame(0, Kegiatan::count());
        $this->post('/login', ['username' => 'admin', 'password' => 'sandiAdmin123'])->assertRedirect('/dashboard');
    }

    public function test_sandi_admin_pendek_ditanyakan_ulang(): void
    {
        $this->artisan('simkeg:pasang', ['--sandi-admin' => 'pendek', '--tanpa-data-contoh' => true])
            ->expectsOutputToContain('Sandi minimal 8 karakter')
            ->expectsQuestion('Buat sandi untuk akun admin (minimal 8 karakter, tidak tampil saat diketik)', 'sandiBaru123')
            ->expectsQuestion('Ketik ulang sandi', 'sandiBaru123')
            ->assertSuccessful();

        $this->post('/login', ['username' => 'admin', 'password' => 'sandiBaru123'])->assertRedirect('/dashboard');
    }

    public function test_cek_database(): void
    {
        $this->artisan('simkeg:cek-database')->assertSuccessful();
    }
}
