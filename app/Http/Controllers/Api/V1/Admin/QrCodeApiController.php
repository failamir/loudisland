<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class QrCodeApiController extends Controller
{
    // GET /api/v1/qrcodes?per_page=50&page=1
    public function index(Request $request)
    {
        $dir = public_path('qrcodes');
        $items = [];
        if (is_dir($dir)) {
            $files = glob($dir . DIRECTORY_SEPARATOR . '*.png');
            foreach ($files as $file) {
                $basename = basename($file);
                $items[] = [
                    'name' => $basename,
                    'url' => url('qrcodes/' . $basename),
                ];
            }
        }
        // natural sort
        usort($items, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        $perPage = max(1, (int)($request->query('per_page', 50)));
        $page = max(1, (int)($request->query('page', 1)));
        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $data = array_slice($items, $offset, $perPage);

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

    // GET /api/v1/qrcodes/download-all
    public function downloadAll()
    {
        $dir = public_path('qrcodes');
        if (!is_dir($dir)) {
            return response()->json(['message' => 'Folder QR belum ada.'], Response::HTTP_NOT_FOUND);
        }

        $zipName = 'qrcodes-all.zip';
        $zipPath = storage_path('app/' . $zipName);

        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return response()->json(['message' => 'Gagal membuat ZIP.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.png');
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
