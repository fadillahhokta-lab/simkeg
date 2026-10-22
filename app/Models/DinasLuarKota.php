<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Table('dinas_luar_kota')]
#[Fillable([
    'kegiatan_id', 'kota_tujuan', 'tanggal_berangkat', 'tanggal_kembali', 'keperluan', 'lampiran_surat_tugas',
])]
class DinasLuarKota extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tanggal_berangkat' => 'date',
            'tanggal_kembali' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DinasLuarKota $dinas) {
            $dinas->created_by ??= auth()->id();
            $dinas->updated_by ??= auth()->id();
        });

        static::updating(function (DinasLuarKota $dinas) {
            $dinas->updated_by = auth()->id() ?? $dinas->updated_by;
        });
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Staff melihat dinas dari kegiatan yang timnya ia ikuti. */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if (! $user->isAdmin()) {
            $query->whereHas('kegiatan', fn (Builder $q) => $q->visibleTo($user));
        }
    }

    /** Status turunan dari tanggal: Terjadwal / Berjalan / Selesai. */
    public function status(): string
    {
        return match (true) {
            today()->lt($this->tanggal_berangkat) => 'Terjadwal',
            today()->gt($this->tanggal_kembali) => 'Selesai',
            default => 'Berjalan',
        };
    }

    public function durasiHari(): int
    {
        return (int) $this->tanggal_berangkat->diffInDays($this->tanggal_kembali) + 1;
    }
}
