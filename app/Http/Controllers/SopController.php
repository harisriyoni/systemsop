<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SopController extends Controller
{
    public function index(Request $request)
    {
        $query = Sop::query()->latest();

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

    public function create()
    {
        return view('sop.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => ['required', 'string', 'max:50', 'unique:sops,code'],
            'title'          => ['required', 'string', 'max:255'],
            'department'     => ['required', 'string', 'max:100'],
            'product'        => ['nullable', 'string', 'max:100'],
            'line'           => ['nullable', 'string', 'max:100'],
            'content'        => ['nullable', 'string'],
            'effective_from' => ['nullable', 'date'],
            'effective_to'   => ['nullable', 'date', 'after_or_equal:effective_from'],

            // FOTO MULTI (ARRAY)
            'photos'         => ['nullable', 'array', 'max:10'],
            'photos.*'       => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'photo_desc'     => ['nullable', 'array'],
            'photo_desc.*'   => ['nullable', 'string', 'max:255'],

            // AKSES
            'pin'            => ['nullable', 'string', 'max:20'],
            'is_public'      => ['nullable', 'boolean'],
        ], [
            'effective_to.after_or_equal' => 'Tanggal berlaku sampai harus setelah/sama dengan tanggal berlaku mulai.',
            'photos.max' => 'Maksimal 10 foto per SOP.',
            'photos.*.image' => 'File foto harus berupa gambar.',
            'photos.*.max' => 'Ukuran foto maksimal 4MB.',
        ]);

        $data['status']     = 'waiting_approval';
        $data['created_by'] = auth()->id();

        $data['is_approved_produksi'] = false;
        $data['is_approved_qa']       = false;
        $data['is_approved_logistik'] = false;

        $data['is_public'] = $request->boolean('is_public');

        // HANDLE FOTO ARRAY + DESKRIPSI
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

        $data['photos'] = count($photosPayload) ? $photosPayload : null;

        Sop::create($data);

        return redirect()
            ->route('sop.index')
            ->with('success', 'SOP berhasil dibuat.');
    }

    public function approvalList(Request $request)
    {
        $userRole = auth()->user()->role;

        $query = Sop::query()
            ->where('status', 'waiting_approval')
            ->latest();

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
            }
            if ($userRole === 'qa' && !$sop->is_approved_qa) {
                $sop->is_approved_qa = true;
            }
            if ($userRole === 'logistik' && !$sop->is_approved_logistik) {
                $sop->is_approved_logistik = true;
            }
        }

        if (
            $sop->is_approved_produksi &&
            $sop->is_approved_qa &&
            $sop->is_approved_logistik
        ) {
            $sop->status = 'approved';
        }

        $sop->save();

        return back()->with('success', 'Persetujuan berhasil disimpan.');
    }

    // ==========================
    // INTERNAL SHOW (LOGIN)
    // ==========================
    public function show(Sop $sop)
    {
        // QR URL untuk blade show (internal)
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
        // syarat publik + sudah approved
        if (!$sop->is_public || $sop->status !== 'approved') {
            abort(404);
        }

        $qrUrl = route('sop.public.show', $sop);

        // kalau ada PIN, cek session unlock
        $locked = false;
        if ($sop->pin) {
            $sessionKey = "sop_unlocked_{$sop->id}";
            $locked = !$request->session()->get($sessionKey, false);
        }

        // render view yang sama, tapi bawa flag locked
        return view('sop.show', compact('sop','qrUrl','locked'));
    }

    // ==========================
    // POST UNLOCK PIN (PUBLIC)
    // ==========================
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
}
