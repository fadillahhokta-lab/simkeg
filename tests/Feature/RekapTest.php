<?php

namespace Tests\Feature;

use App\Livewire\Rekap\Index;
use App\Models\DinasLuarKota;
use App\Models\Kegiatan;
use App\Models\User;
use App\Rekap\Rekap;
use App\Rekap\RekapExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class RekapTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $admin;

    private User $rina;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->rina = User::factory()->staff()->create(['name' => 'Rina Kartika']);
        $budi = User::factory()->staff()->create(['name' => 'Budi Santoso']);

        $periode = ['tanggal_mulai' => '2026-07-01', 'tanggal_target_selesai' => '2026-08-31'];
        $milikRina = Kegiatan::factory()->hasAttached($this->rina, [], 'tim')->create(['kode' => 'PP-001-2026', 'judul' => 'Kegiatan Rina', ...$periode]);
        Kegiatan::factory()->hasAttached($budi, [], 'tim')->create(['kode' => 'SA-002-2026', 'judul' => 'Kegiatan Budi', ...$periode]);
        Kegiatan::factory()->hasAttached($this->rina, [], 'tim')->create(['kode' => 'PP-009-2025', 'judul' => 'Di luar periode', 'tanggal_mulai' => '2025-01-01', 'tanggal_target_selesai' => '2025-02-01']);

        DinasLuarKota::factory()->for($milikRina)->hasAttached($this->rina, [], 'staff')
            ->create(['kota_tujuan' => 'Surabaya', 'tanggal_berangkat' => '2026-07-10', 'tanggal_kembali' => '2026-07-12']);
    }

    private function rekap(User $user, string $jenis): array
    {
        return (new Rekap($user, Carbon::parse('2026-07-01'), Carbon::parse('2026-09-30')->endOfDay()))->buat($jenis);
    }

    private function semuaSel(array $rekap): string
    {
        return collect($rekap['tabel'])->flatMap(fn ($t) => $t['baris'])->flatten()->implode('|');
    }

    public function test_rekap_kegiatan_admin_semua_staff_hanya_timnya_dan_sesuai_periode(): void
    {
        $admin = $this->semuaSel($this->rekap($this->admin, 'kegiatan'));
        $this->assertStringContainsString('Kegiatan Rina', $admin);
        $this->assertStringContainsString('Kegiatan Budi', $admin);
        $this->assertStringNotContainsString('Di luar periode', $admin);

        $staff = $this->semuaSel($this->rekap($this->rina, 'kegiatan'));
        $this->assertStringContainsString('Kegiatan Rina', $staff);
        $this->assertStringNotContainsString('Kegiatan Budi', $staff);
    }

    public function test_rekap_dinas_dan_beban_kerja(): void
    {
        $dinas = $this->rekap($this->rina, 'dinas')['tabel'][0]['baris'];
        $this->assertCount(1, $dinas);
        $this->assertSame('Surabaya', $dinas[0][3]);
        $this->assertSame(3, $dinas[0][2]);

        $beban = collect($this->rekap($this->admin, 'beban')['tabel'][0]['baris'])->keyBy(0);
        $this->assertSame(1, $beban['Rina Kartika'][2]);   // total kegiatan dalam periode
        $this->assertSame(1, $beban['Rina Kartika'][7]);   // perjalanan dinas
        $this->assertSame(3, $beban['Rina Kartika'][8]);   // hari dinas

        // Staff hanya melihat rekan yang ada di kegiatan timnya.
        $bebanStaff = collect($this->rekap($this->rina, 'beban')['tabel'][0]['baris'])->pluck(0);
        $this->assertContains('Rina Kartika', $bebanStaff);
        $this->assertNotContains('Budi Santoso', $bebanStaff);
    }

    public function test_halaman_rekap_untuk_staff_hanya_berisi_data_timnya(): void
    {
        Livewire::actingAs($this->rina)->withQueryParams(['dari' => '2026-07-01', 'sampai' => '2026-09-30'])->test(Index::class)
            ->assertSee('Kegiatan Rina')
            ->assertDontSee('Kegiatan Budi')
            ->set('jenis', 'tidak-ada')
            ->assertSet('jenis', 'kegiatan');
    }

    public function test_export_excel_dan_pdf(): void
    {
        Excel::fake();
        $query = ['jenis' => 'kegiatan', 'dari' => '2026-07-01', 'sampai' => '2026-09-30'];

        $this->actingAs($this->rina)->get(route('rekap.export', ['format' => 'xlsx', ...$query]))->assertOk();
        Excel::assertDownloaded('rekap-kegiatan_2026-07-01_2026-09-30.xlsx', function (RekapExport $export) {
            $sel = collect($export->sheets())->flatMap(fn ($sheet) => $sheet->array())->flatten()->implode('|');

            return str_contains($sel, 'Kegiatan Rina') && ! str_contains($sel, 'Kegiatan Budi');
        });

        $this->actingAs($this->rina)->get(route('rekap.export', ['format' => 'pdf', ...$query]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_export_menolak_parameter_tidak_valid_dan_tamu(): void
    {
        $this->actingAs($this->rina)->get(route('rekap.export', ['format' => 'xlsx', 'jenis' => 'gaji', 'dari' => '2026-07-01', 'sampai' => '2026-06-01']))
            ->assertSessionHasErrors(['jenis', 'sampai']);
        $this->get('/rekap/export/csv?jenis=kegiatan&dari=2026-07-01&sampai=2026-09-30')->assertNotFound();

        $this->post('/logout');
        $this->get(route('rekap.export', ['format' => 'pdf', 'jenis' => 'kegiatan', 'dari' => '2026-07-01', 'sampai' => '2026-09-30']))->assertRedirect('/login');
    }
}
