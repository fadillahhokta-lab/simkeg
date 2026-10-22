<?php

namespace Tests\Feature;

use App\Livewire\JenisKegiatan\Index as JenisIndex;
use App\Livewire\Pengguna\Index as PenggunaIndex;
use App\Models\JenisKegiatan;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $admin;

    private User $rina;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->rina = User::factory()->staff()->create(['username' => 'rina.k']);
    }

    // --- Akses ---

    public function test_staff_tidak_bisa_memakai_komponen_master_data_walau_dipanggil_langsung(): void
    {
        Livewire::actingAs($this->rina)->test(JenisIndex::class)->assertForbidden();
        Livewire::actingAs($this->rina)->test(PenggunaIndex::class)->assertForbidden();
    }

    // --- Jenis kegiatan ---

    public function test_admin_tambah_dan_edit_jenis(): void
    {
        Livewire::actingAs($this->admin)->test(JenisIndex::class)
            ->call('tambah')
            ->set('kode', 'ppk ')
            ->set('nama', 'Pengawasan Khusus')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('formTerbuka', false);

        $jenis = JenisKegiatan::where('kode', 'PPK')->sole();

        Livewire::actingAs($this->admin)->test(JenisIndex::class)
            ->call('edit', $jenis->id)
            ->set('nama', 'Pengawasan Khusus Lapangan')
            ->call('simpan')
            ->assertHasNoErrors();
        $this->assertSame('Pengawasan Khusus Lapangan', $jenis->fresh()->nama);
    }

    public function test_kode_jenis_unik_dan_tanpa_spasi(): void
    {
        Livewire::actingAs($this->admin)->test(JenisIndex::class)
            ->call('tambah')->set('kode', 'PP')->set('nama', 'Dobel')->call('simpan')
            ->assertHasErrors(['kode' => 'unique'])
            ->set('kode', 'P P')->call('simpan')
            ->assertHasErrors(['kode' => 'regex']);
    }

    public function test_jenis_yang_dipakai_kegiatan_tidak_bisa_dihapus(): void
    {
        $dipakai = JenisKegiatan::where('kode', 'PP')->sole();
        Kegiatan::factory()->create(['jenis_kegiatan_id' => $dipakai->id]);
        $kosong = JenisKegiatan::factory()->create();

        Livewire::actingAs($this->admin)->test(JenisIndex::class)
            ->call('hapus', $dipakai->id)
            ->call('hapus', $kosong->id);

        $this->assertModelExists($dipakai);
        $this->assertModelMissing($kosong);
    }

    // --- User ---

    public function test_admin_membuat_staff_yang_bisa_login(): void
    {
        Livewire::actingAs($this->admin)->test(PenggunaIndex::class)
            ->call('tambah')
            ->set('name', 'Budi Santoso')
            ->set('username', 'Budi.S')
            ->set('role', 'staff')
            ->set('password', 'rahasia123')
            ->call('simpan')
            ->assertHasNoErrors();

        $budi = User::where('username', 'budi.s')->sole();
        $this->assertFalse($budi->isAdmin());
        $this->assertTrue($budi->is_active);
        $this->assertTrue(Hash::check('rahasia123', $budi->password));
    }

    public function test_validasi_user(): void
    {
        Livewire::actingAs($this->admin)->test(PenggunaIndex::class)
            ->call('tambah')
            ->set('name', 'X')->set('username', 'rina.k')->set('password', 'pendek')
            ->call('simpan')
            ->assertHasErrors(['username' => 'unique', 'password'])
            ->set('username', 'nama dengan spasi')
            ->call('simpan')
            ->assertHasErrors(['username' => 'regex']);
    }

    public function test_nonaktif_tidak_bisa_login_dan_sesi_aktifnya_berakhir(): void
    {
        // Rina sedang login.
        $this->actingAs($this->rina)->get('/dashboard')->assertOk();

        Livewire::actingAs($this->admin)->test(PenggunaIndex::class)->call('toggleAktif', $this->rina->id);
        $this->assertFalse($this->rina->fresh()->is_active);

        // Request berikutnya dari sesi Rina langsung dikeluarkan.
        $this->actingAs($this->rina->fresh())->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();

        $this->post('/login', ['username' => 'rina.k', 'password' => 'password'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_riwayat_data_tetap_ada_setelah_nonaktif(): void
    {
        $kegiatan = Kegiatan::factory()->hasAttached($this->rina, [], 'tim')->create();

        Livewire::actingAs($this->admin)->test(PenggunaIndex::class)->call('toggleAktif', $this->rina->id);

        $this->assertTrue($kegiatan->tim()->whereKey($this->rina->id)->exists());
    }

    public function test_admin_tidak_bisa_menonaktifkan_atau_menurunkan_diri_sendiri(): void
    {
        Livewire::actingAs($this->admin)->test(PenggunaIndex::class)
            ->call('toggleAktif', $this->admin->id)
            ->call('edit', $this->admin->id)
            ->set('role', 'staff')
            ->call('simpan')
            ->assertHasErrors('role');

        $this->assertTrue($this->admin->fresh()->is_active);
        $this->assertTrue($this->admin->fresh()->isAdmin());
    }

    public function test_harus_ada_minimal_satu_admin_aktif(): void
    {
        // Admin dari seeder dinonaktifkan dulu, sehingga $this->admin satu-satunya admin aktif.
        User::where('username', '!=', $this->admin->username)->role('admin')->update(['is_active' => false]);
        $adminLain = User::factory()->admin()->create();
        $adminLain->update(['is_active' => true]);

        // Admin lain menonaktifkan $this->admin: boleh karena masih ada $adminLain.
        Livewire::actingAs($adminLain)->test(PenggunaIndex::class)->call('toggleAktif', $this->admin->id);
        $this->assertFalse($this->admin->fresh()->is_active);

        // Sekarang $adminLain satu-satunya; admin nonaktif tidak bisa mengubahnya, dan ia tidak bisa menurunkan dirinya.
        Livewire::actingAs($adminLain)->test(PenggunaIndex::class)
            ->call('edit', $adminLain->id)->set('role', 'staff')->call('simpan')->assertHasErrors('role');
        $this->assertTrue($adminLain->fresh()->isAdmin());
    }

    public function test_reset_sandi(): void
    {
        Livewire::actingAs($this->admin)->test(PenggunaIndex::class)
            ->call('bukaReset', $this->rina->id)
            ->call('sandiAcak')
            ->assertSet('sandiBaru', fn (string $s) => strlen($s) === 10)
            ->set('sandiBaru', 'sandibaru99')
            ->call('simpanReset')
            ->assertHasNoErrors();

        auth()->logout();
        $this->post('/login', ['username' => 'rina.k', 'password' => 'password'])->assertSessionHasErrors('username');
        $this->post('/login', ['username' => 'rina.k', 'password' => 'sandibaru99'])->assertRedirect('/dashboard');
    }

    public function test_perintah_reset_sandi_darurat(): void
    {
        $this->rina->update(['is_active' => false]);

        $this->artisan('simkeg:reset-sandi', ['username' => 'rina.k', 'sandi' => 'pendek'])->assertFailed();
        $this->artisan('simkeg:reset-sandi', ['username' => 'tidak.ada', 'sandi' => 'sandibaru99'])->assertFailed();
        $this->artisan('simkeg:reset-sandi', ['username' => 'rina.k', 'sandi' => 'sandibaru99'])->assertSuccessful();

        $this->assertTrue($this->rina->fresh()->is_active);
        $this->post('/login', ['username' => 'rina.k', 'password' => 'sandibaru99'])->assertRedirect('/dashboard');
    }
}
