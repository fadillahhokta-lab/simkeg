<?php

namespace Database\Factories;

use App\Models\DinasLuarKota;
use App\Models\Kegiatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DinasLuarKota>
 */
class DinasLuarKotaFactory extends Factory
{
    public function definition(): array
    {
        $berangkat = fake()->dateTimeBetween('-2 months', '+1 month');

        return [
            'kegiatan_id' => Kegiatan::factory(),
            'kota_tujuan' => fake()->city(),
            'tanggal_berangkat' => $berangkat,
            'tanggal_kembali' => (clone $berangkat)->modify('+2 days'),
            'keperluan' => fake()->sentence(),
        ];
    }
}
