<?php

namespace App\Enums;

enum Prioritas: string
{
    case Rendah = 'rendah';
    case Sedang = 'sedang';
    case Tinggi = 'tinggi';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Nada <x-badge>. */
    public function tone(): string
    {
        return match ($this) {
            self::Rendah => 'outline',
            self::Sedang => 'outline-khaki',
            self::Tinggi => 'outline-danger',
        };
    }
}
