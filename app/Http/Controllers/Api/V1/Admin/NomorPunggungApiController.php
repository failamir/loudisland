<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NomorPunggungApiController extends Controller
{
    // List nomor punggung with QR URLs
    public function index(Request $request)
    {
        $start = 1;
        $end = 20000;
        $result = [];
        $baseUrl = url('/qrcodes');
        for ($i = $start; $i <= $end; $i++) {
            $nomor = str_pad($i, 5, '0', STR_PAD_LEFT);
            $qrPath = public_path("qrcodes/{$nomor}.png");
            if (!file_exists($qrPath)) {
                // Generate QR code if not exists
                QrCode::format('png')->size(300)->generate($nomor, $qrPath);
            }
            $result[] = [
                'nomor_punggung' => $nomor,
                'qr_url' => "$baseUrl/{$nomor}.png",
            ];
        }
        return response()->json($result);
    }

    // Pair nomor punggung with pendaftar (by nomor)
    public function pair(Request $request)
    {
        $request->validate([
            'nomor_punggung' => 'required|string',
            'pendaftar_id' => 'required|integer',
        ]);
        $nomor = $request->input('nomor_punggung');
        $pendaftarId = $request->input('pendaftar_id');
        // Save pairing in database (assume Pendaftar model has 'nomor_punggung' field)
        $pendaftar = \App\Models\Pendaftar::findOrFail($pendaftarId);
        $pendaftar->nomor_punggung = $nomor;
        $pendaftar->save();
        return response()->json(['success' => true, 'message' => 'Paired successfully.']);
    }
}
