<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NomorPunggungApiController extends Controller
{
    // List nomor punggung with QR URLs (no generation here)
    public function index(Request $request)
    {
        $dir = public_path('qrcodes');
        $items = [];
        if (is_dir($dir)) {
            $files = glob($dir . DIRECTORY_SEPARATOR . '*.png');
            foreach ($files as $file) {
                $name = basename($file); // e.g., 00001.png
                $nomor = pathinfo($name, PATHINFO_FILENAME);
                $items[] = [
                    'nomor_punggung' => $nomor,
                    'qr_url' => url('/qrcodes/' . $name),
                ];
            }
        }
        // natural sort by nomor
        usort($items, function ($a, $b) {
            return strnatcasecmp($a['nomor_punggung'], $b['nomor_punggung']);
        });

        // optional query filter by nomor
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $items = array_values(array_filter($items, function ($row) use ($q) {
                return stripos($row['nomor_punggung'], $q) !== false;
            }));
        }

        $perPage = max(1, (int)($request->query('per_page', 10)));
        $page = max(1, (int)($request->query('page', 1)));
        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $data = array_slice($items, $offset, $perPage);

        // enrich with pairing info (batched lookup)
        if (!empty($data)) {
            $nomors = array_map(function ($row) {
                return $row['nomor_punggung'];
            }, $data);
            $pairs = \App\Models\Pendaftar::whereIn('nomor_punggung', $nomors)
                ->get(['id', 'nomor_punggung', 'paired_at'])
                ->keyBy('nomor_punggung');
            foreach ($data as &$row) {
                $p = $pairs->get($row['nomor_punggung']);
                $row['paired'] = (bool) $p;
                $row['pendaftar_id'] = $p ? $p->id : null;
                $row['paired_at'] = $p ? optional($p->paired_at)->toDateTimeString() : null;
            }
            unset($row);
        }

        return response()->json([
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    // Generate QR codes for a range of nomor punggung
    public function generate(Request $request)
    {
        $request->validate([
            'start' => 'required|integer|min:1',
            'end' => 'required|integer|gte:start',
            'size' => 'nullable|integer|min:64|max:1024',
        ]);
        $start = (int)$request->input('start');
        $end = (int)$request->input('end');
        $size = (int)$request->input('size', 300);

        // Force GD driver so it doesn't require Imagick
        // Supports both common config keys depending on package version
        config([
            'simple-qrcode.image_driver' => 'gd',
            'qr-code.writer' => 'gd',
        ]);

        $dir = public_path('qrcodes');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $created = 0;
        $skipped = 0;
        for ($i = $start; $i <= $end; $i++) {
            $nomor = str_pad($i, 5, '0', STR_PAD_LEFT);
            $qrPath = $dir . DIRECTORY_SEPARATOR . $nomor . '.png';
            if (file_exists($qrPath)) {
                $skipped++;
                continue;
            }
            QrCode::format('png')->size($size)->generate($nomor, $qrPath);
            $created++;
        }

        return response()->json([
            'start' => $start,
            'end' => $end,
            'created' => $created,
            'skipped' => $skipped,
        ]);
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
        // check if nomor punggung already pair
        $existing = \App\Models\Pendaftar::where('nomor_punggung', $nomor)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Nomor punggung already paired.']);
        }
        // Save pairing in database (assume Pendaftar model has 'nomor_punggung' field)
        $pendaftar = \App\Models\Pendaftar::findOrFail($pendaftarId);
        $pendaftar->nomor_punggung = $nomor;
        $pendaftar->paired_at = now();
        $pendaftar->save();
        return response()->json(['success' => true, 'message' => 'Paired successfully.', 'paired_at' => $pendaftar->paired_at?->toDateTimeString()]);
    }

    // Unpair nomor punggung from a pendaftar
    public function unpair(Request $request)
    {
        $request->validate([
            'nomor_punggung' => 'required|string',
        ]);

        $nomor = $request->input('nomor_punggung');
        $pendaftar = \App\Models\Pendaftar::where('nomor_punggung', $nomor)->first();
        if (!$pendaftar) {
            return response()->json(['success' => false, 'message' => 'Pairing not found.']);
        }
        $pendaftar->nomor_punggung = null;
        $pendaftar->paired_at = null;
        $pendaftar->save();
        return response()->json(['success' => true, 'message' => 'Unpaired successfully.']);
    }
}
