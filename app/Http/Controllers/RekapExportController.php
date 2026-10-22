<?php

namespace App\Http\Controllers;

use App\Rekap\Rekap;
use App\Rekap\RekapExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class RekapExportController extends Controller
{
    public function __invoke(Request $request, string $format): Response
    {
        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(Rekap::JENIS))],
            'dari' => ['required', 'date'],
            'sampai' => ['required', 'date', 'after_or_equal:dari'],
        ]);

        $dari = Carbon::parse($data['dari'])->startOfDay();
        $sampai = Carbon::parse($data['sampai'])->endOfDay();
        $rekap = (new Rekap($request->user(), $dari, $sampai))->buat($data['jenis']);
        $namaFile = "rekap-{$data['jenis']}_{$dari->toDateString()}_{$sampai->toDateString()}";

        if ($format === 'xlsx') {
            return Excel::download(new RekapExport($rekap), "{$namaFile}.xlsx");
        }

        return Pdf::loadView('rekap.pdf', [
            'rekap' => $rekap,
            'dari' => $dari,
            'sampai' => $sampai,
            'dicetakOleh' => $request->user()->name,
        ])->setPaper('a4', 'landscape')->setOption('isFontSubsettingEnabled', true)->download("{$namaFile}.pdf");
    }
}
