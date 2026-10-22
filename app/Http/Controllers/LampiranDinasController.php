<?php

namespace App\Http\Controllers;

use App\Models\DinasLuarKota;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LampiranDinasController extends Controller
{
    public function __invoke(DinasLuarKota $dinas): StreamedResponse
    {
        Gate::authorize('view', $dinas);

        $disk = Storage::disk(DinasLuarKota::DISK_LAMPIRAN);
        abort_unless($dinas->lampiran_surat_tugas && $disk->exists($dinas->lampiran_surat_tugas), 404);

        return $disk->download($dinas->lampiran_surat_tugas, $dinas->namaLampiran());
    }
}
