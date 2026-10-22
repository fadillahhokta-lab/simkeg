<?php

namespace App\Enums;

enum StatusKegiatan: string
{
    case BelumMulai = 'belum_mulai';
    case Berjalan = 'berjalan';
    case Selesai = 'selesai';
    case Ditunda = 'ditunda';

    public function label(): string
    {
        return match ($this) {
            self::BelumMulai => 'Belum Mulai',
            self::Berjalan => 'Berjalan',
            self::Selesai => 'Selesai',
            self::Ditunda => 'Ditunda',
        };
    }

    /** Nada <x-badge>. */
    public function tone(): string
    {
        return match ($this) {
            self::BelumMulai => 'outline',
            self::Berjalan => 'primary',
            self::Selesai => 'success',
            self::Ditunda => 'khaki',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BelumMulai => 'circle-dashed',
            self::Berjalan => 'circle-play',
            self::Selesai => 'circle-check',
            self::Ditunda => 'circle-pause',
        };
    }
}
