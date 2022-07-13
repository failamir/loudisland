@extends('layouts.frontend')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    {{ trans('global.create') }} {{ trans('cruds.pendaftar.title_singular') }}
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route("frontend.pendaftars.store") }}" enctype="multipart/form-data">
                        @method('POST')
                        @csrf
                        <div class="form-group">
                            <label for="no_tiket">{{ trans('cruds.pendaftar.fields.no_tiket') }}</label>
                            <input class="form-control" type="text" name="no_tiket" id="no_tiket" value="{{ old('no_tiket', '') }}">
                            @if($errors->has('no_tiket'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('no_tiket') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.no_tiket_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label class="required" for="nama">{{ trans('cruds.pendaftar.fields.nama') }}</label>
                            <input class="form-control" type="text" name="nama" id="nama" value="{{ old('nama', '') }}" required>
                            @if($errors->has('nama'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('nama') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.nama_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label class="required" for="nik">{{ trans('cruds.pendaftar.fields.nik') }}</label>
                            <input class="form-control" type="text" name="nik" id="nik" value="{{ old('nik', '') }}" required>
                            @if($errors->has('nik'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('nik') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.nik_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label class="required" for="email">{{ trans('cruds.pendaftar.fields.email') }}</label>
                            <input class="form-control" type="text" name="email" id="email" value="{{ old('email', '') }}" required>
                            @if($errors->has('email'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.email_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label class="required" for="no_hp">{{ trans('cruds.pendaftar.fields.no_hp') }}</label>
                            <input class="form-control" type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', '') }}" required>
                            @if($errors->has('no_hp'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('no_hp') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.no_hp_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label>{{ trans('cruds.pendaftar.fields.checkin') }}</label>
                            <select class="form-control" name="checkin" id="checkin">
                                <option value disabled {{ old('checkin', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                                @foreach(App\Models\Pendaftar::CHECKIN_SELECT as $key => $label)
                                    <option value="{{ $key }}" {{ old('checkin', '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('checkin'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('checkin') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.checkin_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="notes">{{ trans('cruds.pendaftar.fields.notes') }}</label>
                            <textarea class="form-control ckeditor" name="notes" id="notes">{!! old('notes') !!}</textarea>
                            @if($errors->has('notes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('notes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.notes_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label>{{ trans('cruds.pendaftar.fields.status_payment') }}</label>
                            <select class="form-control" name="status_payment" id="status_payment">
                                <option value disabled {{ old('status_payment', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                                @foreach(App\Models\Pendaftar::STATUS_PAYMENT_SELECT as $key => $label)
                                    <option value="{{ $key }}" {{ old('status_payment', '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('status_payment'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('status_payment') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.status_payment_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="event_id">{{ trans('cruds.pendaftar.fields.event') }}</label>
                            <select class="form-control select2" name="event_id" id="event_id">
                                @foreach($events as $id => $entry)
                                    <option value="{{ $id }}" {{ old('event_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('event'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('event') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.event_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label>{{ trans('cruds.pendaftar.fields.payment_type') }}</label>
                            <select class="form-control" name="payment_type" id="payment_type">
                                <option value disabled {{ old('payment_type', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                                @foreach(App\Models\Pendaftar::PAYMENT_TYPE_SELECT as $key => $label)
                                    <option value="{{ $key }}" {{ old('payment_type', '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('payment_type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('payment_type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.payment_type_helper') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="total_bayar">{{ trans('cruds.pendaftar.fields.total_bayar') }}</label>
                            <input class="form-control" type="text" name="total_bayar" id="total_bayar" value="{{ old('total_bayar', '') }}">
                            @if($errors->has('total_bayar'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('total_bayar') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.total_bayar_helper') }}</span>
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

@section('scripts')
<script>
    $(document).ready(function () {
  function SimpleUploadAdapter(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = function(loader) {
      return {
        upload: function() {
          return loader.file
            .then(function (file) {
              return new Promise(function(resolve, reject) {
                // Init request
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route('frontend.pendaftars.storeCKEditorImages') }}', true);
                xhr.setRequestHeader('x-csrf-token', window._token);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.responseType = 'json';

                // Init listeners
                var genericErrorText = `Couldn't upload file: ${ file.name }.`;
                xhr.addEventListener('error', function() { reject(genericErrorText) });
                xhr.addEventListener('abort', function() { reject() });
                xhr.addEventListener('load', function() {
                  var response = xhr.response;

                  if (!response || xhr.status !== 201) {
                    return reject(response && response.message ? `${genericErrorText}\n${xhr.status} ${response.message}` : `${genericErrorText}\n ${xhr.status} ${xhr.statusText}`);
                  }

                  $('form').append('<input type="hidden" name="ck-media[]" value="' + response.id + '">');

                  resolve({ default: response.url });
                });

                if (xhr.upload) {
                  xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                      loader.uploadTotal = e.total;
                      loader.uploaded = e.loaded;
                    }
                  });
                }

                // Send request
                var data = new FormData();
                data.append('upload', file);
                data.append('crud_id', '{{ $pendaftar->id ?? 0 }}');
                xhr.send(data);
              });
            })
        }
      };
    }
  }

  var allEditors = document.querySelectorAll('.ckeditor');
  for (var i = 0; i < allEditors.length; ++i) {
    ClassicEditor.create(
      allEditors[i], {
        extraPlugins: [SimpleUploadAdapter]
      }
    );
  }
});
</script>

@endsection