@extends('layouts.frontend')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    Isi Data Diri
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route("bayar") }}" enctype="multipart/form-data">
                        @method('POST')
                        @csrf
                        <div class="form-group">
                            <label for="no_tiket">{{ trans('cruds.pendaftar.fields.no_tiket') }}</label>
                            <input class="form-control {{ $errors->has('no_tiket') ? 'is-invalid' : '' }}" type="text" name="no_tiket" id="no_tiket" value="{{ 0 . $no_t->no_tiket+1 }}" disabled>
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
                        {{-- <div class="form-group">
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
                        </div> --}}
                        {{-- <div class="form-group">
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
                        </div> --}}
                        <div class="form-group">
                            <label for="total_bayar">{{ trans('cruds.pendaftar.fields.total_bayar') }}</label>
                            
                            {{-- @dump($data) --}}
                            <p>
                                _________________________________________________
                            </p>

                            @if($data['day_1'] > 0)
                            {{ $data['day_1'] }} Ticket Day 1  -->  Rp. {{ $data['price_1'] }}<br>
                            <input type="hidden" name="day_1" value="{{ $data['day_1'] }}">
                            <br>
                            @endif

                            @if($data['day_2'] > 0)
                            {{ $data['day_2'] }} Ticket Day 2  -->  Rp. {{ $data['price_2'] }}<br>
                            <input type="hidden" name="day_2" value="{{ $data['day_2'] }}">
                            <br>
                            @endif

                            @if($data['day_3'] > 0)
                            {{ $data['day_3'] }} Ticket Day 1 & 2  --> Rp. {{ $data['price_3'] }}<br>
                            <input type="hidden" name="day_3" value="{{ $data['day_3'] }}">
                            <br>
                            @endif

                            {{-- @for($i=0;$i<$data['day_1'];$i++)
                            Tiket day_1
                            <br>
                            @endfor
                        
                            @for($i=0;$i<$data['day_2'];$i++)
                            Tiket day_2
                            <br>
                            @endfor

                            @for($i=0;$i<$data['day_3'];$i++)
                            Tiket day_3
                            <br>
                            @endfor --}}


                            <input class="form-control" type="hidden" name="total_bayar" id="total_bayar" value="{{ old('total_bayar', '') }}">
                            @if($errors->has('total_bayar'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('total_bayar') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.pendaftar.fields.total_bayar_helper') }}</span>
                        </div>
                        <div class="form-group text-center ">
                            <button class="btn btn-danger" type="submit">
                                {{-- {{ trans('global.save') }} --}}
                                Bayar Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
