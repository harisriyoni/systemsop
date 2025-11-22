<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SOP - {{ $sop->code ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        .header { border-bottom:1px solid #ddd; margin-bottom:12px; padding-bottom:8px; }
        .title { font-size:18px; font-weight:bold; margin:0; }
        .meta { font-size:11px; color:#555; margin-top:4px; }
        .section { margin-top:12px; }
        .section h3 { font-size:13px; margin:0 0 6px 0; }
        table { width:100%; border-collapse:collapse; margin-top:6px; }
        th, td { border:1px solid #ddd; padding:6px; vertical-align:top; }
        th { background:#f5f7fb; text-align:left; }
        .badge { display:inline-block; padding:2px 6px; border-radius:999px; font-size:10px; background:#eef2ff; color:#1d4ed8; }
        .footer { position: fixed; bottom: 0; left:0; right:0; font-size:10px; color:#666; border-top:1px solid #eee; padding-top:6px; }
        .page-break { page-break-after: always; }
        ol, ul { margin: 0; padding-left: 18px; }
    </style>
</head>
<body>

    <div class="header">
        <p class="title">SOP {{ $sop->code ?? '-' }}</p>
        <div class="meta">
            <div><b>Judul:</b> {{ $sop->title ?? '-' }}</div>
            <div><b>Departemen:</b> {{ $sop->department ?? '-' }}</div>
            <div><b>Status:</b> <span class="badge">{{ strtoupper($sop->status ?? '-') }}</span></div>
            <div><b>Dibuat:</b> {{ optional($sop->created_at)->format('d M Y') }}</div>
        </div>
    </div>

    <div class="section">
        <h3>Deskripsi</h3>
        <div>
            {!! nl2br(e($sop->description ?? '-')) !!}
        </div>
    </div>

    @if(!empty($sop->steps))
    <div class="section">
        <h3>Langkah Kerja</h3>
        <ol>
            @foreach($sop->steps as $i => $step)
                <li style="margin-bottom:6px;">
                    <b>{{ $step['title'] ?? 'Step '.($i+1) }}</b><br>
                    {!! nl2br(e($step['desc'] ?? '')) !!}
                </li>
            @endforeach
        </ol>
    </div>
    @endif

    @if(!empty($sop->attachments))
    <div class="section">
        <h3>Lampiran</h3>
        <ul>
            @foreach($sop->attachments as $att)
                <li>{{ $att['name'] ?? $att }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="footer">
        SOP ini digenerate otomatis dari sistem SOP + CheckFlow • {{ now()->format('d M Y H:i') }}
    </div>

</body>
</html>
