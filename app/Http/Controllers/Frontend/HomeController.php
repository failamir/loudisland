<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Tiket;
use Illuminate\Support\Facades\Auth;

class HomeController
{
    public function index()
    {
        $tikets = Tiket::where('peserta_id', Auth::id())
            ->latest()
            ->get();

        return view('frontend.home', compact('tikets'));
    }
}
