<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SOP - {{ $sop->code ?? '' }}</title>

    <style>
        :root{
            --primary: #05727d;        /* brand utama */
            --primary-dark:#045058;   /* brand lebih gelap */
            --primary-soft:#e6f1f2;   /* brand sangat muda */
            --border:#cde3e5;         /* border brand muda */
            --muted:#64748b;
            --text:#0f172a;
        }

        /* ✅ FIX UTAMA: semua halaman turun */
        @page { margin: 210px 36px 90px 36px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11.5px;
            color: var(--text);
            line-height: 1.55;
        }

        /* ================= HEADER / KOP ================= */
        header {
            position: fixed;
            /* ✅ geser header lebih ke atas -> aman buat page 2+ */
            top: -165px;
            left: 0;
            right: 0;
            height: 115px;
        }

        .kop {
            width: 100%;
            border-bottom: 2px solid var(--primary-dark);
            padding-bottom: 8px;
        }

        .kop-table{
            width:100%;
            border-collapse:collapse;
        }
        .kop-table td{
            padding:0;
            vertical-align:middle;
        }

        .kop-left{ width:72px; }
        .kop-logo{
            width:64px; height:64px; object-fit:contain;
        }

        .kop-mid{ padding-left:8px; }
        .kop-company{
            font-size:16px;
            font-weight:800;
            letter-spacing:.3px;
            margin:0;
        }
        .kop-sub{
            font-size:10.2px;
            color:#334155;
            margin-top:2px;
        }

        .kop-right{
            width:250px;
            text-align:right;
        }
        .right-box{
            display:inline-block;
            width:100%;
            box-sizing:border-box;
            background: var(--primary-soft);
            border:1.5px solid var(--border);
            border-radius:12px;
            padding:10px 12px;
            text-align:right;
        }
        .doc-title{
            font-size:13px;
            font-weight:800;
            margin:0 0 6px 0;
            letter-spacing:.3px;
        }
        .doc-meta{
            font-size:10.3px;
            line-height:1.6;
        }

        /* ================= FOOTER ================= */
        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 55px;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            font-size: 9.5px;
            color: var(--muted);
        }

        /* ================= CONTENT ================= */
        .content{ margin-top: 6px; }
        .section{ margin-top: 16px; }
        .section-first{ margin-top: 26px; }

        .section h3{
            font-size:12.5px;
            margin:0 0 8px 0;
            padding:7px 9px;
            background: var(--primary-soft);
            border:1px solid var(--border);
            color: var(--primary-dark);
            border-radius:6px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:6px;
        }
        th, td{
            border:1px solid #cbd5e1;
            padding:7px 8px;
            vertical-align:top;
        }
        th{
            background:#f8fafc;
            text-align:left;
            font-weight:700;
        }

        .badge{
            display:inline-block;
            padding:3px 8px;
            border-radius:999px;
            font-size:9.5px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-weight:800;
            border:1px solid var(--border);
        }

        .muted{ color:var(--muted); font-size:10px; }
        ol, ul { margin:0; padding-left:18px; }
        li { margin-bottom:6px; }

        .no-border td, .no-border th { border:none !important; }

        .sign-box{ height:78px; }
        .sign-name{
            margin-top:32px;
            font-weight:700;
            text-decoration: underline;
        }
        .sign-role{ font-size:10px; color:#475569; }
    </style>
</head>

@php
    // ================= SAFE FALLBACKS =================
    $code    = $sop->code ?? '-';
    $title   = $sop->title ?? '-';
    $dept    = $sop->department ?? '-';
    $status  = strtoupper($sop->status ?? '-');

    $version = $sop->version ?? ($sop->rev ?? ($sop->revision ?? '0'));
    $docNo   = $sop->doc_no ?? $code;

    $effectiveDate = $sop->effective_date ?? $sop->tgl_berlaku ?? null;
    $reviewDate    = $sop->review_date ?? $sop->tgl_tinjau ?? null;

    $creatorName   = $sop->creator->name ?? $sop->createdBy->name ?? $sop->created_by_name ?? '-';
    $reviewerName  = $sop->reviewer->name ?? $sop->reviewed_by_name ?? '-';
    $approverName  = $sop->approver->name ?? $sop->approved_by_name ?? '-';

    $createdAt  = $sop->created_at ?? null;
    $approvedAt = $sop->approved_at ?? $sop->reviewed_at ?? null;

    $purpose = $sop->purpose ?? $sop->tujuan ?? null;
    $scope   = $sop->scope ?? $sop->ruang_lingkup ?? null;
    $defs    = $sop->definitions ?? $sop->definisi ?? null;
    $refs    = $sop->references ?? $sop->referensi ?? null;
    $resp    = $sop->responsibilities ?? $sop->tanggung_jawab ?? null;

    $steps = $sop->steps ?? [];
    if (is_string($steps)) $steps = json_decode($steps, true) ?: [];

    $attachments = $sop->attachments ?? [];
    if (is_string($attachments)) $attachments = json_decode($attachments, true) ?: [];

    $revisions = $sop->revision_history ?? $sop->revisions ?? [];
    if (is_string($revisions)) $revisions = json_decode($revisions, true) ?: [];

    // ================= LOGO (DOMPDF SAFE) =================
    $logoUrl = $logoUrl ?? public_path('assets/images/dipsol.png');
    $hasLogo = file_exists($logoUrl);

    $companyName = config('app.company_name', 'PT. DIPSOL INDONESIA');
    $companySub  = config('app.company_subtitle', 'STANDAR OPERASIONAL PROSEDUR');
    $companyAddr = config('app.company_address', 'Alamat perusahaan belum di-set');
    $companyTel  = config('app.company_tel', '-');
    $companyWeb  = config('app.company_web', '-');
@endphp

<body>

    {{-- ================= HEADER / KOP ================= --}}
    <header>
        <div class="kop">
            <table class="kop-table no-border">
                <tr>
                    {{-- LOGO --}}
                    <td class="kop-left">
                        @if($hasLogo)
                            <img class="kop-logo" src="{{ $logoUrl }}" alt="logo">
                        @else
                            <div style="width:64px;height:64px;border:1px solid #cbd5e1;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:9px;color:#94a3b8;">
                                LOGO
                            </div>
                        @endif
                    </td>

                    {{-- COMPANY INFO --}}
                    <td class="kop-mid">
                        <p class="kop-company">{{ $companyName }}</p>
                        <div class="kop-sub">{{ $companySub }}</div>
                        <div class="kop-sub">{{ $companyAddr }}</div>
                        <div class="kop-sub">Telp: {{ $companyTel }} • Web: {{ $companyWeb }}</div>
                    </td>

                    {{-- RIGHT BOX --}}
                    <td class="kop-right">
                        <div class="right-box">
                            <p class="doc-title">STANDARD OPERATING PROCEDURE</p>
                            <div class="doc-meta">
                                Kode SOP : <b>{{ $code }}</b><br>
                                No. Dok : <b>{{ $docNo }}</b><br>
                                Revisi : <b>{{ $version }}</b><br>
                                Dept : <b>{{ $dept }}</b>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </header>

    {{-- ================= FOOTER ================= --}}
    <footer>
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                Dokumen ini digenerate otomatis dari Sistem SOP • {{ now()->format('d M Y H:i') }}
            </div>
            <div>
                Status: <span class="badge">{{ $status }}</span>
            </div>
        </div>

        <script type="text/php">
            if (isset($pdf)) {
                $font = $fontMetrics->get_font("DejaVu Sans", "normal");
                $size = 9;
                $pdf->page_text(520, 810, "Hal {PAGE_NUM} / {PAGE_COUNT}", $font, $size, array(90,90,90));
            }
        </script>
    </footer>


    {{-- ================= CONTENT ================= --}}
    <div class="content">

        {{-- ====== KONTROL DOKUMEN ====== --}}
        <div class="section section-first">
            <h3>Kontrol Dokumen</h3>
            <table>
                <tr>
                    <th style="width:22%">Nomor Dokumen</th>
                    <td style="width:28%">{{ $docNo }}</td>
                    <th style="width:22%">Judul SOP</th>
                    <td style="width:28%">{{ $title }}</td>
                </tr>
                <tr>
                    <th>Departemen</th>
                    <td>{{ $dept }}</td>
                    <th>Status</th>
                    <td><span class="badge">{{ $status }}</span></td>
                </tr>
                <tr>
                    <th>Tanggal Dibuat</th>
                    <td>{{ optional($createdAt)->format('d M Y') ?? '-' }}</td>
                    <th>Tanggal Berlaku</th>
                    <td>{{ $effectiveDate ? \Carbon\Carbon::parse($effectiveDate)->format('d M Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Tinjau Ulang</th>
                    <td>{{ $reviewDate ? \Carbon\Carbon::parse($reviewDate)->format('d M Y') : '-' }}</td>
                    <th>Disetujui Pada</th>
                    <td>{{ optional($approvedAt)->format('d M Y') ?? '-' }}</td>
                </tr>
            </table>
        </div>

        {{-- ====== TUJUAN ====== --}}
        <div class="section">
            <h3>1. Tujuan</h3>
            <div>{!! nl2br(e($purpose ?: ($sop->description ?? '-'))) !!}</div>
        </div>

        {{-- ====== RUANG LINGKUP ====== --}}
        <div class="section">
            <h3>2. Ruang Lingkup</h3>
            <div>{!! nl2br(e($scope ?? '-')) !!}</div>
        </div>

        {{-- ====== DEFINISI / REFERENSI ====== --}}
        <div class="section">
            <h3>3. Definisi & Referensi</h3>
            <table>
                <tr>
                    <th style="width:50%">Definisi</th>
                    <th style="width:50%">Referensi</th>
                </tr>
                <tr>
                    <td>{!! nl2br(e($defs ?? '-')) !!}</td>
                    <td>{!! nl2br(e($refs ?? '-')) !!}</td>
                </tr>
            </table>
        </div>

        {{-- ====== TANGGUNG JAWAB ====== --}}
        <div class="section">
            <h3>4. Tanggung Jawab</h3>
            <div>{!! nl2br(e($resp ?? '-')) !!}</div>
        </div>

        {{-- ====== PROSEDUR / LANGKAH KERJA ====== --}}
        <div class="section">
            <h3>5. Prosedur / Langkah Kerja</h3>
            @if(!empty($steps))
                <ol>
                    @foreach($steps as $i => $step)
                        @php
                            $stTitle = $step['title'] ?? 'Langkah '.($i+1);
                            $stDesc  = $step['desc']  ?? $step['description'] ?? '';
                            $stSub   = $step['substeps'] ?? $step['items'] ?? [];
                        @endphp
                        <li>
                            <b>{{ $stTitle }}</b><br>
                            {!! nl2br(e($stDesc)) !!}
                            @if(!empty($stSub) && is_array($stSub))
                                <ul>
                                    @foreach($stSub as $sub)
                                        <li>{!! nl2br(e($sub['desc'] ?? $sub)) !!}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @else
                <div class="muted">Belum ada langkah yang diinput.</div>
            @endif
        </div>

        {{-- ====== LAMPIRAN ====== --}}
        <div class="section">
            <h3>6. Formulir Terkait / Lampiran</h3>
            @if(!empty($attachments))
                <ul>
                    @foreach($attachments as $att)
                        @php $attName = is_array($att) ? ($att['name'] ?? '-') : $att; @endphp
                        <li>{{ $attName }}</li>
                    @endforeach
                </ul>
            @else
                <div class="muted">Tidak ada lampiran.</div>
            @endif
        </div>

        {{-- ====== RIWAYAT REVISI ====== --}}
        <div class="section">
            <h3>7. Riwayat Revisi</h3>
            @if(!empty($revisions))
                <table>
                    <thead>
                        <tr>
                            <th style="width:12%">Revisi</th>
                            <th style="width:18%">Tanggal</th>
                            <th style="width:50%">Perubahan</th>
                            <th style="width:20%">Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($revisions as $rev)
                            @php
                                $revNo   = $rev['rev'] ?? $rev['version'] ?? '-';
                                $revDate = $rev['date'] ?? $rev['created_at'] ?? null;
                                $revDesc = $rev['desc'] ?? $rev['change'] ?? '-';
                                $revBy   = $rev['by'] ?? $rev['user'] ?? '-';
                            @endphp
                            <tr>
                                <td>{{ $revNo }}</td>
                                <td>{{ $revDate ? \Carbon\Carbon::parse($revDate)->format('d M Y') : '-' }}</td>
                                <td>{!! nl2br(e($revDesc)) !!}</td>
                                <td>{{ $revBy }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="muted">Belum ada riwayat revisi.</div>
            @endif
        </div>

        {{-- ====== PERSETUJUAN ====== --}}
        <div class="section">
            <h3>8. Persetujuan Dokumen</h3>
            <table>
                <tr>
                    <th style="width:33%">Dibuat oleh</th>
                    <th style="width:33%">Ditinjau oleh</th>
                    <th style="width:34%">Disetujui oleh</th>
                </tr>
                <tr>
                    <td class="sign-box" style="text-align:center;">
                        <div class="sign-name">{{ $creatorName }}</div>
                        <div class="sign-role">Creator</div>
                        <div class="muted">{{ optional($createdAt)->format('d M Y') ?? '-' }}</div>
                    </td>
                    <td class="sign-box" style="text-align:center;">
                        <div class="sign-name">{{ $reviewerName }}</div>
                        <div class="sign-role">Reviewer</div>
                        <div class="muted">{{ optional($sop->reviewed_at)->format('d M Y') ?? '-' }}</div>
                    </td>
                    <td class="sign-box" style="text-align:center;">
                        <div class="sign-name">{{ $approverName }}</div>
                        <div class="sign-role">Approver</div>
                        <div class="muted">{{ optional($approvedAt)->format('d M Y') ?? '-' }}</div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>
</html>
