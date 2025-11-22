<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Export SOP</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
    h1 { font-size:16px; margin-bottom:8px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ddd; padding:6px 8px; vertical-align: top; }
    th { background:#f3f6ff; text-align:left; }
    .muted { color:#666; font-size:11px; }
  </style>
</head>
<body>

  <h1>Laporan SOP</h1>
  <div class="muted">
    Periode: {{ $from ?? '-' }} s/d {{ $to ?? '-' }}
    @if($department) • Dept: {{ $department }} @endif
  </div>

  <br>

  <table>
    <thead>
      <tr>
        <th style="width:90px">Kode</th>
        <th>Judul</th>
        <th style="width:120px">Dept</th>
        <th style="width:110px">Status</th>
        <th style="width:120px">Tanggal</th>
      </tr>
    </thead>
    <tbody>
      @forelse($sops as $sop)
        <tr>
          <td><b>{{ $sop->code }}</b></td>
          <td>{{ $sop->title }}</td>
          <td>{{ $sop->department }}</td>
          <td>{{ strtoupper($sop->status) }}</td>
          <td>{{ optional($sop->created_at)->format('d M Y') }}</td>
        </tr>
      @empty
        <tr><td colspan="5" style="text-align:center">Tidak ada data</td></tr>
      @endforelse
    </tbody>
  </table>

</body>
</html>
