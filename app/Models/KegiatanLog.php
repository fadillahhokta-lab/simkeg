<?php

namespace App\Models;

use App\Enums\StatusKegiatan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'perubahan'])]
class KegiatanLog extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'perubahan' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Perubahan dalam bentuk siap tampil.
     *
     * @return list<array{field: string, label: string, dari: ?string, ke: ?string}>
     */
    public function ringkasan(): array
    {
        $label = [
            'status' => 'Status', 'detail_status' => 'Detail status', 'progres' => 'Progres',
            'catatan' => 'Catatan', 'hasil' => 'Hasil', 'rekomendasi' => 'Rekomendasi',
        ];

        $format = fn (string $field, $nilai) => match (true) {
            $nilai === null || $nilai === '' => null,
            $field === 'status' => StatusKegiatan::tryFrom($nilai)?->label() ?? $nilai,
            $field === 'progres' => $nilai.'%',
            default => (string) $nilai,
        };

        return collect($this->perubahan)->map(fn (array $ubah, string $field) => [
            'field' => $field,
            'label' => $label[$field] ?? $field,
            'dari' => $format($field, $ubah['dari']),
            'ke' => $format($field, $ubah['ke']),
        ])->values()->all();
    }
}
