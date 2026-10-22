{{-- Dompdf tidak memuat Tailwind; warna diambil dari tokens.md. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $rekap['judul'] }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1a2138; }
        .kop { background: #1e2a4a; color: #ffffff; padding: 12px 16px; border-bottom: 3px solid #c3b091; }
        .kop .unit { color: #c3b091; font-size: 8px; letter-spacing: 1px; text-transform: uppercase; }
        .kop h1 { margin: 4px 0 2px; font-size: 16px; }
        .kop .meta { color: #c9cfdf; }
        h2 { font-size: 11px; margin: 16px 0 6px; color: #1e2a4a; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #efede6; text-align: left; font-weight: bold; }
        th, td { border: 1px solid #e3e0d6; padding: 4px 5px; vertical-align: top; }
        tr:nth-child(even) td { background: #f7f6f2; }
        .kosong { color: #5b6275; font-style: italic; }
    </style>
</head>
<body>
    <div class="kop">
        <div class="unit">{{ config('app.name') }}</div>
        <h1>{{ $rekap['judul'] }}</h1>
        <div class="meta">
            Periode {{ $dari->translatedFormat('j M Y') }} – {{ $sampai->translatedFormat('j M Y') }}
            · Dicetak oleh {{ $dicetakOleh }}, {{ now()->translatedFormat('j M Y, H.i') }}
        </div>
    </div>

    @foreach ($rekap['tabel'] as $tabel)
        <h2>{{ $tabel['judul'] }} ({{ count($tabel['baris']) }})</h2>
        @if (empty($tabel['baris']))
            <p class="kosong">Tidak ada data pada periode ini.</p>
        @else
            <table>
                <thead><tr>@foreach ($tabel['kolom'] as $kolom)<th>{{ $kolom }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($tabel['baris'] as $baris)
                        <tr>@foreach ($baris as $sel)<td>{!! nl2br(e($sel ?? '–')) !!}</td>@endforeach</tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</body>
</html>
