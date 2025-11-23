<?php

namespace App\Http\Controllers;

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

        $q = trim($request->q ?? '');
        $dept = trim($request->department ?? '');
        $active = $request->active; // 1/0/null

        $query = SopTemplate::query()->orderByDesc('updated_at');

        if ($q !== '') {
            $query->where(function($sub) use ($q){
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('code', 'like', "%$q%");
            });
        }
        if ($dept !== '') {
            $query->where('department','like',"%$dept%");
        }
        if ($active !== null && $active !== '') {
            $query->where('is_active', (bool)$active);
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

        return view('sop_templates.create', [
            'template' => new SopTemplate([
                'is_active' => true,
                'form_schema' => [],
                'builder_schema' => [],
                'meta' => ['extra_fields'=>[]],
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $this->validatePayload($request);

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $tpl = SopTemplate::create($data);

        return redirect()->route('sop.templates.edit', $tpl)
            ->with('success','Template SOP berhasil dibuat.');
    }

    // ==========================
    // EDIT / UPDATE
    // ==========================
    public function edit(SopTemplate $template)
    {
        $this->authorizeManage();
        return view('sop_templates.edit', compact('template'));
    }

    public function update(Request $request, SopTemplate $template)
    {
        $this->authorizeManage();

        $data = $this->validatePayload($request, $template);
        $data['updated_by'] = auth()->id();

        $template->update($data);

        return back()->with('success','Template SOP berhasil diperbarui.');
    }

    // ==========================
    // DELETE
    // ==========================
    public function destroy(SopTemplate $template)
    {
        $this->authorizeManage();

        // optional: blok hapus kalau template dipakai SOP
        if ($template->sops()->exists()) {
            return back()->with('error','Template masih dipakai SOP, tidak boleh dihapus.');
        }

        $template->delete();
        return redirect()->route('sop.templates.index')
            ->with('success','Template SOP berhasil dihapus.');
    }

    // ==========================
    // API kecil buat load template ke create SOP
    // ==========================
    public function showJson(SopTemplate $template)
    {
        $this->authorizeManage();

        return response()->json([
            'id'             => $template->id,
            'name'           => $template->name,
            'department'     => $template->department,
            'product'        => $template->product,
            'line'           => $template->line,
            'form_schema'    => $template->form_schema ?? [],
            'builder_schema' => $template->builder_schema ?? [],
            'meta'           => $template->meta ?? [],
        ]);
    }

    // ==========================
    // VALIDATION
    // ==========================
    private function validatePayload(Request $request, ?SopTemplate $template=null)
    {
        $rules = [
            'name'        => ['required','string','max:150'],
            'code'        => ['nullable','string','max:50'],
            'department'  => ['nullable','string','max:100'],
            'product'     => ['nullable','string','max:100'],
            'line'        => ['nullable','string','max:100'],

            'form_schema'    => ['nullable','string'],   // JSON string
            'builder_schema' => ['nullable','string'],   // JSON string
            'meta'           => ['nullable','string'],   // JSON string

            'is_active'   => ['nullable','boolean'],
        ];

        // code unique kalau diisi
        if ($request->filled('code')) {
            $rules['code'][] = Rule::unique('sop_templates','code')->ignore($template?->id);
        }

        $validated = $request->validate($rules);

        // decode JSON
        foreach (['form_schema','builder_schema','meta'] as $jsonKey) {
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
        if (!auth()->user()->isRole(['admin','produksi'])) {
            abort(403,'Anda tidak punya akses mengelola SOP Template.');
        }
    }
}
