<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyTiketRequest;
use App\Http\Requests\StoreTiketRequest;
use App\Http\Requests\UpdateTiketRequest;
use App\Models\Tiket;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class TiketController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('tiket_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $tikets = Tiket::with(['peserta', 'media'])->get();

        $users = User::get();

        return view('admin.tikets.index', compact('tikets', 'users'));
    }

    public function create()
    {
        abort_if(Gate::denies('tiket_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $pesertas = User::pluck('email', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.tikets.create', compact('pesertas'));
    }

    public function store(StoreTiketRequest $request)
    {
        $tiket = Tiket::create($request->all());

        if ($request->input('qr', false)) {
            $tiket->addMedia(storage_path('tmp/uploads/' . basename($request->input('qr'))))->toMediaCollection('qr');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $tiket->id]);
        }

        return redirect()->route('admin.tikets.index');
    }

    public function edit(Tiket $tiket)
    {
        abort_if(Gate::denies('tiket_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $pesertas = User::pluck('email', 'id')->prepend(trans('global.pleaseSelect'), '');

        $tiket->load('peserta');

        return view('admin.tikets.edit', compact('pesertas', 'tiket'));
    }

    public function update(UpdateTiketRequest $request, Tiket $tiket)
    {
        $tiket->update($request->all());

        if ($request->input('qr', false)) {
            if (!$tiket->qr || $request->input('qr') !== $tiket->qr->file_name) {
                if ($tiket->qr) {
                    $tiket->qr->delete();
                }
                $tiket->addMedia(storage_path('tmp/uploads/' . basename($request->input('qr'))))->toMediaCollection('qr');
            }
        } elseif ($tiket->qr) {
            $tiket->qr->delete();
        }

        return redirect()->route('admin.tikets.index');
    }

    public function show(Tiket $tiket)
    {
        abort_if(Gate::denies('tiket_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $tiket->load('peserta', 'tiketTransaksis');

        return view('admin.tikets.show', compact('tiket'));
    }

    public function destroy(Tiket $tiket)
    {
        abort_if(Gate::denies('tiket_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $tiket->delete();

        return back();
    }

    public function massDestroy(MassDestroyTiketRequest $request)
    {
        Tiket::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('tiket_create') && Gate::denies('tiket_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Tiket();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
