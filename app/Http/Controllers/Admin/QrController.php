<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class QrController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('pendaftar_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
        // sort by name (numeric friendly)
        usort($items, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        return view('admin.qrcodes.index', ['items' => $items]);
    }

    public function downloadAll()
    {
        abort_if(Gate::denies('pendaftar_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $dir = public_path('qrcodes');
        if (!is_dir($dir)) {
            return back()->with('error', 'Folder QR belum ada.');
        }

        $zipName = 'qrcodes-all.zip';
        $zipPath = storage_path('app/' . $zipName);

        // recreate zip
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return back()->with('error', 'Gagal membuat ZIP.');
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.png');
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
