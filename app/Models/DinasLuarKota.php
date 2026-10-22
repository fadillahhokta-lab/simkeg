<?php

namespace App\Models;

use App\Policies\DinasLuarKotaPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Table('dinas_luar_kota')]
#[UsePolicy(DinasLuarKotaPolicy::class)]
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

    /** Staff melihat dinas dari kegiatan yang timnya ia ikuti, atau dinas yang ia ikuti sendiri. */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if (! $user->isAdmin()) {
            $query->where(fn (Builder $q) => $q
                ->whereHas('kegiatan', fn (Builder $k) => $k->visibleTo($user))
                ->orWhereHas('staff', fn (Builder $s) => $s->whereKey($user->id)));
        }
    }

    /** Disk privat: lampiran hanya bisa diunduh lewat route yang dicek policy. */
    public const DISK_LAMPIRAN = 'local';

    public const STATUS = ['terjadwal' => 'Terjadwal', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'];

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Status turunan dari tanggal: terjadwal / berjalan / selesai. */
    public function status(): string
    {
        return match (true) {
            today()->lt($this->tanggal_berangkat) => 'terjadwal',
            today()->gt($this->tanggal_kembali) => 'selesai',
            default => 'berjalan',
        };
    }

    public function statusLabel(): string
    {
        return self::STATUS[$this->status()];
    }

    /** Nada <x-badge>. */
    public function statusTone(): string
    {
        return ['terjadwal' => 'khaki', 'berjalan' => 'primary', 'selesai' => 'success'][$this->status()];
    }

    #[Scope]
    protected function denganStatus(Builder $query, string $status): void
    {
        match ($status) {
            'terjadwal' => $query->whereDate('tanggal_berangkat', '>', today()),
            'berjalan' => $query->whereDate('tanggal_berangkat', '<=', today())->whereDate('tanggal_kembali', '>=', today()),
            'selesai' => $query->whereDate('tanggal_kembali', '<', today()),
            default => null,
        };
    }

    public function namaLampiran(): ?string
    {
        return $this->lampiran_surat_tugas ? basename($this->lampiran_surat_tugas) : null;
    }

    public function durasiHari(): int
    {
        return (int) $this->tanggal_berangkat->diffInDays($this->tanggal_kembali) + 1;
    }
}
