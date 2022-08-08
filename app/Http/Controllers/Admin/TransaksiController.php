<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyTransaksiRequest;
use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use App\Models\Event;
use App\Models\Tiket;
use App\Models\Transaksi;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class TransaksiController extends Controller
{
    use MediaUploadingTrait;
    use CsvImportTrait;

    public function index()
    {
        abort_if(Gate::denies('transaksi_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transaksis = Transaksi::with(['event', 'tiket', 'peserta', 'created_by'])->get();

        return view('admin.transaksis.index', compact('transaksis'));
    }

    public function create()
    {
        abort_if(Gate::denies('transaksi_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::pluck('nama_event', 'id')->prepend(trans('global.pleaseSelect'), '');

        $tikets = Tiket::pluck('no_tiket', 'id')->prepend(trans('global.pleaseSelect'), '');

        $pesertas = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.transaksis.create', compact('events', 'pesertas', 'tikets'));
    }

    public function store(StoreTransaksiRequest $request)
    {
        $transaksi = Transaksi::create($request->all());

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $transaksi->id]);
        }

        return redirect()->route('admin.transaksis.index');
    }

    public function edit(Transaksi $transaksi)
    {
        abort_if(Gate::denies('transaksi_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::pluck('nama_event', 'id')->prepend(trans('global.pleaseSelect'), '');

        $tikets = Tiket::pluck('no_tiket', 'id')->prepend(trans('global.pleaseSelect'), '');

        $pesertas = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $transaksi->load('event', 'tiket', 'peserta', 'created_by');

        return view('admin.transaksis.edit', compact('events', 'pesertas', 'tikets', 'transaksi'));
    }

    public function update(UpdateTransaksiRequest $request, Transaksi $transaksi)
    {
        $transaksi->update($request->all());

        return redirect()->route('admin.transaksis.index');
    }

    public function show(Transaksi $transaksi)
    {
        abort_if(Gate::denies('transaksi_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transaksi->load('event', 'tiket', 'peserta', 'created_by');

        return view('admin.transaksis.show', compact('transaksi'));
    }

    public function destroy(Transaksi $transaksi)
    {
        abort_if(Gate::denies('transaksi_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transaksi->delete();

        return back();
    }

    public function massDestroy(MassDestroyTransaksiRequest $request)
    {
        Transaksi::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('transaksi_create') && Gate::denies('transaksi_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Transaksi();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
