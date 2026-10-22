<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_halaman_login_tampil(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke akun Anda');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_tidak_ada_registrasi_publik(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_admin_bisa_login(): void
    {
        $user = User::factory()->admin()->create(['username' => 'hendra.w']);

        $this->post('/login', ['username' => 'hendra.w', 'password' => 'password'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_staff_bisa_login(): void
    {
        $user = User::factory()->staff()->create(['username' => 'rina.k']);

        $this->post('/login', ['username' => 'rina.k', 'password' => 'password'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_kata_sandi_salah_ditolak(): void
    {
        User::factory()->staff()->create(['username' => 'rina.k']);

        $this->post('/login', ['username' => 'rina.k', 'password' => 'salah'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        User::factory()->staff()->inactive()->create(['username' => 'yoga.p']);

        $this->post('/login', ['username' => 'yoga.p', 'password' => 'password'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_dibatasi_setelah_5_percobaan(): void
    {
        $this->freezeTime();
        User::factory()->staff()->create(['username' => 'rina.k']);

        foreach (range(1, 5) as $_) {
            $this->post('/login', ['username' => 'rina.k', 'password' => 'salah']);
        }

        $this->post('/login', ['username' => 'rina.k', 'password' => 'password'])
            ->assertSessionHasErrors(['username' => 'Terlalu banyak percobaan. Coba lagi dalam 60 detik.']);
        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->post('/logout')
            ->assertRedirect('/login');
        $this->assertGuest();
    }
}
