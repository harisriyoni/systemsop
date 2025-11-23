<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use App\Models\SopTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SopTemplateController extends Controller
{
    // ==========================
    // LIST TEMPLATES
    // ==========================
    public function index(Request $request)
    {
        $this->authorizeManage();

        $q      = trim($request->q ?? '');
        $dept   = trim($request->department ?? '');
        $active = $request->active; // 1/0/null

        $query = SopTemplate::query()->orderByDesc('updated_at');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        if ($dept !== '') {
            $query->where('department', 'like', "%{$dept}%");
        }

        if ($active !== null && $active !== '') {
            $query->where('is_active', (bool) $active);
        }

        $templates = $query->paginate(12)->withQueryString();

        return view('sop_templates.index', compact('templates'));
    }

    // ==========================
    // CREATE
    // ==========================
   public function create()
{
    $this->authorizeManage();

    $sops = Sop::query()
        ->orderByDesc('updated_at')
        ->limit(300)
        ->get(['id','code','title','department','product','line','status','updated_at']);

    return view('sop_templates.create', [
        'template' => new SopTemplate([
            'is_active'      => true,
            'form_schema'    => [],
            'builder_schema' => [],
            'meta'           => ['extra_fields' => []],
            'canvas'         => [ 'page' => [], 'blocks' => [] ], // <— NEW
        ]),
        'sops' => $sops,
    ]);
}


    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $this->validatePayload($request);

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $tpl = SopTemplate::create($data);

        return redirect()
            ->route('sop.templates.edit', $tpl)
            ->with('success', 'Template SOP berhasil dibuat.');
    }

    // ==========================
    // EDIT / UPDATE
    // ==========================
    public function edit(SopTemplate $template)
    {
        $this->authorizeManage();

        /**
         * Supaya editing template juga bisa import dari SOP,
         * kalau mau: load lagi $sops seperti di create()
         * (optional).
         */
        $sops = Sop::query()
            ->orderByDesc('updated_at')
            ->limit(300)
            ->get(['id','code','title','department','product','line','status','updated_at']);

        return view('sop_templates.edit', [
            'template' => $template,
            'sops'     => $sops,
        ]);
    }

    public function update(Request $request, SopTemplate $template)
    {
        $this->authorizeManage();

        $data = $this->validatePayload($request, $template);
        $data['updated_by'] = auth()->id();

        $template->update($data);

        return back()->with('success', 'Template SOP berhasil diperbarui.');
    }

    // ==========================
    // DELETE
    // ==========================
    public function destroy(SopTemplate $template)
    {
        $this->authorizeManage();

        // optional: blok hapus kalau template dipakai SOP
        if ($template->sops()->exists()) {
            return back()->with('error', 'Template masih dipakai SOP, tidak boleh dihapus.');
        }

        $template->delete();

        return redirect()
            ->route('sop.templates.index')
            ->with('success', 'Template SOP berhasil dihapus.');
    }

    // ==========================
    // JSON SOP (BUAT IMPORT KE TEMPLATE)
    // ==========================
    public function showJson($id)
    {
        $sop = Sop::query()
            ->with(['creator', 'approvers.user'])
            ->findOrFail($id);

        $attributes = $sop->getAttributes();

        $sop->form_schema    = $sop->form_schema ?? [];
        $sop->builder_schema = $sop->builder_schema ?? [];
        $sop->meta           = $sop->meta ?? [];

        $attributes['form_schema']    = $sop->form_schema;
        $attributes['builder_schema'] = $sop->builder_schema;
        $attributes['meta']           = $sop->meta;

        return response()->json([
            'id'             => $sop->id,
            'code'           => $sop->code,
            'title'          => $sop->title,
            'department'     => $sop->department,
            'product'        => $sop->product,
            'line'           => $sop->line,
            'version'        => $sop->version ?? 1,
            'revision'       => $sop->revision ?? 1,
            'status'         => $sop->status ?? 'draft',
            'is_active'      => $sop->is_active ?? false,
            'created_at'     => $sop->created_at,
            'updated_at'     => $sop->updated_at,
            'created_by'     => $sop->creator->name ?? null,

            'approvers'      => $sop->approvers->map(fn($a) => [
                'id'          => $a->id,
                'role'        => $a->role,
                'name'        => $a->user->name ?? null,
                'status'      => $a->status,
                'approved_at' => $a->approved_at,
            ])->values(),

            'form_schema'    => $sop->form_schema,
            'builder_schema' => $sop->builder_schema,
            'meta'           => $sop->meta,

            '_attributes'    => $attributes,
        ]);
    }

    // ==========================
    // SHOW PDF PREVIEW TEMPLATE
    // ==========================
    public function show(SopTemplate $template)
    {
        $this->authorizeManage();

        $template->form_schema    = $template->form_schema ?? [];
        $template->builder_schema = $template->builder_schema ?? [];
        $template->meta           = $template->meta ?? [];
        $template->canvas         = $template->canvas ?? []; // optional

        $pdf = \PDF::loadView('sop_templates.show_pdf', compact('template'))
            ->setPaper('a4');

        return response($pdf->stream("template-{$template->code}.pdf"))
            ->header('Content-Type', 'application/pdf')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    // ==========================
    // VALIDATION
    // ==========================
   private function validatePayload(Request $request, ?SopTemplate $template = null)
{
    $rules = [
        'name'        => ['required', 'string', 'max:150'],
        'code'        => ['nullable', 'string', 'max:50'],
        'department'  => ['nullable', 'string', 'max:100'],
        'product'     => ['nullable', 'string', 'max:100'],
        'line'        => ['nullable', 'string', 'max:100'],

        // JSON string dari textarea / hidden input
        'form_schema'    => ['nullable', 'string'],
        'builder_schema' => ['nullable', 'string'],
        'meta'           => ['nullable', 'string'],
        'canvas'         => ['nullable', 'string'], // <— NEW

        'is_active'      => ['nullable', 'boolean'],
    ];

    if ($request->filled('code')) {
        $rules['code'][] = Rule::unique('sop_templates', 'code')
            ->ignore($template?->id);
    }

    $validated = $request->validate($rules);

    // decode JSON → array/null
    foreach (['form_schema', 'builder_schema', 'meta', 'canvas'] as $jsonKey) { // <— canvas ikut
        if (!empty($validated[$jsonKey] ?? null)) {
            $decoded = json_decode($validated[$jsonKey], true);
            $validated[$jsonKey] = is_array($decoded) ? $decoded : null;
        } else {
            $validated[$jsonKey] = null;
        }
    }

    $validated['is_active'] = $request->boolean('is_active');

    return $validated;
}


    private function authorizeManage()
    {
        if (!auth()->user()->isRole(['admin', 'produksi'])) {
            abort(403, 'Anda tidak punya akses mengelola SOP Template.');
        }
    }
}
