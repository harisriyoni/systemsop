<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use App\Models\SopTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\SopRawMaterial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SopController extends Controller
{
    // ==========================
    // LIST SOP
    // ==========================
    public function index(Request $request)
    {
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
            $query->where('product', 'like', "%" . trim($request->product) . "%");
        }

        if ($request->filled('line')) {
            $query->where('line', 'like', "%" . trim($request->line) . "%");
        }

        $sops = $query->paginate(10)->withQueryString();
        return view('sop.index', compact('sops'));
    }

    // ==========================
    // CREATE SOP (WITH TEMPLATE PICK)
    // ==========================
    public function create(Request $request)
    {
        // templates aktif
        $templates = SopTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // default schema core
        $defaultFormSchema = [
            ['key' => 'code', 'label' => 'Kode SOP', 'type' => 'text', 'required' => true, 'visible' => true, 'is_core' => true],
            ['key' => 'title', 'label' => 'Judul SOP', 'type' => 'text', 'required' => true, 'visible' => true, 'is_core' => true],
            ['key' => 'department', 'label' => 'Departemen', 'type' => 'select', 'options' => ['Produksi', 'QA', 'Logistik'], 'required' => true, 'visible' => true, 'is_core' => true],
            ['key' => 'product', 'label' => 'Produk', 'type' => 'text', 'required' => false, 'visible' => true, 'is_core' => true],
            ['key' => 'line', 'label' => 'Line Produksi', 'type' => 'text', 'required' => false, 'visible' => true, 'is_core' => true],
            ['key' => 'effective_from', 'label' => 'Tanggal Berlaku Dari', 'type' => 'date', 'required' => false, 'visible' => true, 'is_core' => true],
            ['key' => 'effective_to', 'label' => 'Tanggal Berlaku Sampai', 'type' => 'date', 'required' => false, 'visible' => false, 'is_core' => true],
            ['key' => 'is_public', 'label' => 'Tersedia untuk Publik', 'type' => 'checkbox', 'required' => false, 'visible' => true, 'is_core' => true],
            ['key' => 'pin', 'label' => 'PIN Akses (Opsional)', 'type' => 'text', 'required' => false, 'visible' => true, 'is_core' => true],

            ['key' => 'meta.mesin', 'label' => 'Nama Mesin', 'type' => 'text', 'required' => false, 'visible' => false, 'is_core' => false],
        ];

        $defaultBuilderSchema = [];

        // kalau pilih template dari UI (query ?template_id=)
        $selectedTemplate = null;
        if ($request->filled('template_id')) {
            $selectedTemplate = SopTemplate::find($request->template_id);
        }

        // prefill dari template
        if ($selectedTemplate) {
            $defaultFormSchema   = $selectedTemplate->form_schema ?: $defaultFormSchema;
            $defaultBuilderSchema = $selectedTemplate->builder_schema ?: [];
        }

        return view('sop.create', [
            'formSchema'     => $defaultFormSchema,
            'builderSchema'  => $defaultBuilderSchema,
            'templates'      => $templates,
            'selectedTemplate' => $selectedTemplate,
            'rawMaterials'   => [], // Kirim array kosong
        ]);
    }

    public function store(Request $request)
    {
        // 1) JSON builder + extra
        $builderSchemaJson = $request->input('builder_schema');
        $extraFieldsJson   = $request->input('extra_fields');

        $builderSchema = $builderSchemaJson ? json_decode($builderSchemaJson, true) : [];
        if (!is_array($builderSchema)) $builderSchema = [];

        $extraFields = $extraFieldsJson ? json_decode($extraFieldsJson, true) : [];
        if (!is_array($extraFields)) $extraFields = [];

        // 2) VALIDASI CORE + RAW MATERIALS (Sesuai input terbaru)
        $request->validate([
            'template_id'      => ['nullable', 'integer', 'exists:sop_templates,id'],

            'code'             => ['required', 'string', 'max:50', 'unique:sops,code'],
            'title'            => ['required', 'string', 'max:255'],
            'department'       => ['required', 'string', 'max:100'],
            'product'          => ['nullable', 'string', 'max:100'],
            'line'             => ['nullable', 'string', 'max:100'],
            'content'          => ['nullable', 'string'],
            'effective_from'   => ['nullable', 'date'],
            'effective_to'     => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_public'        => ['nullable', 'boolean'],
            'pin'              => ['nullable', 'string', 'max:20'],

            'photos'           => ['nullable', 'array', 'max:10'],
            'photos.*'         => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'photo_desc'       => ['nullable', 'array'],
            'photo_desc.*'     => ['nullable', 'string', 'max:255'],

            // ATURAN VALIDASI RAW MATERIALS
            'raw_materials'            => ['nullable', 'array'],
            'raw_materials.*.name'     => ['required', 'string', 'max:255'],
            'raw_materials.*.amount'   => ['nullable', 'numeric'],
            'raw_materials.*.unit'     => ['nullable', 'string', 'max:50'],
            'raw_materials.*.image'    => ['nullable', 'image', 'max:2048'],
            'raw_materials.*.notes'    => ['nullable', 'string'],
        ]);

        $coreFields = [
            'code', 'title', 'department', 'product', 'line', 'content',
            'effective_from', 'effective_to', 'is_public', 'pin', 'template_id'
        ];
        $data = $request->only($coreFields);
        $data['is_public'] = $request->boolean('is_public');

        // 2b) kalau template dipilih → apply default2 dari template
        if (!empty($data['template_id'])) {
            $tpl = SopTemplate::find($data['template_id']);
            if ($tpl) {
                $data = $this->applyTemplateToData($data, $tpl);
                if (empty($builderSchema)) {
                    $builderSchema = $tpl->builder_schema ?: [];
                }
            }
        }

        // 3) normalisasi extra_fields → meta
        $normalizedExtra = [];
        foreach ($extraFields as $row) {
            if (!is_array($row)) continue;

            $label = trim((string)($row['label'] ?? ''));
            $value = trim((string)($row['value'] ?? ''));

            if ($label === '' && $value === '') continue;

            $normalizedExtra[] = [
                'label' => $label !== '' ? $label : '-',
                'value' => $value !== '' ? $value : '-',
            ];
        }

        // --- Ambil form_values (field dinamis) dari request ---
        $formValues = $request->input('form_values', []);
        if (!is_array($formValues)) $formValues = [];

        // Normalisasi sederhana: trim string values (jaga supaya tetap simple)
        foreach ($formValues as $k => $v) {
            if (is_string($v)) $formValues[$k] = trim($v);
        }

        // optional: validasi ringan untuk dua field spesifik (boleh hapus/ubah)
        $request->validate([
            'form_values.lot_name'      => ['nullable', 'string', 'max:150'],
            'form_values.operator_name' => ['nullable', 'string', 'max:150'],
        ]);

        $meta = [
            'extra_fields'   => $normalizedExtra,
            'builder_schema' => $builderSchema,
            'form_values'    => $formValues, // <--- simpan di meta
            'logs'           => [],
        ];


        // 4) upload foto
        $photos = $this->handlePhotosUpload($request);

        // 5) simpan SOP v1
        $sop = Sop::create([
            ...$data,
            'photos'         => count($photos) ? $photos : null,
            'form_schema'    => [],
            'builder_schema' => $builderSchema,
            'meta'           => $meta,
            'created_by'     => $request->user()->id,
            'status'         => 'draft',
            'version'        => 1,
        ]);

        // ==========================================
        // INTEGRASI: PROSES RAW MATERIALS
        // ==========================================
        $rawMaterialsInputs = $request->input('raw_materials', []);
        if (!empty($rawMaterialsInputs)) {
            // Karena ini SOP baru, kita hanya perlu "Create All"
            $this->processRawMaterials($sop, $rawMaterialsInputs, $request);
        }

        return redirect()
            ->route('sop.show', $sop)
            ->with('success', 'SOP berhasil dibuat (v' . ($sop->version ?? 1) . ').');
    }

    // ==========================
    // EDIT / UPDATE SOP
    // ==========================
    public function edit(Sop $sop)
    {
        $this->authorizeManage();
        
        $templates = SopTemplate::where('is_active', true)->orderBy('name')->get();

        // INTEGRASI: Load Raw Materials
        $sop->load('rawMaterials');

        return view('sop.edit', compact('sop', 'templates'));
    }

   public function update(Request $request, Sop $sop)
{
    // 1. Otorisasi
    // $this->authorizeManage(); 

    // 2. Validasi Input
    $request->validate([
        'code'            => ['required', 'string', 'max:50', Rule::unique('sops')->ignore($sop->id)],
        'title'           => ['required', 'string', 'max:255'],
        'department'      => ['required', 'string', 'max:100'],
        'product'         => ['nullable', 'string', 'max:100'],
        'line'            => ['nullable', 'string', 'max:100'],
        'effective_from'  => ['nullable', 'date'],
        'effective_to'    => ['nullable', 'date', 'after_or_equal:effective_from'],
        'is_public'       => ['nullable', 'boolean'],
        'pin'             => ['nullable', 'string', 'max:20'],
        'content'         => ['nullable', 'string'],
        
        // Foto Utama
        'photos'          => ['nullable', 'array', 'max:10'],
        'photos.*'        => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        
        // Raw Materials
        'raw_materials'   => ['nullable', 'array'],
        'raw_materials.*.id'     => ['nullable', 'integer'], 
        'raw_materials.*.name'   => ['required', 'string', 'max:255'],
        'raw_materials.*.amount' => ['nullable', 'numeric'],
        'raw_materials.*.unit'   => ['nullable', 'string', 'max:50'],
        'raw_materials.*.notes'  => ['nullable', 'string'],
        'raw_materials.*.image'  => ['nullable', 'image', 'max:2048'], 
    ]);

    // 3. Persiapan Data JSON
    $builderSchema = $request->input('builder_schema') ? json_decode($request->input('builder_schema'), true) : [];
    $extraFields   = $request->input('extra_fields') ? json_decode($request->input('extra_fields'), true) : [];
    
    $normalizedExtra = [];
    if(is_array($extraFields)){
        foreach ($extraFields as $row) {
            if (!empty($row['label']) || !empty($row['value'])) {
                $normalizedExtra[] = [
                    'label' => $row['label'] ?? '-',
                    'value' => $row['value'] ?? '-'
                ];
            }
        }
    }

    $formValues = $request->input('form_values', []);

    // 4. Handle Foto Utama SOP (Metode Hapus & Tambah)
    $currentPhotos = is_string($sop->photos) ? json_decode($sop->photos, true) : ($sop->photos ?? []);
    if (!is_array($currentPhotos)) $currentPhotos = [];

    // Hapus foto yang dicentang
    $removePaths = $request->input('remove_photos', []);
    if (!empty($removePaths)) {
        $currentPhotos = array_filter($currentPhotos, function($p) use ($removePaths) {
            $path = $p['path'] ?? ($p['url'] ?? null);
            if (in_array($path, $removePaths)) {
                Storage::disk('public')->delete($path); // Hapus fisik
                return false; 
            }
            return true;
        });
    }

    // Upload foto baru SOP
    $newPhotos = [];
    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $idx => $file) {
            $path = $file->store('sop-photos', 'public');
            $desc = $request->input("photo_desc.$idx");
            $newPhotos[] = [
                'path' => $path,
                'url'  => asset('storage/' . $path), // Helper asset standar Laravel
                'desc' => $desc
            ];
        }
    }

    $finalPhotos = array_merge(array_values($currentPhotos), $newPhotos);

    // 5. Payload
    $payload = [
        'code'           => $request->code,
        'title'          => $request->title,
        'department'     => $request->department,
        'product'        => $request->product,
        'line'           => $request->line,
        'content'        => $request->content,
        'effective_from' => $request->effective_from,
        'effective_to'   => $request->effective_to,
        'is_public'      => $request->boolean('is_public'),
        'pin'            => $request->pin,
        'photos'         => !empty($finalPhotos) ? $finalPhotos : null,
        'builder_schema' => $builderSchema,
        'meta'           => [
            'extra_fields' => $normalizedExtra,
            'form_values'  => $formValues,
            'logs'         => $sop->meta['logs'] ?? [] 
        ]
    ];

    DB::beginTransaction();
    try {
        // ==========================================
        // KASUS 1: REVISI BARU (SOP SUDAH APPROVED)
        // ==========================================
        if ($sop->status === 'approved') {
            $lastVer = Sop::where('code', $request->code)->max('version');
            $payload['version'] = ($lastVer ?? $sop->version) + 1;
            $payload['status'] = 'draft';
            $payload['created_by'] = auth()->id();
            
            // Reset approval
            $payload['is_approved_produksi'] = false;
            $payload['is_approved_qa'] = false;
            $payload['is_approved_logistik'] = false;

            $newSop = Sop::create($payload);
            
            // Proses Raw Materials (Copy Logic)
            $this->processRawMaterials($newSop, $request->input('raw_materials', []), $request, true);

            DB::commit();
            return redirect()->route('sop.edit', $newSop)
                ->with('success', 'Versi baru (v'.$newSop->version.') berhasil dibuat dari revisi.');
        } 
        
        // ==========================================
        // KASUS 2: UPDATE BIASA (DRAFT)
        // ==========================================
        else {
            $sop->update($payload);
            
            // Proses Raw Materials (Sync Logic)
            $this->processRawMaterials($sop, $request->input('raw_materials', []), $request, false);

            DB::commit();
            return redirect()->route('sop.edit', $sop) // Redirect ke edit lagi biar user lihat hasilnya
                ->with('success', 'SOP berhasil diperbarui.');
        }

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
}
    public function destroy(Sop $sop)
{
    // 1. Otorisasi
    if (!auth()->user()->isRole(['admin'])) {
        return back()->with('error', 'Hanya admin yang boleh menghapus SOP.');
    }

    // 2. Hapus Foto Utama SOP
    $photos = is_array($sop->photos) ? $sop->photos : (json_decode($sop->photos, true) ?: []);
    foreach ($photos as $p) {
        if (!empty($p['path'])) Storage::disk('public')->delete($p['path']);
    }

    // 3. INTEGRASI: Hapus Foto Raw Materials 🗑️
    $sop->load('rawMaterials');
    foreach ($sop->rawMaterials as $material) {
        if ($material->image_path) {
            // Hapus file fisik dari public disk
            Storage::disk('public')->delete($material->image_path);
        }
    }

    // 4. Hapus Record SOP dari Database
    $sop->delete();
    
    // PERUBAHAN DISINI: Gunakan back() untuk kembali ke halaman sebelumnya (reload)
    return back()->with('success', 'SOP berhasil dihapus.');
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
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $sop->status = 'draft';
        $sop->is_approved_produksi = false;
        $sop->is_approved_qa       = false;
        $sop->is_approved_logistik = false;

        if (Schema::hasColumn('sops', 'rejected_reason')) $sop->rejected_reason = $request->reason;
        if (Schema::hasColumn('sops', 'rejected_by'))     $sop->rejected_by = auth()->id();
        if (Schema::hasColumn('sops', 'rejected_at'))     $sop->rejected_at = now();

        $sop->save();
        return back()->with('success', 'SOP ditolak dan dikembalikan ke Draft.');
    }

    // ==========================
    // INTERNAL SHOW (LOGIN)
    // ==========================
    public function show(Sop $sop)
    {
        $qrUrl = $sop->is_public && Route::has('sop.public.show')
            ? route('sop.public.show', $sop)
            : route('sop.show', $sop);

        return view('sop.show', compact('sop', 'qrUrl'));
    }

    // ==========================
    // ✅ JSON SOP (BUAT IMPORT KE TEMPLATE)
    // terima ID atau CODE
    // ==========================
    public function showJson(Sop $sop)
    {
        // ==== Normalisasi JSON field ====
        $formSchema     = $sop->form_schema ?? [];
        $builderSchema = $sop->builder_schema ?? [];
        $meta          = $sop->meta ?? [];
        $formValues = is_array($meta['form_values'] ?? null) ? $meta['form_values'] : [];


        if (is_string($formSchema)) {
            $formSchema = json_decode($formSchema, true) ?: [];
        }
        if (is_string($builderSchema)) {
            $builderSchema = json_decode($builderSchema, true) ?: [];
        }
        if (is_string($meta)) {
            $meta = json_decode($meta, true) ?: [];
        }

        // ==== Normalisasi foto (LOGIC SAMA DENGAN VIEW DETAIL SOP) ====
        $rawPhotos = $sop->photos ?? [];
        if (is_string($rawPhotos)) {
            $rawPhotos = json_decode($rawPhotos, true) ?: [];
        }

        $photos = [];
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

            // Kalau sudah URL full, pakai langsung
            $isHttp = \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//']);
            if ($isHttp) {
                $url = $path;
            } else {
                // Bersihkan prefix yang mungkin ikut ke DB
                $cleanPath = preg_replace('#^storage/(app/public/)?#', '', ltrim($path, '/'));

                if (app()->environment('local')) {
                    // LOCAL: standar Laravel -> public/storage/...
                    $publicPath = 'storage/' . $cleanPath;
                } else {
                    // PRODUKSI (Hostinger dsb): storage/app/public/...
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

        // ==== Atribut mentah SOP (untuk disimpan ke meta._sop_attributes) ====
        $attributes = $sop->getAttributes();

        // Biar di _sop_attributes versi JSON-nya sudah didecode
        $attributes['form_schema']     = $formSchema;
        $attributes['builder_schema'] = $builderSchema;
        $attributes['meta']           = $meta;
        $attributes['photos_normalized'] = $photos;

        return response()->json([
            'id'         => $sop->id,
            'code'       => $sop->code,
            'title'      => $sop->title,
            'department' => $sop->department,
            'product'    => $sop->product,
            'line'       => $sop->line,
            'version'    => $sop->version ?? 1,
            'status'     => $sop->status ?? 'draft',

            // JSON field utama
            'form_schema'    => $formSchema,
            'builder_schema' => $builderSchema,
            'meta'           => $meta,
            'form_values'    => $formValues,

            // Foto yang sudah punya URL siap pakai (INI yang dipakai Canvas)
            'photos'      => $photos,

            // Semua atribut mentah (plus yang sudah didecode) buat disimpan ke meta._sop_attributes
            '_attributes' => $attributes,
        ]);
    }

    // ==========================
    // PUBLIC SHOW (QR TANPA LOGIN)
    // ==========================
    public function publicShow(Request $request, Sop $sop)
    {
        if (!$sop->is_public || $sop->status !== 'approved') abort(404);

        $qrUrl = route('sop.public.show', $sop);

        $locked = false;
        if ($sop->pin) {
            $sessionKey = "sop_unlocked_{$sop->id}";
            $locked = !$request->session()->get($sessionKey, false);
        }

        return view('sop.show', compact('sop', 'qrUrl', 'locked'));
    }

    public function publicUnlock(Request $request, Sop $sop)
    {
        if (!$sop->is_public || $sop->status !== 'approved') abort(404);
        if (!$sop->pin) return redirect()->route('sop.public.show', $sop);

        $request->validate(['pin' => ['required', 'string', 'max:20']]);

        if ($request->pin !== $sop->pin) {
            return back()->with('error', 'PIN salah.');
        }

        $sessionKey = "sop_unlocked_{$sop->id}";
        $request->session()->put($sessionKey, true);
        return redirect()->route('sop.public.show', $sop);
    }

    public function publicAck(Request $request, Sop $sop)
    {
        if (!$sop->is_public || $sop->status !== 'approved') abort(404);
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
            $fileName = 'qr-sop-' . $sop->id . '-' . Str::random(6) . '.png';
            $qrPath   = 'qr/' . $fileName;

            $png = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(600)->margin(2)->generate($url);

            Storage::disk('public')->put($qrPath, $png);
            $qrUrl = Storage::disk('public')->url($qrPath);
        }

        if (Schema::hasColumn('sops', 'qr_path')) $sop->qr_path = $qrPath;
        if (Schema::hasColumn('sops', 'qr_url'))  $sop->qr_url  = $qrUrl;

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

            $filename = ($sop->code ?? 'sop') . '_v' . ($sop->version ?? 1) . '.pdf';
            return $pdf->download($filename);
        }

        return redirect()->route('sop.show', $sop)
            ->with('error', 'Export PDF belum aktif (Dompdf belum terpasang).');
    }

    // ==========================
    // VERSIONS / HISTORY (ALL)
    // ==========================
    public function versionsIndex(Request $request)
    {
        $this->authorizeView();

        $sub = Sop::query()
            ->selectRaw('code, MAX(version) as max_version')
            ->groupBy('code');

        $query = Sop::query()
            ->joinSub($sub, 'mx', function ($join) {
                $join->on('sops.code', '=', 'mx.code')
                    ->on('sops.version', '=', 'mx.max_version');
            })
            ->select('sops.*')
            ->orderByDesc('sops.updated_at');

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($subq) use ($keyword) {
                $subq->where('sops.code', 'like', "%{$keyword}%")
                    ->orWhere('sops.title', 'like', "%{$keyword}%");
            });
        }

        $latestSops = $query->paginate(15)->withQueryString();
        return view('sop.versions_index', compact('latestSops'));
    }

    public function historyIndex(Request $request)
    {
        $this->authorizeView();

        $query = Sop::orderByDesc('updated_at');

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($subq) use ($keyword) {
                $subq->where('code', 'like', "%{$keyword}%")
                    ->orWhere('sops.title', 'like', "%{$keyword}%");
            });
        }

        $items = $query->paginate(20)->withQueryString();
        return view('sop.history_index', compact('items'));
    }

    // ==========================
    // VERSIONS / HISTORY (PER SOP)
    // ==========================
    public function versions(Sop $sop)
    {
        $this->authorizeView();

        $versions = Sop::where('code', $sop->code)
            ->orderByDesc('version')
            ->get();

        return view('sop.versions', compact('sop', 'versions'));
    }

    public function history(Sop $sop)
    {
        $this->authorizeView();

        $meta = is_array($sop->meta) ? $sop->meta : (json_decode($sop->meta, true) ?: []);
        $logs = $meta['logs'] ?? [];

        return view('sop.history', compact('sop', 'logs'));
    }

    // ==========================
    // HELPER
    // ==========================
    private function validatePayload(Request $request, ?Sop $sop = null)
    {
        $rules = [
            'template_id'      => ['nullable', 'integer', 'exists:sop_templates,id'],

            'code'             => ['required', 'string', 'max:50'],
            'title'            => ['required', 'string', 'max:255'],
            'department'       => ['required', 'string', 'max:100'],
            'product'          => ['nullable', 'string', 'max:100'],
            'line'             => ['nullable', 'string', 'max:100'],
            'content'          => ['nullable', 'string'],
            'effective_from'   => ['nullable', 'date'],
            'effective_to'     => ['nullable', 'date', 'after_or_equal:effective_from'],

            'photos'           => ['nullable', 'array', 'max:10'],
            'photos.*'         => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'photo_desc'       => ['nullable', 'array'],
            'photo_desc.*'     => ['nullable', 'string', 'max:255'],

            'builder_schema'   => ['nullable', 'string'],
            'extra_fields'     => ['nullable', 'string'],

            // dynamic form values
            'form_values'      => ['nullable', 'array'],
            'form_values.*'    => ['nullable', 'string', 'max:1000'],

            'pin'              => ['nullable', 'string', 'max:20'],
            'is_public'        => ['nullable', 'boolean'],
            
            // ATURAN RAW MATERIALS DITAMBAHKAN DI SINI
            'raw_materials'            => ['nullable', 'array'],
            'raw_materials.*.id'       => ['nullable', 'integer'], 
            'raw_materials.*.name'     => ['required', 'string', 'max:255'],
            'raw_materials.*.amount'   => ['nullable', 'numeric'],
            'raw_materials.*.unit'     => ['nullable', 'string', 'max:50'],
            'raw_materials.*.notes'    => ['nullable', 'string'],
            'raw_materials.*.image'    => ['nullable', 'image', 'max:2048'],
        ];

        if ($sop && $sop->status !== 'approved') {
            $rules['code'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('sops', 'code')
                    ->where(fn($q) => $q->where('version', $sop->version))
                    ->ignore($sop->id),
            ];
        }

        $validated = $request->validate($rules, [
            'effective_to.after_or_equal' => 'Tanggal berlaku sampai harus setelah/sama dengan tanggal berlaku mulai.',
            'photos.max' => 'Maksimal 10 foto per SOP.',
            'photos.*.image' => 'File foto harus berupa gambar.',
            'photos.*.max' => 'Ukuran foto maksimal 4MB.',
            'raw_materials.*.name.required' => 'Nama material wajib diisi.', // Custom message for raw material
        ]);

        if (!empty($validated['builder_schema'] ?? null)) {
            $decoded = json_decode($validated['builder_schema'], true);
            $validated['builder_schema'] = is_array($decoded) ? $decoded : null;
        } else {
            $validated['builder_schema'] = null;
        }

        return $validated;
    }

    private function applyTemplateToData(array $data, SopTemplate $tpl): array
    {
        // merge form_values dari template (jika template menyimpan default di meta.form_values)
        $tplFormValues = is_array($tpl->meta['form_values'] ?? null) ? $tpl->meta['form_values'] : [];
        $dataFormValues = $data['form_values'] ?? [];

        foreach ($tplFormValues as $k => $v) {
            if ((empty($dataFormValues[$k]) || $dataFormValues[$k] === '') && !empty($v)) {
                $dataFormValues[$k] = $v;
            }
        }
        $data['form_values'] = $dataFormValues;


        // default public/pin dari template jika user ga set
        if (!array_key_exists('is_public', $data) || is_null($data['is_public'])) {
            $data['is_public'] = (bool)($tpl->is_public_default ?? false);
        }
        if (empty($data['pin']) && !empty($tpl->pin_default)) {
            $data['pin'] = $tpl->pin_default;
        }

        return $data;
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
        if (!auth()->user()->isRole(['admin', 'produksi'])) {
            abort(403, 'Anda tidak punya akses mengelola SOP.');
        }
    }

    private function authorizeView()
    {
        if (!auth()->check()) {
            abort(403, 'Anda tidak punya akses.');
        }
    }

    private function authorizeApprover()
    {
        if (!auth()->user()->isRole(['admin', 'produksi', 'qa', 'logistik'])) {
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
    
    // ==========================
    // HELPER RAW MATERIALS
    // ==========================
    
    /**
     * Handle Create/Update/Delete Raw Materials
     * Logic:
     * - Jika punya ID -> Update row
     * - Jika tidak punya ID -> Create row baru
     * - Jika ID ada di DB tapi tidak ada di input -> Delete (Logic ini opsional, tergantung UI. 
     * Biasanya di UI repeater, item yang dihapus tidak dikirim ke server).
     */
    private function processRawMaterials($sopModel, array $inputs, Request $request, bool $isRevision = false)
{
    // 1. Identifikasi ID yang tetap dipertahankan
    $submittedIds = [];
    foreach ($inputs as $row) {
        if (!empty($row['id'])) {
            $submittedIds[] = $row['id'];
        }
    }

    // 2. Logic Hapus: Hanya jika BUKAN revisi baru (kalau revisi, kita create all)
    if (!$isRevision) {
        // Ambil ID yang ada di DB saat ini
        $existingIds = $sopModel->rawMaterials()->pluck('id')->toArray();
        
        // Cari ID yang ada di DB tapi TIDAK ada di form submit => HAPUS
        $idsToDelete = array_diff($existingIds, $submittedIds);
        
        if (!empty($idsToDelete)) {
            $toDeleteItems = SopRawMaterial::whereIn('id', $idsToDelete)->get();
            foreach ($toDeleteItems as $item) {
                // Hapus file fisik
                if ($item->image_path) {
                    Storage::disk('public')->delete($item->image_path);
                }
                $item->delete();
            }
        }
    }

    // 3. Loop Inputs untuk Create/Update
    foreach ($inputs as $index => $row) {
        $inputRowId = $row['id'] ?? null;
        
        // Cari data lama jika ada ID-nya (untuk ambil path gambar lama)
        $oldRecord = $inputRowId ? SopRawMaterial::find($inputRowId) : null;

        // Siapkan data dasar
        $dataToSave = [
            'sop_id' => $sopModel->id, // Link ke SOP (baru/lama)
            'name'   => $row['name'],
            'amount' => $row['amount'] ?? null,
            'unit'   => $row['unit'] ?? null,
            'notes'  => $row['notes'] ?? null,
        ];

        // --- LOGIC GAMBAR UTAMA ---
        // Cek apakah user upload file baru di baris ini?
        if ($request->hasFile("raw_materials.$index.image")) {
            // Upload file baru
            $file = $request->file("raw_materials.$index.image");
            $path = $file->store('raw-materials', 'public');
            $dataToSave['image_path'] = $path;

            // Jika ini update record yang sama, hapus file lama untuk hemat space
            if (!$isRevision && $oldRecord && $oldRecord->image_path) {
                Storage::disk('public')->delete($oldRecord->image_path);
            }
        } else {
            // TIDAK upload file baru.
            // Gunakan path lama jika ada record sebelumnya.
            if ($oldRecord) {
                $dataToSave['image_path'] = $oldRecord->image_path;
            }
        }

        // --- EKSEKUSI DB ---
        if ($isRevision) {
            // Kalau revisi, SEMUA dianggap record baru (Clone)
            // Walaupun punya ID dari SOP lama, kita abaikan ID itu dan create baru
            SopRawMaterial::create($dataToSave);
        } else {
            // Kalau update biasa
            if ($inputRowId && $oldRecord) {
                // Update
                $oldRecord->update($dataToSave);
            } else {
                // Create baru (baris tambahan)
                SopRawMaterial::create($dataToSave);
                }
            }
        }
    }
}