<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SopController extends Controller
{
    // ==========================
    // LIST SOP
    // ==========================
    public function index(Request $request)
    {
        // default urut terbaru update
        $query = Sop::query()->orderByDesc('updated_at');

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($sub) use ($keyword) {
                $sub->where('code', 'like', "%{$keyword}%")
                    ->orWhere('title', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('department')) {
            $dept = trim($request->department);
            $query->where('department', 'like', "%{$dept}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product')) {
            $query->where('product', 'like', "%".trim($request->product)."%");
        }

        if ($request->filled('line')) {
            $query->where('line', 'like', "%".trim($request->line)."%");
        }

        $sops = $query->paginate(10)->withQueryString();

        return view('sop.index', compact('sops'));
    }

    // ==========================
    // CREATE SOP
    // ==========================
    public function create()
    {
        $this->authorizeManage();
        return view('sop.create');
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $this->validatePayload($request);

        // mode simpan: draft atau langsung submit approval
        $isDraft = $request->boolean('save_draft'); // tombol name="save_draft" value=1 (opsional)

        // ✅ AUTO VERSION PER CODE (selalu ambil max version untuk code tsb)
        $latest = Sop::where('code', $data['code'])
            ->orderByDesc('version')
            ->first();

        $data['version'] = $latest ? ($latest->version + 1) : 1;

        $data['status']     = $isDraft ? 'draft' : 'waiting_approval';
        $data['created_by'] = auth()->id();

        // reset approval flags
        $data['is_approved_produksi'] = false;
        $data['is_approved_qa']       = false;
        $data['is_approved_logistik'] = false;

        $data['is_public'] = $request->boolean('is_public');

        // HANDLE FOTO ARRAY + DESKRIPSI
        $photosPayload = $this->handlePhotosUpload($request);
        $data['photos'] = $photosPayload ?: null;

        $sop = Sop::create($data);

        return redirect()
            ->route('sop.show', $sop)
            ->with('success', $isDraft
                ? 'SOP berhasil disimpan sebagai Draft (v'.$data['version'].').'
                : 'SOP berhasil dibuat dan dikirim untuk approval (v'.$data['version'].').'
            );
    }

    // ==========================
    // EDIT / UPDATE SOP
    // ==========================
    public function edit(Sop $sop)
    {
        $this->authorizeManage();
        return view('sop.edit', compact('sop'));
    }

    /**
     * Update SOP:
     * - Jika SOP masih draft / waiting_approval → update record yang sama.
     * - Jika SOP sudah approved → buat RECORD BARU versi+1 status draft.
     */
    public function update(Request $request, Sop $sop)
    {
        $this->authorizeManage();

        // ✅ kalau SOP sudah approved, jangan overwrite
        if ($sop->status === 'approved') {
            $data = $this->validatePayload($request);

            // ambil versi terakhir dari code ini
            $latest = Sop::where('code', $data['code'])
                ->orderByDesc('version')
                ->first();
            $nextVersion = $latest ? ($latest->version + 1) : (($sop->version ?? 1) + 1);

            // siapkan data new record
            $newData = $data;
            $newData['version'] = $nextVersion;
            $newData['status']  = 'draft';
            $newData['created_by'] = auth()->id();

            $newData['is_approved_produksi'] = false;
            $newData['is_approved_qa']       = false;
            $newData['is_approved_logistik'] = false;

            $newData['is_public'] = $request->boolean('is_public');

            // FOTO: gabung foto lama + foto baru, boleh remove
            $existing = is_array($sop->photos) ? $sop->photos : (json_decode($sop->photos, true) ?: []);
            $removedPaths = $request->input('remove_photos', []);

            if (is_array($removedPaths) && count($removedPaths)) {
                $existing = array_values(array_filter($existing, function($p) use ($removedPaths){
                    return !in_array($p['path'] ?? null, $removedPaths);
                }));
                foreach ($removedPaths as $rp) {
                    if ($rp) Storage::disk('public')->delete($rp);
                }
            }

            $newPhotos = $this->handlePhotosUpload($request);
            $merged = array_merge($existing, $newPhotos);
            $newData['photos'] = count($merged) ? $merged : null;

            $newSop = Sop::create($newData);

            return redirect()
                ->route('sop.edit', $newSop)
                ->with('success', 'Revisi dibuat sebagai SOP versi v'.$nextVersion.'. Silakan submit approval ulang.');
        }

        // ✅ SOP belum approved → update biasa
        $data = $this->validatePayload($request, $sop);

        $data['is_public'] = $request->boolean('is_public');

        // FOTO: merge foto lama + foto baru, boleh remove
        $existing = is_array($sop->photos) ? $sop->photos : (json_decode($sop->photos, true) ?: []);
        $removedPaths = $request->input('remove_photos', []);

        if (is_array($removedPaths) && count($removedPaths)) {
            $existing = array_values(array_filter($existing, function($p) use ($removedPaths){
                return !in_array($p['path'] ?? null, $removedPaths);
            }));
            foreach ($removedPaths as $rp) {
                if ($rp) Storage::disk('public')->delete($rp);
            }
        }

        $newPhotos = $this->handlePhotosUpload($request);
        $mergedPhotos = array_merge($existing, $newPhotos);
        $data['photos'] = count($mergedPhotos) ? $mergedPhotos : null;

        $sop->update($data);

        return redirect()
            ->route('sop.edit', $sop)
            ->with('success', 'SOP berhasil diperbarui.');
    }

    public function destroy(Sop $sop)
    {
        if (!auth()->user()->isRole(['admin'])) {
            return back()->with('error', 'Hanya admin yang boleh menghapus SOP.');
        }

        $photos = is_array($sop->photos) ? $sop->photos : (json_decode($sop->photos, true) ?: []);
        foreach ($photos as $p) {
            if (!empty($p['path'])) Storage::disk('public')->delete($p['path']);
        }

        $sop->delete();

        return redirect()->route('sop.index')->with('success', 'SOP berhasil dihapus.');
    }

    // ==========================
    // SUBMIT APPROVAL (DRAFT -> WAITING)
    // ==========================
    public function submitApproval(Sop $sop)
    {
        $this->authorizeManage();

        if ($sop->status !== 'draft') {
            return back()->with('error', 'Hanya SOP draft yang bisa diajukan approval.');
        }

        $sop->status = 'waiting_approval';
        $sop->is_approved_produksi = false;
        $sop->is_approved_qa       = false;
        $sop->is_approved_logistik = false;

        $sop->save();

        return back()->with('success', 'SOP berhasil diajukan untuk approval.');
    }

    // ==========================
    // APPROVAL LIST
    // ==========================
    public function approvalList(Request $request)
    {
        $userRole = auth()->user()->role;

        $query = Sop::query()
            ->where('status', 'waiting_approval')
            ->orderByDesc('updated_at');

        if ($userRole === 'produksi') {
            $query->where('is_approved_produksi', false);
        } elseif ($userRole === 'qa') {
            $query->where('is_approved_qa', false);
        } elseif ($userRole === 'logistik') {
            $query->where('is_approved_logistik', false);
        }

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($sub) use ($keyword) {
                $sub->where('code', 'like', "%{$keyword}%")
                    ->orWhere('title', 'like', "%{$keyword}%");
            });
        }

        $sops = $query->paginate(10)->withQueryString();

        return view('sop.approval', compact('sops', 'userRole'));
    }

    // ==========================
    // APPROVE / REJECT SOP
    // ==========================
    public function approve(Request $request, Sop $sop)
    {
        $userRole = auth()->user()->role;

        if ($sop->status !== 'waiting_approval') {
            return back()->with('error', 'SOP ini tidak dalam status menunggu persetujuan.');
        }

        if (!in_array($userRole, ['admin', 'produksi', 'qa', 'logistik'])) {
            return back()->with('error', 'Anda tidak punya akses untuk menyetujui SOP ini.');
        }

        if ($userRole === 'admin') {
            $sop->is_approved_produksi = true;
            $sop->is_approved_qa       = true;
            $sop->is_approved_logistik = true;
        } else {
            if ($userRole === 'produksi' && !$sop->is_approved_produksi) {
                $sop->is_approved_produksi = true;
                $this->stampApproval($sop, 'produksi');
            }
            if ($userRole === 'qa' && !$sop->is_approved_qa) {
                $sop->is_approved_qa = true;
                $this->stampApproval($sop, 'qa');
            }
            if ($userRole === 'logistik' && !$sop->is_approved_logistik) {
                $sop->is_approved_logistik = true;
                $this->stampApproval($sop, 'logistik');
            }
        }

        if ($sop->is_approved_produksi && $sop->is_approved_qa && $sop->is_approved_logistik) {
            $sop->status = 'approved';
        }

        $sop->save();

        return back()->with('success', 'Persetujuan berhasil disimpan.');
    }

    /**
     * NOTE:
     * tabel kamu belum punya status "rejected" di enum.
     * Jadi reject kita kembalikan ke DRAFT + reset flag.
     * Kalau kamu mau status rejected beneran, tinggal tambah enum di migration.
     */
    public function reject(Request $request, Sop $sop)
    {
        $userRole = auth()->user()->role;

        if ($sop->status !== 'waiting_approval') {
            return back()->with('error', 'SOP ini tidak dalam status menunggu persetujuan.');
        }

        if (!in_array($userRole, ['admin', 'produksi', 'qa', 'logistik'])) {
            return back()->with('error', 'Anda tidak punya akses untuk menolak SOP ini.');
        }

        $request->validate([
            'reason' => ['nullable','string','max:500'],
        ]);

        // balik draft
        $sop->status = 'draft';
        $sop->is_approved_produksi = false;
        $sop->is_approved_qa       = false;
        $sop->is_approved_logistik = false;

        if (Schema::hasColumn('sops','rejected_reason')) {
            $sop->rejected_reason = $request->reason;
        }
        if (Schema::hasColumn('sops','rejected_by')) {
            $sop->rejected_by = auth()->id();
        }
        if (Schema::hasColumn('sops','rejected_at')) {
            $sop->rejected_at = now();
        }

        $sop->save();

        return back()->with('success', 'SOP ditolak dan dikembalikan ke Draft.');
    }

    // ==========================
    // INTERNAL SHOW (LOGIN)
    // ==========================
    public function show(Sop $sop)
    {
        $qrUrl = $sop->is_public && \Route::has('sop.public.show')
            ? route('sop.public.show', $sop)
            : route('sop.show', $sop);

        return view('sop.show', compact('sop','qrUrl'));
    }

    // ==========================
    // PUBLIC SHOW (QR TANPA LOGIN)
    // ==========================
    public function publicShow(Request $request, Sop $sop)
    {
        if (!$sop->is_public || $sop->status !== 'approved') {
            abort(404);
        }

        $qrUrl = route('sop.public.show', $sop);

        $locked = false;
        if ($sop->pin) {
            $sessionKey = "sop_unlocked_{$sop->id}";
            $locked = !$request->session()->get($sessionKey, false);
        }

        return view('sop.show', compact('sop','qrUrl','locked'));
    }

    public function publicUnlock(Request $request, Sop $sop)
    {
        if (!$sop->is_public || $sop->status !== 'approved') {
            abort(404);
        }

        if (!$sop->pin) {
            return redirect()->route('sop.public.show', $sop);
        }

        $request->validate([
            'pin' => ['required','string','max:20'],
        ]);

        if ($request->pin !== $sop->pin) {
            return back()->with('error', 'PIN salah.');
        }

        $sessionKey = "sop_unlocked_{$sop->id}";
        $request->session()->put($sessionKey, true);

        return redirect()->route('sop.public.show', $sop);
    }

    public function publicAck(Request $request, Sop $sop)
    {
        if (!$sop->is_public || $sop->status !== 'approved') {
            abort(404);
        }

        // optional: simpan log ack kalau ada tabelnya

        return back()->with('success', 'Terima kasih, sudah mengkonfirmasi SOP.');
    }

    // ==========================
    // GENERATE QR SOP
    // ==========================
    public function generateQr(Sop $sop)
    {
        $this->authorizeApprover();

        $url = $sop->is_public
            ? route('sop.public.show', $sop)
            : route('sop.show', $sop);

        $qrPath = null;
        $qrUrl  = $url;

        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $fileName = 'qr-sop-'.$sop->id.'-'.Str::random(6).'.png';
            $qrPath   = 'qr/'.$fileName;

            $png = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(600)
                ->margin(2)
                ->generate($url);

            Storage::disk('public')->put($qrPath, $png);
            $qrUrl = Storage::disk('public')->url($qrPath);
        }

        if (Schema::hasColumn('sops','qr_path')) $sop->qr_path = $qrPath;
        if (Schema::hasColumn('sops','qr_url'))  $sop->qr_url  = $qrUrl;

        $sop->save();

        return back()->with('success', 'QR SOP berhasil dibuat.');
    }

    // ==========================
    // DOWNLOAD PDF SOP
    // ==========================
    public function downloadPdf(Sop $sop)
    {
        $this->authorizeApprover();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sop.pdf', [
                'sop' => $sop,
                'generatedAt' => now(),
            ])->setPaper('a4', 'portrait');

            $filename = ($sop->code ?? 'sop')
                . '_v' . ($sop->version ?? 1)
                . '.pdf';

            return $pdf->download($filename);
        }

        return redirect()->route('sop.show', $sop)
            ->with('error', 'Export PDF belum aktif (Dompdf belum terpasang).');
    }

    // ==========================
    // VERSIONS / HISTORY
    // ==========================
    public function versions(Sop $sop)
    {
        $this->authorizeApprover();

        $versions = Sop::where('code', $sop->code)
            ->orderByDesc('version')
            ->get();

        return view('sop.versions', compact('sop','versions'));
    }

    public function history(Sop $sop)
    {
        $this->authorizeApprover();

        $logs = []; // nanti kalau ada sop_logs tinggal fetch
        return view('sop.history', compact('sop','logs'));
    }

    // ==========================
    // HELPER
    // ==========================
    private function validatePayload(Request $request, ?Sop $sop = null)
    {
        // karena unique gabungan code+version ditangani DB,
        // code TIDAK unique tunggal lagi.
        $rules = [
            'code'           => ['required', 'string', 'max:50'],
            'title'          => ['required', 'string', 'max:255'],
            'department'     => ['required', 'string', 'max:100'],
            'product'        => ['nullable', 'string', 'max:100'],
            'line'           => ['nullable', 'string', 'max:100'],
            'content'        => ['nullable', 'string'],
            'effective_from' => ['nullable', 'date'],
            'effective_to'   => ['nullable', 'date', 'after_or_equal:effective_from'],

            'photos'         => ['nullable', 'array', 'max:10'],
            'photos.*'       => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'photo_desc'     => ['nullable', 'array'],
            'photo_desc.*'   => ['nullable', 'string', 'max:255'],

            'pin'            => ['nullable', 'string', 'max:20'],
            'is_public'      => ['nullable', 'boolean'],
        ];

        // kalau update SOP belum approved (same record),
        // cegah bentrok jika user ubah CODE tapi versi sama.
        if ($sop && $sop->status !== 'approved') {
            $rules['code'] = [
                'required','string','max:50',
                Rule::unique('sops', 'code')
                    ->where(fn($q) => $q->where('version', $sop->version))
                    ->ignore($sop->id),
            ];
        }

        return $request->validate($rules, [
            'effective_to.after_or_equal' => 'Tanggal berlaku sampai harus setelah/sama dengan tanggal berlaku mulai.',
            'photos.max' => 'Maksimal 10 foto per SOP.',
            'photos.*.image' => 'File foto harus berupa gambar.',
            'photos.*.max' => 'Ukuran foto maksimal 4MB.',
        ]);
    }

    private function handlePhotosUpload(Request $request): array
    {
        $photosPayload = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $i => $file) {
                if (!$file) continue;

                $path = $file->store('sops', 'public');
                $desc = $request->input("photo_desc.$i") ?? null;

                $photosPayload[] = [
                    'path' => $path,
                    'desc' => $desc,
                ];
            }
        }

        return $photosPayload;
    }

    private function authorizeManage()
    {
        if (!auth()->user()->isRole(['admin','produksi'])) {
            abort(403, 'Anda tidak punya akses mengelola SOP.');
        }
    }

    private function authorizeApprover()
    {
        if (!auth()->user()->isRole(['admin','produksi','qa','logistik'])) {
            abort(403, 'Anda tidak punya akses.');
        }
    }

    private function stampApproval(Sop $sop, string $stage)
    {
        $byCol = "approved_by_{$stage}";
        $atCol = "approved_at_{$stage}";

        if (Schema::hasColumn('sops', $byCol)) $sop->{$byCol} = auth()->id();
        if (Schema::hasColumn('sops', $atCol)) $sop->{$atCol} = now();
    }
}
