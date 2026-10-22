<?php

namespace App\Rekap;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Satu sheet per tabel rekap. */
class RekapExport implements Export, WithMultipleSheets
{
    public function __construct(private array $rekap) {}

    public function sheets(): array
    {
        return array_map(fn (array $tabel) => new RekapSheet($tabel), $this->rekap['tabel']);
    }
}
