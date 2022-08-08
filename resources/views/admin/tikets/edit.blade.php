@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.tiket.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.tikets.update", [$tiket->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="no_tiket">{{ trans('cruds.tiket.fields.no_tiket') }}</label>
                <input class="form-control {{ $errors->has('no_tiket') ? 'is-invalid' : '' }}" type="text" name="no_tiket" id="no_tiket" value="{{ old('no_tiket', $tiket->no_tiket) }}">
                @if($errors->has('no_tiket'))
                    <div class="invalid-feedback">
                        {{ $errors->first('no_tiket') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.tiket.fields.no_tiket_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="peserta_id">{{ trans('cruds.tiket.fields.peserta') }}</label>
                <select class="form-control select2 {{ $errors->has('peserta') ? 'is-invalid' : '' }}" name="peserta_id" id="peserta_id">
                    @foreach($pesertas as $id => $entry)
                        <option value="{{ $id }}" {{ (old('peserta_id') ? old('peserta_id') : $tiket->peserta->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('peserta'))
                    <div class="invalid-feedback">
                        {{ $errors->first('peserta') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.tiket.fields.peserta_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.tiket.fields.checkin') }}</label>
                <select class="form-control {{ $errors->has('checkin') ? 'is-invalid' : '' }}" name="checkin" id="checkin">
                    <option value disabled {{ old('checkin', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\Tiket::CHECKIN_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('checkin', $tiket->checkin) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('checkin'))
                    <div class="invalid-feedback">
                        {{ $errors->first('checkin') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.tiket.fields.checkin_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="notes">{{ trans('cruds.tiket.fields.notes') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('notes') ? 'is-invalid' : '' }}" name="notes" id="notes">{!! old('notes', $tiket->notes) !!}</textarea>
                @if($errors->has('notes'))
                    <div class="invalid-feedback">
                        {{ $errors->first('notes') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.tiket.fields.notes_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.tiket.fields.status_payment') }}</label>
                <select class="form-control {{ $errors->has('status_payment') ? 'is-invalid' : '' }}" name="status_payment" id="status_payment">
                    <option value disabled {{ old('status_payment', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\Tiket::STATUS_PAYMENT_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('status_payment', $tiket->status_payment) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('status_payment'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status_payment') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.tiket.fields.status_payment_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.tiket.fields.payment_type') }}</label>
                <select class="form-control {{ $errors->has('payment_type') ? 'is-invalid' : '' }}" name="payment_type" id="payment_type">
                    <option value disabled {{ old('payment_type', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\Tiket::PAYMENT_TYPE_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('payment_type', $tiket->payment_type) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('payment_type'))
                    <div class="invalid-feedback">
                        {{ $errors->first('payment_type') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.tiket.fields.payment_type_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="total_bayar">{{ trans('cruds.tiket.fields.total_bayar') }}</label>
                <input class="form-control {{ $errors->has('total_bayar') ? 'is-invalid' : '' }}" type="text" name="total_bayar" id="total_bayar" value="{{ old('total_bayar', $tiket->total_bayar) }}">
                @if($errors->has('total_bayar'))
                    <div class="invalid-feedback">
                        {{ $errors->first('total_bayar') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.tiket.fields.total_bayar_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="qr">{{ trans('cruds.tiket.fields.qr') }}</label>
                <div class="needsclick dropzone {{ $errors->has('qr') ? 'is-invalid' : '' }}" id="qr-dropzone">
                </div>
                @if($errors->has('qr'))
                    <div class="invalid-feedback">
                        {{ $errors->first('qr') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.tiket.fields.qr_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
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
                xhr.open('POST', '{{ route('admin.tikets.storeCKEditorImages') }}', true);
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
                data.append('crud_id', '{{ $tiket->id ?? 0 }}');
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

<script>
    Dropzone.options.qrDropzone = {
    url: '{{ route('admin.tikets.storeMedia') }}',
    maxFilesize: 2, // MB
    acceptedFiles: '.jpeg,.jpg,.png,.gif',
    maxFiles: 1,
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 2,
      width: 4096,
      height: 4096
    },
    success: function (file, response) {
      $('form').find('input[name="qr"]').remove()
      $('form').append('<input type="hidden" name="qr" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="qr"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($tiket) && $tiket->qr)
      var file = {!! json_encode($tiket->qr) !!}
          this.options.addedfile.call(this, file)
      this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="qr" value="' + file.file_name + '">')
      this.options.maxFiles = this.options.maxFiles - 1
@endif
    },
    error: function (file, response) {
        if ($.type(response) === 'string') {
            var message = response //dropzone sends it's own error messages in string
        } else {
            var message = response.errors.file
        }
        file.previewElement.classList.add('dz-error')
        _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
        _results = []
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i]
            _results.push(node.textContent = message)
        }

        return _results
    }
}

</script>
@endsection