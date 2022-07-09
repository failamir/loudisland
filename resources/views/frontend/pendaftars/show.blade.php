@extends('layouts.frontend')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    {{ trans('global.show') }} {{ trans('cruds.pendaftar.title') }}
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <div class="form-group">
                            <a class="btn btn-default" href="{{ route('frontend.pendaftars.index') }}">
                                {{ trans('global.back_to_list') }}
                            </a>
                        </div>
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.id') }}
                                    </th>
                                    <td>
                                        {{ $pendaftar->id }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.no_tiket') }}
                                    </th>
                                    <td>
                                        {{ $pendaftar->no_tiket }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.nama') }}
                                    </th>
                                    <td>
                                        {{ $pendaftar->nama }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.nik') }}
                                    </th>
                                    <td>
                                        {{ $pendaftar->nik }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.email') }}
                                    </th>
                                    <td>
                                        {{ $pendaftar->email }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.no_hp') }}
                                    </th>
                                    <td>
                                        {{ $pendaftar->no_hp }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.checkin') }}
                                    </th>
                                    <td>
                                        {{ App\Models\Pendaftar::CHECKIN_SELECT[$pendaftar->checkin] ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.payment') }}
                                    </th>
                                    <td>
                                        {{ App\Models\Pendaftar::PAYMENT_SELECT[$pendaftar->payment] ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.notes') }}
                                    </th>
                                    <td>
                                        {!! $pendaftar->notes !!}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.status_payment') }}
                                    </th>
                                    <td>
                                        {{ App\Models\Pendaftar::STATUS_PAYMENT_SELECT[$pendaftar->status_payment] ?? '' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="form-group">
                            <a class="btn btn-default" href="{{ route('frontend.pendaftars.index') }}">
                                {{ trans('global.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection