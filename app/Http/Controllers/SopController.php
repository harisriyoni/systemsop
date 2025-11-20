<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index(Request $request)
    {
        $q = Sop::query()->orderByDesc('created_at');

        if ($request->filled('department')) {
            $q->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        $sops = $q->paginate(10)->withQueryString();

        return view('sop.index', compact('sops'));
    }

    public function create()
    {
        return view('sop.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'          => ['required', 'string', 'max:50', 'unique:sops,code'],
            'title'         => ['required', 'string', 'max:255'],
            'department'    => ['required', 'string', 'max:100'],
            'product'       => ['nullable', 'string', 'max:100'],
            'line'          => ['nullable', 'string', 'max:100'],
            'content'       => ['nullable', 'string'],
            'effective_from' => ['nullable', 'date'],
            'effective_to'  => ['nullable', 'date'],
        ]);

        $data['status'] = 'waiting_approval';
        $data['created_by'] = auth()->id();

        Sop::create($data);

        return redirect()->route('sop.index')->with('success', 'SOP berhasil dibuat.');
    }

    public function approvalList(Request $request)
    {
        $userRole = auth()->user()->role;

        $q = Sop::query()->where('status', 'waiting_approval');

        if ($userRole === 'produksi') {
            $q->where('is_approved_produksi', false);
        } elseif ($userRole === 'qa') {
            $q->where('is_approved_qa', false);
        } elseif ($userRole === 'logistik') {
            $q->where('is_approved_logistik', false);
        }

        $sops = $q->orderBy('created_at', 'desc')->paginate(10);

        return view('sop.approval', compact('sops', 'userRole'));
    }

    public function approve(Request $request, Sop $sop)
    {
        $userRole = auth()->user()->role;

        if ($sop->status !== 'waiting_approval') {
            return back()->with('error', 'SOP ini tidak dalam status waiting approval.');
        }

        if ($userRole === 'produksi') {
            $sop->is_approved_produksi = true;
        } elseif ($userRole === 'qa') {
            $sop->is_approved_qa = true;
        } elseif ($userRole === 'logistik') {
            $sop->is_approved_logistik = true;
        } elseif ($userRole === 'admin') {
            // Admin langsung approve semua departemen
            $sop->is_approved_produksi = true;
            $sop->is_approved_qa = true;
            $sop->is_approved_logistik = true;
        }

        // jika semua sudah approve → status approved
        if (
            $sop->is_approved_produksi &&
            $sop->is_approved_qa &&
            $sop->is_approved_logistik
        ) {
            $sop->status = 'approved';
        }

        $sop->save();

        return back()->with('success', 'Approval tersimpan.');
    }


    public function show(Sop $sop)
    {
        return view('sop.show', compact('sop'));
    }
}
