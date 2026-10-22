<?php

namespace Tests\Feature;

use App\Enums\StatusKegiatan;
use App\Livewire\Kegiatan\Form;
use App\Livewire\Kegiatan\Index;
use App\Livewire\Kegiatan\Show;
use App\Models\DinasLuarKota;
use App\Models\JenisKegiatan;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KegiatanTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $admin;

    private User $rina;

    private Kegiatan $milikRina;

    private Kegiatan $milikLain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->rina = User::factory()->staff()->create();
        $this->milikRina = Kegiatan::factory()->hasAttached($this->rina, [], 'tim')->create(['kode' => 'PP-001-2026', 'judul' => 'Kegiatan Rina']);
        $this->milikLain = Kegiatan::factory()->hasAttached(User::factory()->staff(), [], 'tim')->create(['kode' => 'SA-002-2026', 'judul' => 'Kegiatan Lain']);
    }

    // --- Daftar ---

    public function test_staff_hanya_melihat_kegiatan_timnya_di_daftar(): void
    {
        Livewire::actingAs($this->rina)->test(Index::class)
            ->assertSee('Kegiatan Rina')
            ->assertDontSee('Kegiatan Lain')
            ->assertDontSee('Tambah Kegiatan');
    }

    public function test_admin_melihat_semua_dan_tombol_tambah(): void
    {
        Livewire::actingAs($this->admin)->test(Index::class)
            ->assertSee('Kegiatan Rina')
            ->assertSee('Kegiatan Lain')
            ->assertSee('Tambah Kegiatan');
    }

    public function test_filter_status_dan_terlambat(): void
    {
        $this->milikLain->update(['status' => StatusKegiatan::Selesai]);
        $this->milikRina->update(['tanggal_target_selesai' => today()->subDay()]);

        Livewire::actingAs($this->admin)->test(Index::class)
            ->set('status', 'selesai')
            ->assertSee('Kegiatan Lain')->assertDontSee('Kegiatan Rina')
            ->set('status', '')->set('terlambat', true)
            ->assertSee('Kegiatan Rina')->assertDontSee('Kegiatan Lain');
    }

    public function test_cari_kode_persis_langsung_membuka_detail(): void
    {
        Livewire::actingAs($this->rina)->withQueryParams(['q' => 'PP-001-2026'])->test(Index::class)
            ->assertRedirect(route('kegiatan.show', $this->milikRina));

        // Kode kegiatan tim lain tidak boleh membocorkan detail.
        Livewire::actingAs($this->rina)->withQueryParams(['q' => 'SA-002-2026'])->test(Index::class)
            ->assertNoRedirect()
            ->assertDontSee('Kegiatan Lain');
    }

    // --- Detail ---

    public function test_staff_bisa_membuka_detail_kegiatan_timnya_tapi_tidak_yang_lain(): void
    {
        $this->actingAs($this->rina)->get('/kegiatan/PP-001-2026')->assertOk()->assertSee('Update progres')->assertDontSee('Hapus');
        $this->actingAs($this->rina)->get('/kegiatan/SA-002-2026')->assertForbidden();
    }

    public function test_kode_dengan_garis_miring_bisa_dibuka(): void
    {
        Kegiatan::factory()->create(['kode' => '001/ST/2026', 'judul' => 'Kode Bergaris Miring']);

        $this->actingAs($this->admin)->get('/kegiatan/001/ST/2026')->assertOk()->assertSee('Kode Bergaris Miring');
        $this->actingAs($this->admin)->get('/kegiatan/001/ST/2026/edit')->assertOk()->assertSee('Edit kegiatan');
    }

    public function test_staff_tim_bisa_update_progres_dan_tercatat_di_riwayat(): void
    {
        Livewire::actingAs($this->rina)->test(Show::class, ['kegiatan' => $this->milikRina])
            ->set('status', 'selesai')
            ->set('progres', 100)
            ->set('catatan', 'Laporan akhir terkirim')
            ->set('hasil', '12 pasar diperiksa')
            ->set('rekomendasi', 'Tera ulang timbangan')
            ->call('simpanProgres')
            ->assertHasNoErrors()
            ->assertSee('Laporan akhir terkirim')
            ->assertSee('12 pasar diperiksa');

        $kegiatan = $this->milikRina->fresh();
        $this->assertSame(StatusKegiatan::Selesai, $kegiatan->status);
        $this->assertSame(100, $kegiatan->progres);
        $this->assertTrue($kegiatan->tanggal_selesai_aktual->isToday());
        $this->assertSame('Tera ulang timbangan', $kegiatan->rekomendasi);
        $log = $kegiatan->logs()->sole();
        $this->assertSame($this->rina->id, $log->user_id);
        $this->assertSame(['dari' => null, 'ke' => '12 pasar diperiksa'], $log->perubahan['hasil']);
    }

    public function test_hasil_dan_rekomendasi_wajib_saat_selesai(): void
    {
        Livewire::actingAs($this->rina)->test(Show::class, ['kegiatan' => $this->milikRina])
            ->set('status', 'selesai')
            ->call('simpanProgres')
            ->assertHasErrors(['hasil' => 'required_if', 'rekomendasi' => 'required_if']);
        $this->assertNotSame(StatusKegiatan::Selesai, $this->milikRina->fresh()->status);

        // Status lain: boleh kosong, boleh diisi kapan saja.
        Livewire::actingAs($this->rina)->test(Show::class, ['kegiatan' => $this->milikRina])
            ->set('status', 'berjalan')
            ->set('hasil', 'Temuan sementara')
            ->call('simpanProgres')
            ->assertHasNoErrors();
        $this->assertSame('Temuan sementara', $this->milikRina->fresh()->hasil);
    }

    public function test_staff_di_luar_tim_tidak_bisa_update_progres(): void
    {
        Livewire::actingAs($this->rina)->test(Show::class, ['kegiatan' => $this->milikLain])->assertForbidden();
    }

    public function test_progres_harus_0_sampai_100(): void
    {
        Livewire::actingAs($this->rina)->test(Show::class, ['kegiatan' => $this->milikRina])
            ->set('progres', 150)
            ->call('simpanProgres')
            ->assertHasErrors('progres');
    }

    public function test_staff_tidak_bisa_hapus(): void
    {
        Livewire::actingAs($this->rina)->test(Show::class, ['kegiatan' => $this->milikRina])
            ->call('hapus')
            ->assertForbidden();
        $this->assertModelExists($this->milikRina);
    }

    public function test_admin_bisa_hapus_kecuali_masih_ada_dinas(): void
    {
        DinasLuarKota::factory()->for($this->milikLain)->create();

        Livewire::actingAs($this->admin)->test(Show::class, ['kegiatan' => $this->milikLain])
            ->call('hapus')
            ->assertNoRedirect();
        $this->assertModelExists($this->milikLain);

        Livewire::actingAs($this->admin)->test(Show::class, ['kegiatan' => $this->milikRina])
            ->call('hapus')
            ->assertRedirect(route('kegiatan.index'));
        $this->assertModelMissing($this->milikRina);
    }

    // --- Tambah / edit ---

    public function test_staff_tidak_bisa_membuka_form(): void
    {
        $this->actingAs($this->rina)->get('/kegiatan/baru')->assertForbidden();
        $this->actingAs($this->rina)->get('/kegiatan/PP-001-2026/edit')->assertForbidden();
    }

    public function test_admin_membuat_kegiatan_dengan_tim(): void
    {
        $jenis = JenisKegiatan::where('kode', 'PHP')->first();

        Livewire::actingAs($this->admin)->test(Form::class)
            ->set('kode', 'PHP-010-2026')
            ->set('judul', 'Tindak Lanjut Baru')
            ->set('jenisKegiatanId', (string) $jenis->id)
            ->set('tanggalMulai', '2026-11-01')
            ->set('tanggalTargetSelesai', '2026-12-01')
            ->set('tim', [(string) $this->rina->id])
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertRedirect('/kegiatan/PHP-010-2026');

        $baru = Kegiatan::where('kode', 'PHP-010-2026')->sole();
        $this->assertSame(StatusKegiatan::BelumMulai, $baru->status);
        $this->assertSame($this->admin->id, $baru->created_by);
        $this->assertTrue($baru->tim->contains($this->rina));
    }

    public function test_kode_kegiatan_harus_unik_saat_tambah_dan_edit(): void
    {
        Livewire::actingAs($this->admin)->test(Form::class)
            ->set('kode', 'PP-001-2026')
            ->set('judul', 'Duplikat')
            ->set('jenisKegiatanId', (string) JenisKegiatan::first()->id)
            ->call('simpan')
            ->assertHasErrors(['kode' => 'unique']);

        Livewire::actingAs($this->admin)->test(Form::class, ['kegiatan' => $this->milikLain])
            ->set('kode', 'PP-001-2026')
            ->call('simpan')
            ->assertHasErrors(['kode' => 'unique']);

        // Menyimpan tanpa mengganti kode sendiri tetap boleh.
        Livewire::actingAs($this->admin)->test(Form::class, ['kegiatan' => $this->milikLain])
            ->set('judul', 'Judul Diganti')
            ->call('simpan')
            ->assertHasNoErrors();
    }

    public function test_target_tidak_boleh_sebelum_mulai(): void
    {
        Livewire::actingAs($this->admin)->test(Form::class, ['kegiatan' => $this->milikRina])
            ->set('tanggalMulai', '2026-10-10')
            ->set('tanggalTargetSelesai', '2026-10-01')
            ->call('simpan')
            ->assertHasErrors('tanggalTargetSelesai');
    }
}
