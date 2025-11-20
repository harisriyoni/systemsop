<?php

namespace App\Http\Controllers;

use App\Models\CheckSheet;
use App\Models\Sop;

class QrCenterController extends Controller
{
    public function index()
    {
        $sops = Sop::where('status','approved')->get();
        $forms = CheckSheet::where('status','active')->get();

        return view('qr_center.index', compact('sops','forms'));
    }
}
