@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.transaksi.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.transaksis.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.transaksi.fields.id') }}
                        </th>
                        <td>
                            {{ $transaksi->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.transaksi.fields.invoice') }}
                        </th>
                        <td>
                            {{ $transaksi->invoice }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.transaksi.fields.event') }}
                        </th>
                        <td>
                            {{ $transaksi->event->nama_event ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.transaksi.fields.tiket') }}
                        </th>
                        <td>
                            {{ $transaksi->tiket->no_tiket ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.transaksi.fields.peserta') }}
                        </th>
                        <td>
                            {{ $transaksi->peserta->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.transaksi.fields.amount') }}
                        </th>
                        <td>
                            {{ $transaksi->amount }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.transaksi.fields.note') }}
                        </th>
                        <td>
                            {!! $transaksi->note !!}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.transaksi.fields.snap_token') }}
                        </th>
                        <td>
                            {{ $transaksi->snap_token }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.transaksi.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\Transaksi::STATUS_SELECT[$transaksi->status] ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.transaksis.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection