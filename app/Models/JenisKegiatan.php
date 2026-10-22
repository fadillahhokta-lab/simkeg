<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('jenis_kegiatan')]
#[Fillable(['kode', 'nama', 'deskripsi'])]
class JenisKegiatan extends Model
{
    use HasFactory;

    public function kegiatan(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }
}
