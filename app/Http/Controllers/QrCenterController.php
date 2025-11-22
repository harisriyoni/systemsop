<?php

namespace App\Http\Controllers;

use App\Models\CheckSheet;
use App\Models\Sop;
use Illuminate\Http\Request;

class QrCenterController extends Controller
{
    public function index(Request $request)
    {
        $q          = trim($request->input('q', ''));
        $department = trim($request->input('department', ''));
        $product    = trim($request->input('product', ''));
        $line       = trim($request->input('line', ''));
        $type       = $request->input('type'); // sop / checksheet / all

        // =========================
        // SOP APPROVED + FILTERS
        // =========================
        $sopQuery = Sop::query()->where('status', 'approved')->latest();

        if ($q) {
            $sopQuery->where(function($s) use ($q) {
                $s->where('title','like',"%{$q}%")
                  ->orWhere('code','like',"%{$q}%");
            });
        }

        if ($department) {
            $sopQuery->where('department','like',"%{$department}%");
        }

        if ($product) {
            $sopQuery->where('product','like',"%{$product}%");
        }

        if ($line) {
            $sopQuery->where('line','like',"%{$line}%");
        }

        // ambil SOP kalau type bukan khusus checksheet
        $sops = ($type === 'checksheet')
            ? collect()
            : $sopQuery->get()->map(function($sop){
                // fallback QR jika belum ada kolom qr_url
                if (empty($sop->qr_url)) {
                    $sop->qr_url = route('sop.public.show', $sop);
                }
                $sop->qr_type = 'sop';
                return $sop;
            });

        // =========================
        // CHECK SHEET ACTIVE + FILTERS
        // =========================
        $csQuery = CheckSheet::query()->where('status', 'active')->latest();

        if ($q) {
            $csQuery->where('title','like',"%{$q}%");
        }

        if ($department) {
            $csQuery->where('department','like',"%{$department}%");
        }

        if ($product) {
            $csQuery->where('product','like',"%{$product}%");
        }

        if ($line) {
            $csQuery->where('line','like',"%{$line}%");
        }

        // ambil CS kalau type bukan khusus sop
        $forms = ($type === 'sop')
            ? collect()
            : $csQuery->get()->map(function($form){
                // fallback QR jika belum ada kolom qr_url
                if (empty($form->qr_url)) {
                    $form->qr_url = route('check_sheets.fill', $form);
                }
                $form->qr_type = 'checksheet';
                return $form;
            });

        // =========================
        // OPTIONAL: UNIFIED LIST
        // =========================
        // Kalau view kamu mau 1 tabel gabungan
        $items = $sops->merge($forms)->sortByDesc('updated_at')->values();

        return view('qr_center.index', [
            'sops'  => $sops,
            'forms' => $forms,
            'items' => $items, // opsional dipakai kalau kamu mau 1 list

            'filters' => [
                'q'          => $q,
                'department' => $department,
                'product'    => $product,
                'line'       => $line,
                'type'       => $type,
            ]
        ]);
    }
}
