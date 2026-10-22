<?php

namespace App\Models;

use App\Enums\Prioritas;
use App\Enums\StatusKegiatan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('kegiatan')]
#[Fillable([
    'kode', 'judul', 'jenis_kegiatan_id', 'deskripsi', 'status', 'detail_status', 'progres',
    'prioritas', 'tanggal_mulai', 'tanggal_target_selesai', 'tanggal_selesai_aktual', 'catatan',
])]
class Kegiatan extends Model
{
    use HasFactory;

    /** Field yang perubahannya dicatat di kegiatan_logs. */
    public const LOGGED_FIELDS = ['status', 'detail_status', 'progres', 'catatan'];

    protected function casts(): array
    {
        return [
            'status' => StatusKegiatan::class,
            'prioritas' => Prioritas::class,
            'progres' => 'integer',
            'tanggal_mulai' => 'date',
            'tanggal_target_selesai' => 'date',
            'tanggal_selesai_aktual' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Kegiatan $kegiatan) {
            $kegiatan->created_by ??= auth()->id();
            $kegiatan->updated_by ??= auth()->id();
        });

        static::updating(function (Kegiatan $kegiatan) {
            $kegiatan->updated_by = auth()->id() ?? $kegiatan->updated_by;
        });

        static::updated(function (Kegiatan $kegiatan) {
            $perubahan = [];
            foreach (self::LOGGED_FIELDS as $field) {
                if ($kegiatan->wasChanged($field)) {
                    $perubahan[$field] = [
                        'dari' => $kegiatan->getRawOriginal($field),
                        'ke' => $kegiatan->getAttributes()[$field],
                    ];
                }
            }

            if ($perubahan) {
                $kegiatan->logs()->create(['user_id' => auth()->id(), 'perubahan' => $perubahan]);
            }
        });
    }

    public function jenisKegiatan(): BelongsTo
    {
        return $this->belongsTo(JenisKegiatan::class);
    }

    public function tim(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(KegiatanLog::class)->latest('created_at');
    }

    public function dinasLuarKota(): HasMany
    {
        return $this->hasMany(DinasLuarKota::class);
    }

    /** Admin melihat semua; staff hanya kegiatan yang timnya memuat dirinya. */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if (! $user->isAdmin()) {
            $query->whereHas('tim', fn (Builder $q) => $q->whereKey($user->id));
        }
    }

    #[Scope]
    protected function terlambat(Builder $query): void
    {
        $query->where('status', '!=', StatusKegiatan::Selesai)
            ->whereDate('tanggal_target_selesai', '<', today());
    }

    public function isTerlambat(): bool
    {
        return $this->status !== StatusKegiatan::Selesai
            && $this->tanggal_target_selesai?->lt(today());
    }

    /** Hari keterlambatan (positif) atau sisa hari menuju target (negatif = belum lewat). */
    public function hariTerlambat(): ?int
    {
        return $this->tanggal_target_selesai
            ? (int) $this->tanggal_target_selesai->diffInDays(today(), false)
            : null;
    }
}
