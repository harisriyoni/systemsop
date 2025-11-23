<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Template SOP - {{ $template->code ?? $template->name }}</title>
  <style>
    @page { margin: 90px 36px 70px 36px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11.5px; color:#0f172a; }
    header { position: fixed; top: -70px; left: 0; right: 0; height: 60px; }
    .kop { border-bottom: 2px solid #05727d; padding-bottom: 6px; }
    .title { font-size: 16px; font-weight: 700; color:#05727d; }
    .sub { font-size: 11px; color:#334155; margin-top:2px; }
    .badge { display:inline-block; padding:3px 8px; border-radius:999px; font-size:10px; background:#e6f2f3; color:#05727d; }
    .block { border:1px solid #e2e8f0; border-radius:10px; padding:10px 12px; margin-top:10px; }
    table { width:100%; border-collapse: collapse; }
    td { padding:4px 0; vertical-align:top; }
    .key { width:120px; color:#64748b; }
    pre {
      background:#0b1220;
      color:#e2e8f0;
      padding:10px;
      border-radius:8px;
      font-size:10px;
      white-space: pre-wrap;
      word-break: break-word;
      line-height:1.35;
      margin:0;
    }
    h3 { margin:0 0 6px 0; font-size:12px; color:#05727d; }
  </style>
</head>
<body>

<header>
  <div class="kop">
    <div class="title">TEMPLATE SOP</div>
    <div class="sub">
      {{ $template->name }} ({{ $template->code ?? '-' }})
      • Dept: {{ $template->department ?? '-' }}
      • Status: {{ $template->is_active ? 'Aktif' : 'Nonaktif' }}
    </div>
  </div>
</header>

<main>
  <div class="block">
    <table>
      <tr>
        <td class="key">Nama Template</td>
        <td>: <b>{{ $template->name }}</b></td>
      </tr>
      <tr>
        <td class="key">Kode</td>
        <td>: {{ $template->code ?? '-' }}</td>
      </tr>
      <tr>
        <td class="key">Departemen</td>
        <td>: {{ $template->department ?? '-' }}</td>
      </tr>
      <tr>
        <td class="key">Product</td>
        <td>: {{ $template->product ?? '-' }}</td>
      </tr>
      <tr>
        <td class="key">Line/Area</td>
        <td>: {{ $template->line ?? '-' }}</td>
      </tr>
      <tr>
        <td class="key">Dibuat / Update</td>
        <td>:
          {{ $template->created_at?->format('d M Y H:i') ?? '-' }}
          •
          {{ $template->updated_at?->format('d M Y H:i') ?? '-' }}
        </td>
      </tr>
    </table>
  </div>

  <div class="block">
    <h3>form_schema</h3>
    <pre>{{ json_encode($template->form_schema ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
  </div>

  <div class="block">
    <h3>builder_schema</h3>
    <pre>{{ json_encode($template->builder_schema ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
  </div>

  <div class="block">
    <h3>meta</h3>
    <pre>{{ json_encode($template->meta ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
  </div>
</main>

</body>
</html>
