<?php

namespace Database\Factories;

use App\Models\JenisKegiatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JenisKegiatan>
 */
class JenisKegiatanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => strtoupper(fake()->unique()->lexify('???')),
            'nama' => fake()->words(3, true),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
