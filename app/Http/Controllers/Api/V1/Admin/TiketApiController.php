<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreTiketRequest;
use App\Http\Requests\UpdateTiketRequest;
use App\Http\Resources\Admin\TiketResource;
use App\Models\Tiket;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TiketApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('tiket_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new TiketResource(Tiket::with(['peserta'])->get());
    }

    public function store(StoreTiketRequest $request)
    {
        $tiket = Tiket::create($request->all());

        if ($request->input('qr', false)) {
            $tiket->addMedia(storage_path('tmp/uploads/' . basename($request->input('qr'))))->toMediaCollection('qr');
        }

        return (new TiketResource($tiket))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Tiket $tiket)
    {
        abort_if(Gate::denies('tiket_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new TiketResource($tiket->load(['peserta']));
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

        return (new TiketResource($tiket))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Tiket $tiket)
    {
        abort_if(Gate::denies('tiket_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $tiket->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
