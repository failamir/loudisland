@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.sponsor.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.sponsors.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.sponsor.fields.id') }}
                        </th>
                        <td>
                            {{ $sponsor->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.sponsor.fields.nama') }}
                        </th>
                        <td>
                            {{ $sponsor->nama }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.sponsor.fields.image') }}
                        </th>
                        <td>
                            @if($sponsor->image)
                                <a href="{{ $sponsor->image->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $sponsor->image->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.sponsor.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\Sponsor::STATUS_SELECT[$sponsor->status] ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.sponsors.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection