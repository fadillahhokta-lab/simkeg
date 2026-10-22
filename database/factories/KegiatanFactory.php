<?php

namespace Database\Factories;

use App\Enums\Prioritas;
use App\Enums\StatusKegiatan;
use App\Models\JenisKegiatan;
use App\Models\Kegiatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kegiatan>
 */
class KegiatanFactory extends Factory
{
    public function definition(): array
    {
        $mulai = fake()->dateTimeBetween('-3 months', '+1 month');

        return [
            'kode' => fake()->unique()->bothify('KG-###-2026'),
            'judul' => fake()->sentence(5),
            'jenis_kegiatan_id' => JenisKegiatan::factory(),
            'status' => StatusKegiatan::Berjalan,
            'progres' => fake()->numberBetween(0, 90),
            'prioritas' => fake()->randomElement(Prioritas::cases()),
            'tanggal_mulai' => $mulai,
            'tanggal_target_selesai' => (clone $mulai)->modify('+2 months'),
        ];
    }
}
