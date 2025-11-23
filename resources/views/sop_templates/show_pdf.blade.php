<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Template SOP - {{ $template->code ?? $template->name }}</title>
  <style>
    @page {
      margin: 90px 36px 70px 36px;
    }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 11.5px;
      color: #0f172a;
    }

    header {
      position: fixed;
      top: -70px;
      left: 0;
      right: 0;
      height: 60px;
    }

    .kop {
      border-bottom: 2px solid #05727d;
      padding-bottom: 6px;
    }

    .title {
      font-size: 16px;
      font-weight: 700;
      color: #05727d;
    }

    .sub {
      font-size: 11px;
      color: #334155;
      margin-top: 2px;
    }

    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 999px;
      font-size: 10px;
      background: #e6f2f3;
      color: #05727d;
      margin-left: 6px;
    }

    main {
      margin-top: 4px;
    }

    .block {
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 10px 12px;
      margin-top: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    td {
      padding: 4px 0;
      vertical-align: top;
    }

    .key {
      width: 120px;
      color: #64748b;
      font-size: 10.5px;
    }

    .val {
      font-size: 11.5px;
    }

    .section-title {
      margin-top: 14px;
      font-size: 12px;
      font-weight: 600;
      color: #0f172a;
    }

    .doc-card {
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 12px 14px;
      margin-top: 6px;
    }

    .doc-header {
      margin-bottom: 8px;
    }

    .doc-header-title {
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      color: #0f172a;
    }

    .doc-header-sub {
      font-size: 11px;
      color: #64748b;
      margin-top: 2px;
    }

    .block-heading {
      margin: 10px 0 4px 0;
    }

    .block-heading.h1 {
      font-size: 13px;
      font-weight: 700;
      color: #0f172a;
    }

    .block-heading.h2 {
      font-size: 12px;
      font-weight: 600;
      color: #0f172a;
    }

    .block-heading.h3 {
      font-size: 11px;
      font-weight: 600;
      color: #1e293b;
    }

    .align-left {
      text-align: left;
    }

    .align-center {
      text-align: center;
    }

    .block-paragraph {
      font-size: 11px;
      line-height: 1.5;
      margin: 4px 0 8px;
      text-align: justify;
    }

    .info-table-block {
      margin: 8px 0 10px;
    }

    .info-table-title {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #64748b;
      margin-bottom: 4px;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
    }

    .info-table td.label {
      width: 120px;
      font-size: 10px;
      color: #64748b;
      padding: 2px 0;
    }

    .info-table td.value {
      font-size: 11px;
      color: #0f172a;
      padding: 2px 0;
    }

    .checklist-block {
      margin: 8px 0 10px;
    }

    .checklist-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #64748b;
      margin-bottom: 4px;
    }

    .checklist-items {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .checklist-items li {
      display: flex;
      align-items: flex-start;
      margin-bottom: 3px;
    }

    .checklist-box {
      width: 10px;
      height: 10px;
      border: 1px solid #94a3b8;
      margin-right: 6px;
      margin-top: 2px;
    }

    .checklist-text-main {
      font-size: 11px;
      color: #0f172a;
    }

    .checklist-text-note {
      font-size: 10px;
      color: #94a3b8;
    }

    /* ==== PHOTO GALLERY (harus mirip canvas) ==== */
    .photo-block {
      margin: 8px 0 10px;
    }

    .photo-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #64748b;
      margin-bottom: 4px;
    }

    .photo-table {
      width: 100%;
      border-collapse: collapse;
    }

    .photo-cell {
      width: 50%;
      padding: 4px;
    }

    .photo-frame {
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      height: 120px;
      text-align: center;
      vertical-align: middle;
      background: #f8fafc;
      overflow: hidden;
    }

    .photo-frame img {
      max-width: 100%;
      max-height: 120px;
    }

    .photo-caption {
      font-size: 10px;
      color: #64748b;
      margin-top: 2px;
      text-align: left;
    }

    pre {
      background: #0b1220;
      color: #e2e8f0;
      padding: 10px;
      border-radius: 8px;
      font-size: 10px;
      white-space: pre-wrap;
      word-break: break-word;
      line-height: 1.35;
      margin: 0;
    }

    h3 {
      margin: 0 0 6px 0;
      font-size: 12px;
      color: #05727d;
    }
  </style>
</head>
<body>

@php
  // =========================================
  // Normalisasi form_schema, builder, dan meta
  // =========================================
  $formSchemaRaw    = $template->form_schema;
  $builderRaw       = $template->builder_schema;
  $metaRaw          = $template->meta;

  if (is_string($formSchemaRaw)) {
      $formSchema = json_decode($formSchemaRaw, true) ?: [];
  } else {
      $formSchema = $formSchemaRaw ?? [];
  }

  if (is_string($builderRaw)) {
      $builder = json_decode($builderRaw, true) ?: [];
  } else {
      $builder = $builderRaw ?? [];
  }

  if (is_string($metaRaw)) {
      $metaSchema = json_decode($metaRaw, true) ?: [];
  } else {
      $metaSchema = $metaRaw ?? [];
  }

  // ===== Konfigurasi page dari builder =====
  $pageTheme          = $builder['page']['theme']            ?? '#05727d';
  $pageTitle          = $builder['page']['title']            ?? 'STANDARD OPERATING PROCEDURE';
  $pageSubtitle       = $builder['page']['subtitle']         ?? 'Template SOP';
  $pageShowLogo       = $builder['page']['show_logo']        ?? true;
  $pageShowPageNumber = $builder['page']['show_page_number'] ?? true;

  // ===== Blok dari builder (format baru: page+blocks / lama: sections) =====
  $blocks = [];

  if (is_array($builder)) {
      if (!empty($builder['blocks']) && is_array($builder['blocks'])) {
          $blocks = $builder['blocks'];
      } elseif (!empty($builder['sections']) && is_array($builder['sections'])) {
          // Konversi sections lama ke blocks minimal
          foreach ($builder['sections'] as $idx => $secRaw) {
              $sec = (array) $secRaw;
              $secName = $sec['name'] ?? ('Section ' . ($idx + 1));
              $itemsRaw = $sec['items'] ?? [];
              $items = [];
              if (is_array($itemsRaw)) {
                  foreach ($itemsRaw as $itRaw) {
                      $it = (array) $itRaw;
                      $items[] = [
                          'text' => $it['label'] ?? '',
                          'note' => $it['note'] ?? '',
                      ];
                  }
              }

              $blocks[] = [
                  'id'    => 'sec_' . $idx . '_h',
                  'type'  => 'heading',
                  'level' => 2,
                  'align' => 'left',
                  'text'  => $secName,
              ];

              $blocks[] = [
                  'id'    => 'sec_' . $idx . '_c',
                  'type'  => 'checklist',
                  'title' => 'Checklist ' . $secName,
                  'items' => $items,
              ];
          }
      }
  }

  // Default blocks kalau tetap kosong
  if (!count($blocks)) {
      $blocks = [
          [
              'id'    => 'blk_title',
              'type'  => 'heading',
              'level' => 1,
              'align' => 'center',
              'text'  => 'JUDUL SOP',
          ],
          [
              'id'    => 'blk_info',
              'type'  => 'info_table',
              'title' => 'INFORMASI DOKUMEN',
              'rows'  => [
                  ['label' => 'Kode Dokumen',  'value' => '@{{ sop.code }}'],
                  ['label' => 'Departemen',    'value' => '@{{ sop.department }}'],
                  ['label' => 'Produk / Line', 'value' => '@{{ sop.product }} / @{{ sop.line }}'],
                  ['label' => 'Revisi',        'value' => '1'],
              ],
          ],
          [
              'id'    => 'blk_purpose',
              'type'  => 'heading',
              'level' => 2,
              'align' => 'left',
              'text'  => 'TUJUAN',
          ],
          [
              'id'   => 'blk_purpose_text',
              'type' => 'paragraph',
              'text' => 'Menjelaskan tujuan pelaksanaan prosedur ini...',
          ],
          [
              'id'    => 'blk_procedure',
              'type'  => 'heading',
              'level' => 2,
              'align' => 'left',
              'text'  => 'PROSEDUR',
          ],
          [
              'id'    => 'blk_steps',
              'type'  => 'checklist',
              'title' => 'Langkah Kerja',
              'items' => [
                  ['text' => 'Persiapan area & alat',      'note' => ''],
                  ['text' => 'Pelaksanaan prosedur utama', 'note' => ''],
              ],
          ],
      ];
  }

  // ===== Extra fields dari meta (hasil import SOP) =====
  $extraFields = [];
  if (!empty($metaSchema['extra_fields']) && is_array($metaSchema['extra_fields'])) {
      foreach ($metaSchema['extra_fields'] as $row) {
          if (!is_array($row)) continue;
          $label = trim($row['label'] ?? '');
          $value = trim($row['value'] ?? '');
          if ($label === '' && $value === '') continue;
          $extraFields[] = [
              'label' => $label ?: '-',
              'value' => $value ?: '-',
          ];
      }
  }

  // ===== Normalisasi FOTO dari SOP asal (meta._sop_attributes.photos) =====
  $photos = [];
  $sopAttrs = $metaSchema['_sop_attributes'] ?? [];
  if (is_string($sopAttrs)) {
      $sopAttrs = json_decode($sopAttrs, true) ?: [];
  }
  $rawPhotos = $sopAttrs['photos'] ?? [];

  if (is_string($rawPhotos)) {
      $rawPhotos = json_decode($rawPhotos, true) ?: [];
  }

  foreach ($rawPhotos as $p) {
      if (is_string($p)) {
          $path = $p;
          $desc = null;
      } elseif (is_array($p)) {
          $path = $p['path'] ?? $p['url'] ?? $p['photo'] ?? null;
          $desc = $p['desc'] ?? $p['description'] ?? $p['keterangan'] ?? null;
      } else {
          $path = null;
          $desc = null;
      }

      if (!$path) {
          continue;
      }

      $isHttp = \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//']);
      if ($isHttp) {
          $url = $path;
      } else {
          // bersihkan prefix storage yang mungkin ikut tersimpan
          $cleanPath = preg_replace('#^storage/(app/public/)?#', '', ltrim($path, '/'));

          if (app()->environment('local')) {
              // LOCAL: standar Laravel -> public/storage/...
              $publicPath = 'storage/' . $cleanPath;
          } else {
              // HOSTING: storage/app/public/...
              $publicPath = 'storage/app/public/' . $cleanPath;
          }

          $url = asset($publicPath);
      }

      $photos[] = [
          'path' => $path,
          'url'  => $url,
          'desc' => $desc,
      ];
  }

  // ==== Untuk blok JSON debug di bawah ====
  $builderSchema = $builder;
@endphp

<header>
  <div class="kop">
    <div class="title">
      TEMPLATE SOP
      <span class="badge">
        {{ $template->is_active ? 'AKTIF' : 'NONAKTIF' }}
      </span>
    </div>
    <div class="sub">
      {{ $template->name }}
      @if ($template->code)
        ({{ $template->code }})
      @endif
      • Dept: {{ $template->department ?? '-' }}
      • Product: {{ $template->product ?? '-' }}
      • Line: {{ $template->line ?? '-' }}
    </div>
  </div>
</header>

<main>
  {{-- =======================
       INFO TEMPLATE
  ======================== --}}
  <div class="block">
    <table>
      <tr>
        <td class="key">Nama Template</td>
        <td class="val">: <b>{{ $template->name }}</b></td>
      </tr>
      <tr>
        <td class="key">Kode</td>
        <td class="val">: {{ $template->code ?? '-' }}</td>
      </tr>
      <tr>
        <td class="key">Departemen</td>
        <td class="val">: {{ $template->department ?? '-' }}</td>
      </tr>
      <tr>
        <td class="key">Product</td>
        <td class="val">: {{ $template->product ?? '-' }}</td>
      </tr>
      <tr>
        <td class="key">Line / Area</td>
        <td class="val">: {{ $template->line ?? '-' }}</td>
      </tr>
      <tr>
        <td class="key">Status</td>
        <td class="val">: {{ $template->is_active ? 'Aktif' : 'Nonaktif' }}</td>
      </tr>
      <tr>
        <td class="key">Dibuat / Update</td>
        <td class="val">
          :
          {{ $template->created_at?->format('d M Y H:i') ?? '-' }}
          •
          {{ $template->updated_at?->format('d M Y H:i') ?? '-' }}
        </td>
      </tr>

      @if(count($extraFields))
        @foreach($extraFields as $ef)
          <tr>
            <td class="key">{{ $ef['label'] }}</td>
            <td class="val">: {{ $ef['value'] }}</td>
          </tr>
        @endforeach
      @endif
    </table>
  </div>

  {{-- =======================
       PREVIEW LAYOUT DOKUMEN
  ======================== --}}
  <div class="section-title">
    Preview Layout Dokumen dari Template
  </div>

  <div class="doc-card">
    {{-- Header dokumen (dari builder.page) --}}
    <div class="doc-header">
      <div class="doc-header-title" style="color: {{ $pageTheme }}">
        {{ $pageTitle }}
      </div>
      <div class="doc-header-sub">
        {{ $pageSubtitle }}
      </div>
    </div>

    {{-- Blok-blok dari builder_schema --}}
    @foreach($blocks as $bRaw)
      @php
        $b = (array) $bRaw;
        $type = $b['type'] ?? 'paragraph';
      @endphp

      @if ($type === 'heading')
        @php
          $level = (int)($b['level'] ?? 2);
          $align = $b['align'] ?? 'left';
          $text  = $b['text']  ?? 'Heading';
          $clsLevel = $level === 1 ? 'h1' : ($level === 3 ? 'h3' : 'h2');
        @endphp
        <div class="block-heading {{ $clsLevel }} align-{{ $align === 'center' ? 'center' : 'left' }}">
          {{ $text }}
        </div>

      @elseif ($type === 'paragraph')
        @php
          $text = $b['text'] ?? '';
        @endphp
        <div class="block-paragraph">
          {!! nl2br(e($text)) !!}
        </div>

      @elseif ($type === 'info_table')
        @php
          $title   = $b['title'] ?? 'INFORMASI DOKUMEN';
          $rowsRaw = $b['rows'] ?? [];
          $rows    = [];
          if (is_array($rowsRaw)) {
              foreach ($rowsRaw as $rowRaw) {
                  $rows[] = (array) $rowRaw;
              }
          }
        @endphp
        <div class="info-table-block">
          <div class="info-table-title">
            {{ strtoupper($title) }}
          </div>
          <table class="info-table">
            <tbody>
            @foreach($rows as $row)
              <tr>
                <td class="label">{{ $row['label'] ?? '' }}</td>
                <td class="value">: {{ $row['value'] ?? '' }}</td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>

      @elseif ($type === 'checklist')
        @php
          $title    = $b['title'] ?? 'Checklist';
          $itemsRaw = $b['items'] ?? [];
          $items    = [];
          if (is_array($itemsRaw)) {
              foreach ($itemsRaw as $itRaw) {
                  $items[] = (array) $itRaw;
              }
          }
        @endphp
        <div class="checklist-block">
          <div class="checklist-title">
            {{ strtoupper($title) }}
          </div>
          <ul class="checklist-items">
            @foreach($items as $it)
              <li>
                <div class="checklist-box"></div>
                <div>
                  <div class="checklist-text-main">
                    {{ $it['text'] ?? '' }}
                  </div>
                  @if (!empty($it['note'] ?? ''))
                    <div class="checklist-text-note">
                      {{ $it['note'] }}
                    </div>
                  @endif
                </div>
              </li>
            @endforeach
          </ul>
        </div>

      @elseif ($type === 'photo_gallery')
        @php
          $title    = $b['title'] ?? 'Foto Pendukung';
          $itemsRaw = $b['items'] ?? [];
          $items    = [];
          if (is_array($itemsRaw)) {
              foreach ($itemsRaw as $itRaw) {
                  $items[] = (array) $itRaw;
              }
          }
        @endphp
        <div class="photo-block">
          <div class="photo-title">{{ strtoupper($title) }}</div>

          @if(count($items))
            <table class="photo-table">
              <tbody>
                @for($row = 0; $row < ceil(count($items) / 2); $row++)
                  <tr>
                    @for($col = 0; $col < 2; $col++)
                      @php
                        $idx = $row * 2 + $col;
                      @endphp
                      <td class="photo-cell">
                        @if(isset($items[$idx]))
                          @php
                            $it        = $items[$idx];
                            $label     = $it['label'] ?? ('Foto ' . ($idx + 1));
                            // support 2 kemungkinan key index foto
                            $photoIdx  = $it['sop_photo_index'] ?? ($it['photo_index'] ?? $idx);
                            $photo     = $photos[$photoIdx] ?? null;
                          @endphp
                          <div class="photo-frame">
                            @if($photo)
                              <img src="{{ $photo['url'] }}" alt="{{ $label }}">
                            @endif
                          </div>
                          <div class="photo-caption">{{ $label }}</div>
                        @endif
                      </td>
                    @endfor
                  </tr>
                @endfor
              </tbody>
            </table>
          @else
            {{-- fallback kalau belum ada item di block --}}
            <div class="photo-frame"></div>
          @endif
        </div>
      @endif
    @endforeach
  </div>

  {{-- =======================
       LAMPIRAN: RAW JSON SCHEMA
       (untuk debugging)
  ======================== --}}
  <div style="page-break-before: always;"></div>

  <div class="block">
    <h3>form_schema</h3>
    <pre>{{ json_encode($formSchema, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
  </div>

  <div class="block">
    <h3>builder_schema</h3>
    <pre>{{ json_encode($builderSchema, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
  </div>

  <div class="block">
    <h3>meta</h3>
    <pre>{{ json_encode($metaSchema, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
  </div>
</main>

{{-- =======================
     NOMOR HALAMAN (opsional)
======================== --}}
@if ($pageShowPageNumber ?? true)
  <script type="text/php">
    if (isset($pdf)) {
        $text = "Halaman {PAGE_NUM} / {PAGE_COUNT}";
        $size = 8;
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $pdf->page_text(500, 820, $text, $font, $size, [0.4, 0.4, 0.4]);
    }
  </script>
@endif

</body>
</html>
