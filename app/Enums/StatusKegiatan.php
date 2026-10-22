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
}
