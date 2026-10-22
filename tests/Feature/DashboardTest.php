<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_tamu_tidak_bisa_membuka_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_melihat_kartu_user_aktif_dan_filter_staff(): void
    {
        $this->actingAs(User::factory()->admin()->create(['name' => 'Hendra Wijaya']))
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Halo, Hendra')
            ->assertSee('User aktif')
            ->assertSee('Semua staff')
            ->assertSee('Manajemen User');
    }

    public function test_staff_tidak_melihat_menu_pengaturan_maupun_filter_staff(): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Kegiatan tim')
            ->assertDontSee('User aktif')
            ->assertDontSee('Semua staff')
            ->assertDontSee('Manajemen User');
    }

    public function test_perlu_perhatian_staff_hanya_dari_timnya(): void
    {
        $rina = User::factory()->staff()->create();
        Kegiatan::factory()->hasAttached($rina, [], 'tim')->create([
            'judul' => 'Kegiatan Tim Rina', 'tanggal_target_selesai' => today()->subDays(3),
        ]);
        Kegiatan::factory()->create([
            'judul' => 'Kegiatan Tim Lain', 'tanggal_target_selesai' => today()->subDays(3),
        ]);

        Livewire::actingAs($rina)->test(Dashboard::class)
            ->assertSee('Kegiatan Tim Rina')
            ->assertDontSee('Kegiatan Tim Lain')
            ->assertViewHas('metrik', fn (array $m) => $m['terlambat'] === 1);
    }

    public function test_staff_tidak_bisa_memakai_filter_staff_untuk_mengintip_tim_lain(): void
    {
        $rina = User::factory()->staff()->create();
        $budi = User::factory()->staff()->create();
        Kegiatan::factory()->hasAttached($budi, [], 'tim')->create([
            'judul' => 'Kegiatan Tim Budi', 'tanggal_target_selesai' => today()->subDay(),
        ]);

        Livewire::actingAs($rina)->withQueryParams(['staff' => $budi->id])->test(Dashboard::class)
            ->assertSet('staffId', null)
            ->assertDontSee('Kegiatan Tim Budi');

        Livewire::actingAs($rina)->test(Dashboard::class)
            ->set('staffId', $budi->id)
            ->assertDontSee('Kegiatan Tim Budi');
    }

    public function test_filter_staff_admin_membatasi_data(): void
    {
        $admin = User::factory()->admin()->create();
        $rina = User::factory()->staff()->create();
        Kegiatan::factory()->hasAttached($rina, [], 'tim')->create(['judul' => 'Milik Rina', 'tanggal_target_selesai' => today()->addDay()]);
        Kegiatan::factory()->create(['judul' => 'Milik Orang Lain', 'tanggal_target_selesai' => today()->addDay()]);

        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertSee('Milik Orang Lain')
            ->set('staffId', $rina->id)
            ->assertSee('Milik Rina')
            ->assertDontSee('Milik Orang Lain');
    }

    public function test_staff_ditolak_di_halaman_khusus_admin(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get('/pengguna')->assertForbidden();
        $this->actingAs($staff)->get('/jenis-kegiatan')->assertForbidden();
    }
}
