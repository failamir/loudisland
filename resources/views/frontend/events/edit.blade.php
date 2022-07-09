@extends('layouts.frontend')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    {{ trans('global.edit') }} {{ trans('cruds.event.title_singular') }}
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route("frontend.events.update", [$event->id]) }}" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="form-group">
                            <label for="nama_event">{{ trans('cruds.event.fields.nama_event') }}</label>
                            <input class="form-control" type="text" name="nama_event" id="nama_event" value="{{ old('nama_event', $event->nama_event) }}">
                            @if($errors->has('nama_event'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('nama_event') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.event.fields.nama_event_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="event_code">{{ trans('cruds.event.fields.event_code') }}</label>
                            <input class="form-control" type="text" name="event_code" id="event_code" value="{{ old('event_code', $event->event_code) }}">
                            @if($errors->has('event_code'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('event_code') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.event.fields.event_code_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="harga">{{ trans('cruds.event.fields.harga') }}</label>
                            <input class="form-control" type="text" name="harga" id="harga" value="{{ old('harga', $event->harga) }}">
                            @if($errors->has('harga'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('harga') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.event.fields.harga_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_mulai">{{ trans('cruds.event.fields.tanggal_mulai') }}</label>
                            <input class="form-control datetime" type="text" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai', $event->tanggal_mulai) }}">
                            @if($errors->has('tanggal_mulai'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('tanggal_mulai') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.event.fields.tanggal_mulai_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_selesai">{{ trans('cruds.event.fields.tanggal_selesai') }}</label>
                            <input class="form-control datetime" type="text" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai', $event->tanggal_selesai) }}">
                            @if($errors->has('tanggal_selesai'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('tanggal_selesai') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.event.fields.tanggal_selesai_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-danger" type="submit">
                                {{ trans('global.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection