<?php

namespace Tests\Feature;

use App\Livewire\Dinas\Form;
use App\Livewire\Dinas\Index;
use App\Livewire\Dinas\Show;
use App\Models\DinasLuarKota;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DinasLuarKotaTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $admin;

    private User $rina;

    private DinasLuarKota $dinasTimRina;

    private DinasLuarKota $dinasTimLain;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(DinasLuarKota::DISK_LAMPIRAN);

        $this->admin = User::factory()->admin()->create();
        $this->rina = User::factory()->staff()->create();

        $kegiatanRina = Kegiatan::factory()->hasAttached($this->rina, [], 'tim')->create();
        $this->dinasTimRina = DinasLuarKota::factory()->for($kegiatanRina)->create(['kota_tujuan' => 'Surabaya']);
        $this->dinasTimLain = DinasLuarKota::factory()->create(['kota_tujuan' => 'Makassar']);
    }

    public function test_staff_hanya_melihat_dinas_dari_kegiatan_timnya(): void
    {
        Livewire::actingAs($this->rina)->test(Index::class)
            ->assertSee('Surabaya')
            ->assertDontSee('Makassar')
            ->assertDontSee('Catat Dinas');

        $this->actingAs($this->rina)->get(route('dinas.show', $this->dinasTimRina))->assertOk()->assertDontSee('Hapus');
        $this->actingAs($this->rina)->get(route('dinas.show', $this->dinasTimLain))->assertForbidden();
    }

    public function test_staff_yang_berangkat_di_luar_tim_bisa_melihat_dinasnya_tapi_tidak_kegiatannya(): void
    {
        Storage::disk(DinasLuarKota::DISK_LAMPIRAN)->put('surat-tugas/y/st.pdf', '%PDF-1.4');
        $this->dinasTimLain->update(['lampiran_surat_tugas' => 'surat-tugas/y/st.pdf']);
        $this->dinasTimLain->staff()->attach($this->rina);

        Livewire::actingAs($this->rina)->test(Index::class)->assertSee('Makassar');
        $this->actingAs($this->rina)->get(route('dinas.show', $this->dinasTimLain))->assertOk()
            ->assertDontSee(route('kegiatan.show', $this->dinasTimLain->kegiatan));
        $this->actingAs($this->rina)->get(route('dinas.lampiran', $this->dinasTimLain))->assertOk();
        $this->actingAs($this->rina)->get(route('kegiatan.show', $this->dinasTimLain->kegiatan))->assertForbidden();
    }

    public function test_admin_melihat_semua_dinas(): void
    {
        Livewire::actingAs($this->admin)->test(Index::class)
            ->assertSee('Surabaya')
            ->assertSee('Makassar')
            ->assertSee('Catat Dinas');
    }

    public function test_filter_status_dari_tanggal(): void
    {
        $this->dinasTimRina->update(['tanggal_berangkat' => today()->addDays(5), 'tanggal_kembali' => today()->addDays(7)]);
        $this->dinasTimLain->update(['tanggal_berangkat' => today()->subDays(7), 'tanggal_kembali' => today()->subDays(5)]);

        Livewire::actingAs($this->admin)->test(Index::class)
            ->set('status', 'terjadwal')->assertSee('Surabaya')->assertDontSee('Makassar')
            ->set('status', 'selesai')->assertSee('Makassar')->assertDontSee('Surabaya');
    }

    public function test_lampiran_hanya_bisa_diunduh_admin_dan_tim_terkait(): void
    {
        Storage::disk(DinasLuarKota::DISK_LAMPIRAN)->put('surat-tugas/x/st.pdf', '%PDF-1.4');
        $this->dinasTimRina->update(['lampiran_surat_tugas' => 'surat-tugas/x/st.pdf']);
        $budi = User::factory()->staff()->create();

        $this->actingAs($this->rina)->get(route('dinas.lampiran', $this->dinasTimRina))->assertOk()->assertDownload('st.pdf');
        $this->actingAs($this->admin)->get(route('dinas.lampiran', $this->dinasTimRina))->assertOk();
        $this->actingAs($budi)->get(route('dinas.lampiran', $this->dinasTimRina))->assertForbidden();
        $this->post('/logout');
        $this->get(route('dinas.lampiran', $this->dinasTimRina))->assertRedirect('/login');
    }

    public function test_staff_tidak_bisa_membuka_form_maupun_menghapus(): void
    {
        $this->actingAs($this->rina)->get(route('dinas.create'))->assertForbidden();
        $this->actingAs($this->rina)->get(route('dinas.edit', $this->dinasTimRina))->assertForbidden();

        Livewire::actingAs($this->rina)->test(Show::class, ['dinas' => $this->dinasTimRina])->call('hapus')->assertForbidden();
        Livewire::actingAs($this->rina)->test(Index::class)->call('hapus', $this->dinasTimRina->id)->assertForbidden();
        $this->assertModelExists($this->dinasTimRina);
    }

    public function test_admin_mencatat_dinas_dengan_lampiran(): void
    {
        Livewire::actingAs($this->admin)->test(Form::class)
            ->set('kegiatanId', (string) $this->dinasTimRina->kegiatan_id)
            ->set('kotaTujuan', 'Denpasar')
            ->set('tanggalBerangkat', '2026-11-02')
            ->set('tanggalKembali', '2026-11-04')
            ->set('keperluan', 'Pemantauan satker Bali')
            ->set('staff', [(string) $this->rina->id])
            ->set('lampiran', UploadedFile::fake()->create('Surat Tugas 01.pdf', 100, 'application/pdf'))
            ->call('simpan')
            ->assertHasNoErrors();

        $dinas = DinasLuarKota::where('kota_tujuan', 'Denpasar')->sole();
        $this->assertSame($this->admin->id, $dinas->created_by);
        $this->assertTrue($dinas->staff->contains($this->rina));
        $this->assertSame('surat-tugas-01.pdf', $dinas->namaLampiran());
        Storage::disk(DinasLuarKota::DISK_LAMPIRAN)->assertExists($dinas->lampiran_surat_tugas);
    }

    public function test_validasi_form_dinas(): void
    {
        Livewire::actingAs($this->admin)->test(Form::class)
            ->set('kotaTujuan', 'Medan')
            ->set('tanggalBerangkat', '2026-11-10')
            ->set('tanggalKembali', '2026-11-08')
            ->set('keperluan', 'x')
            ->set('lampiran', UploadedFile::fake()->create('virus.exe', 10))
            ->call('simpan')
            ->assertHasErrors(['kegiatanId', 'tanggalKembali', 'staff', 'lampiran']);
    }

    public function test_ganti_dan_hapus_lampiran_membersihkan_file_lama(): void
    {
        $disk = Storage::disk(DinasLuarKota::DISK_LAMPIRAN);
        $disk->put('surat-tugas/lama/st.pdf', '%PDF-1.4');
        $this->dinasTimRina->update(['lampiran_surat_tugas' => 'surat-tugas/lama/st.pdf']);
        $this->dinasTimRina->staff()->attach($this->rina);

        Livewire::actingAs($this->admin)->test(Form::class, ['dinas' => $this->dinasTimRina])
            ->set('lampiran', UploadedFile::fake()->create('baru.pdf', 50, 'application/pdf'))
            ->call('simpan')
            ->assertHasNoErrors();

        $disk->assertMissing('surat-tugas/lama/st.pdf');
        $baru = $this->dinasTimRina->fresh()->lampiran_surat_tugas;
        $disk->assertExists($baru);

        Livewire::actingAs($this->admin)->test(Show::class, ['dinas' => $this->dinasTimRina->fresh()])
            ->call('hapus')
            ->assertRedirect(route('dinas.index'));
        $disk->assertMissing($baru);
    }
}
