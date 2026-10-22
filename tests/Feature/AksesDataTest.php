<?php

namespace Tests\Feature;

use App\Enums\StatusKegiatan;
use App\Models\DinasLuarKota;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesDataTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_staff_hanya_melihat_kegiatan_timnya(): void
    {
        $rina = User::factory()->staff()->create();
        $milikRina = Kegiatan::factory()->hasAttached($rina, [], 'tim')->create();
        Kegiatan::factory()->hasAttached(User::factory()->staff(), [], 'tim')->create();

        $this->assertEquals([$milikRina->id], Kegiatan::visibleTo($rina)->pluck('id')->all());
    }

    public function test_admin_melihat_semua_kegiatan(): void
    {
        $admin = User::factory()->admin()->create();
        Kegiatan::factory()->count(3)->create();

        $this->assertCount(3, Kegiatan::visibleTo($admin)->get());
    }

    public function test_staff_hanya_melihat_dinas_dari_kegiatan_timnya(): void
    {
        $rina = User::factory()->staff()->create();
        $kegiatanRina = Kegiatan::factory()->hasAttached($rina, [], 'tim')->create();
        // Rina tidak ikut berangkat, tapi dinas ini milik kegiatan timnya.
        $dinasTim = DinasLuarKota::factory()->for($kegiatanRina)->create();
        DinasLuarKota::factory()->create();

        $this->assertEquals([$dinasTim->id], DinasLuarKota::visibleTo($rina)->pluck('id')->all());
    }

    public function test_perubahan_status_dan_progres_tercatat_di_riwayat(): void
    {
        $rina = User::factory()->staff()->create();
        $kegiatan = Kegiatan::factory()->create(['status' => StatusKegiatan::BelumMulai, 'progres' => 0]);

        $this->actingAs($rina);
        $kegiatan->update(['status' => StatusKegiatan::Berjalan, 'progres' => 30, 'judul' => 'Judul baru']);

        $log = $kegiatan->logs()->sole();
        $this->assertEquals($rina->id, $log->user_id);
        $this->assertEquals([
            'status' => ['dari' => 'belum_mulai', 'ke' => 'berjalan'],
            'progres' => ['dari' => 0, 'ke' => 30],
        ], $log->perubahan);
        $this->assertEquals($rina->id, $kegiatan->fresh()->updated_by);
    }

    public function test_terlambat_dihitung_dari_tanggal_target(): void
    {
        $lewat = Kegiatan::factory()->create(['tanggal_target_selesai' => today()->subDay()]);
        $selesai = Kegiatan::factory()->create(['tanggal_target_selesai' => today()->subDay(), 'status' => StatusKegiatan::Selesai]);
        $belum = Kegiatan::factory()->create(['tanggal_target_selesai' => today()->addDay()]);

        $this->assertTrue($lewat->isTerlambat());
        $this->assertFalse($selesai->isTerlambat());
        $this->assertFalse($belum->isTerlambat());
        $this->assertEquals([$lewat->id], Kegiatan::terlambat()->pluck('id')->all());
    }
}
