<?php

namespace App\Rekap;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private array $tabel) {}

    public function array(): array
    {
        return $this->tabel['baris'];
    }

    public function headings(): array
    {
        return $this->tabel['kolom'];
    }

    public function title(): string
    {
        // Batas nama sheet Excel 31 karakter.
        return mb_substr($this->tabel['judul'], 0, 31);
    }

    public function styles(Worksheet $sheet): ?array
    {
        $sheet->freezePane('A2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
