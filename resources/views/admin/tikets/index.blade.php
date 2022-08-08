@extends('layouts.admin')
@section('content')
@can('tiket_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.tikets.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.tiket.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.tiket.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Tiket">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.tiket.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.tiket.fields.no_tiket') }}
                        </th>
                        <th>
                            {{ trans('cruds.tiket.fields.peserta') }}
                        </th>
                        <th>
                            {{ trans('cruds.user.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.tiket.fields.checkin') }}
                        </th>
                        <th>
                            {{ trans('cruds.tiket.fields.status_payment') }}
                        </th>
                        <th>
                            {{ trans('cruds.tiket.fields.payment_type') }}
                        </th>
                        <th>
                            {{ trans('cruds.tiket.fields.total_bayar') }}
                        </th>
                        <th>
                            {{ trans('cruds.tiket.fields.qr') }}
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
                            <select class="search">
                                <option value>{{ trans('global.all') }}</option>
                                @foreach($users as $key => $item)
                                    <option value="{{ $item->email }}">{{ $item->email }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                        </td>
                        <td>
                            <select class="search" strict="true">
                                <option value>{{ trans('global.all') }}</option>
                                @foreach(App\Models\Tiket::CHECKIN_SELECT as $key => $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="search" strict="true">
                                <option value>{{ trans('global.all') }}</option>
                                @foreach(App\Models\Tiket::STATUS_PAYMENT_SELECT as $key => $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="search" strict="true">
                                <option value>{{ trans('global.all') }}</option>
                                @foreach(App\Models\Tiket::PAYMENT_TYPE_SELECT as $key => $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                        </td>
                        <td>
                        </td>
                        <td>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tikets as $key => $tiket)
                        <tr data-entry-id="{{ $tiket->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $tiket->id ?? '' }}
                            </td>
                            <td>
                                {{ $tiket->no_tiket ?? '' }}
                            </td>
                            <td>
                                {{ $tiket->peserta->email ?? '' }}
                            </td>
                            <td>
                                {{ $tiket->peserta->name ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\Tiket::CHECKIN_SELECT[$tiket->checkin] ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\Tiket::STATUS_PAYMENT_SELECT[$tiket->status_payment] ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\Tiket::PAYMENT_TYPE_SELECT[$tiket->payment_type] ?? '' }}
                            </td>
                            <td>
                                {{ $tiket->total_bayar ?? '' }}
                            </td>
                            <td>
                                @if($tiket->qr)
                                    <a href="{{ $tiket->qr->getUrl() }}" target="_blank" style="display: inline-block">
                                        <img src="{{ $tiket->qr->getUrl('thumb') }}">
                                    </a>
                                @endif
                            </td>
                            <td>
                                @can('tiket_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.tikets.show', $tiket->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('tiket_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.tikets.edit', $tiket->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('tiket_delete')
                                    <form action="{{ route('admin.tikets.destroy', $tiket->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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



@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('tiket_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.tikets.massDestroy') }}",
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
    pageLength: 100,
  });
  let table = $('.datatable-Tiket:not(.ajaxTable)').DataTable({ buttons: dtButtons })
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