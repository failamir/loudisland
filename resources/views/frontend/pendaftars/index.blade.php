@extends('layouts.frontend')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @can('pendaftar_create')
                <div style="margin-bottom: 10px;" class="row">
                    <div class="col-lg-12">
                        <a class="btn btn-success" href="{{ route('frontend.pendaftars.create') }}">
                            {{ trans('global.add') }} {{ trans('cruds.pendaftar.title_singular') }}
                        </a>
                        <button class="btn btn-warning" data-toggle="modal" data-target="#csvImportModal">
                            {{ trans('global.app_csvImport') }}
                        </button>
                        @include('csvImport.modal', ['model' => 'Pendaftar', 'route' => 'admin.pendaftars.parseCsvImport'])
                    </div>
                </div>
            @endcan
            <div class="card">
                <div class="card-header">
                    {{ trans('cruds.pendaftar.title_singular') }} {{ trans('global.list') }}
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class=" table table-bordered table-striped table-hover datatable datatable-Pendaftar">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.id') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.no_tiket') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.nama') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.nik') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.email') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.no_hp') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.checkin') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.status_payment') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.event') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.event.fields.event_code') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.payment_type') }}
                                    </th>
                                    <th>
                                        {{ trans('cruds.pendaftar.fields.total_bayar') }}
                                    </th>
                                    <th>
                                        &nbsp;
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                    </td>
                                    <td>
                                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                                    </td>
                                    <td>
                                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                                    </td>
                                    <td>
                                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                                    </td>
                                    <td>
                                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                                    </td>
                                    <td>
                                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                                    </td>
                                    <td>
                                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                                    </td>
                                    <td>
                                        <select class="search" strict="true">
                                            <option value>{{ trans('global.all') }}</option>
                                            @foreach(App\Models\Pendaftar::CHECKIN_SELECT as $key => $item)
                                                <option value="{{ $item }}">{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="search" strict="true">
                                            <option value>{{ trans('global.all') }}</option>
                                            @foreach(App\Models\Pendaftar::STATUS_PAYMENT_SELECT as $key => $item)
                                                <option value="{{ $item }}">{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="search">
                                            <option value>{{ trans('global.all') }}</option>
                                            @foreach($events as $key => $item)
                                                <option value="{{ $item->nama_event }}">{{ $item->nama_event }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                    </td>
                                    <td>
                                        <select class="search" strict="true">
                                            <option value>{{ trans('global.all') }}</option>
                                            @foreach(App\Models\Pendaftar::PAYMENT_TYPE_SELECT as $key => $item)
                                                <option value="{{ $item }}">{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendaftars as $key => $pendaftar)
                                    <tr data-entry-id="{{ $pendaftar->id }}">
                                        <td>
                                            {{ $pendaftar->id ?? '' }}
                                        </td>
                                        <td>
                                            {{ $pendaftar->no_tiket ?? '' }}
                                        </td>
                                        <td>
                                            {{ $pendaftar->nama ?? '' }}
                                        </td>
                                        <td>
                                            {{ $pendaftar->nik ?? '' }}
                                        </td>
                                        <td>
                                            {{ $pendaftar->email ?? '' }}
                                        </td>
                                        <td>
                                            {{ $pendaftar->no_hp ?? '' }}
                                        </td>
                                        <td>
                                            {{ App\Models\Pendaftar::CHECKIN_SELECT[$pendaftar->checkin] ?? '' }}
                                        </td>
                                        <td>
                                            {{ App\Models\Pendaftar::STATUS_PAYMENT_SELECT[$pendaftar->status_payment] ?? '' }}
                                        </td>
                                        <td>
                                            {{ $pendaftar->event->nama_event ?? '' }}
                                        </td>
                                        <td>
                                            {{ $pendaftar->event->event_code ?? '' }}
                                        </td>
                                        <td>
                                            {{ App\Models\Pendaftar::PAYMENT_TYPE_SELECT[$pendaftar->payment_type] ?? '' }}
                                        </td>
                                        <td>
                                            {{ $pendaftar->total_bayar ?? '' }}
                                        </td>
                                        <td>
                                            @can('pendaftar_show')
                                                <a class="btn btn-xs btn-primary" href="{{ route('frontend.pendaftars.show', $pendaftar->id) }}">
                                                    {{ trans('global.view') }}
                                                </a>
                                            @endcan

                                            @can('pendaftar_edit')
                                                <a class="btn btn-xs btn-info" href="{{ route('frontend.pendaftars.edit', $pendaftar->id) }}">
                                                    {{ trans('global.edit') }}
                                                </a>
                                            @endcan

                                            @can('pendaftar_delete')
                                                <form action="{{ route('frontend.pendaftars.destroy', $pendaftar->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                                </form>
                                            @endcan

                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('pendaftar_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('frontend.pendaftars.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 1, 'desc' ]],
    pageLength: 50,
  });
  let table = $('.datatable-Pendaftar:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
let visibleColumnsIndexes = null;
$('.datatable thead').on('input', '.search', function () {
      let strict = $(this).attr('strict') || false
      let value = strict && this.value ? "^" + this.value + "$" : this.value

      let index = $(this).parent().index()
      if (visibleColumnsIndexes !== null) {
        index = visibleColumnsIndexes[index]
      }

      table
        .column(index)
        .search(value, strict)
        .draw()
  });
table.on('column-visibility.dt', function(e, settings, column, state) {
      visibleColumnsIndexes = []
      table.columns(":visible").every(function(colIdx) {
          visibleColumnsIndexes.push(colIdx);
      });
  })
})

</script>
@endsection