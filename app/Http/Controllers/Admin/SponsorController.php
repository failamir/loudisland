<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroySponsorRequest;
use App\Http\Requests\StoreSponsorRequest;
use App\Http\Requests\UpdateSponsorRequest;
use App\Models\Sponsor;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class SponsorController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('sponsor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sponsors = Sponsor::with(['media'])->get();

        return view('admin.sponsors.index', compact('sponsors'));
    }

    public function create()
    {
        abort_if(Gate::denies('sponsor_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.sponsors.create');
    }

    public function store(StoreSponsorRequest $request)
    {
        $sponsor = Sponsor::create($request->all());

        if ($request->input('image', false)) {
            $sponsor->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))->toMediaCollection('image');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $sponsor->id]);
        }

        return redirect()->route('admin.sponsors.index');
    }

    public function edit(Sponsor $sponsor)
    {
        abort_if(Gate::denies('sponsor_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.sponsors.edit', compact('sponsor'));
    }

    public function update(UpdateSponsorRequest $request, Sponsor $sponsor)
    {
        $sponsor->update($request->all());

        if ($request->input('image', false)) {
            if (!$sponsor->image || $request->input('image') !== $sponsor->image->file_name) {
                if ($sponsor->image) {
                    $sponsor->image->delete();
                }
                $sponsor->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))->toMediaCollection('image');
            }
        } elseif ($sponsor->image) {
            $sponsor->image->delete();
        }

        return redirect()->route('admin.sponsors.index');
    }

    public function show(Sponsor $sponsor)
    {
        abort_if(Gate::denies('sponsor_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.sponsors.show', compact('sponsor'));
    }

    public function destroy(Sponsor $sponsor)
    {
        abort_if(Gate::denies('sponsor_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sponsor->delete();

        return back();
    }

    public function massDestroy(MassDestroySponsorRequest $request)
    {
        Sponsor::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('sponsor_create') && Gate::denies('sponsor_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Sponsor();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
