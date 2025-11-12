@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="card-header">Import Pembelian Offline (CSV)</div>
  <div class="card-body">
    @if(session('message'))
      <div class="alert alert-info">{{ session('message') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <p class="mb-2">
      Unduh contoh CSV: <a href="{{ url('/samples/offline_purchase_import_sample.csv') }}" target="_blank">offline_purchase_import_sample.csv</a>
    </p>
    <p class="mb-3 text-muted">
      Header wajib (urutannya harus sama):
      <code>invoice,user_uid,ticket_id,participant_name,participant_email,participant_phone,participant_nik,participant_province,participant_city,shirt_size,amount,status_racepack</code>
    </p>

    <form method="POST" action="{{ route('admin.offline_import.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label for="csv_file">File CSV</label>
        <input id="csv_file" name="csv_file" type="file" class="form-control-file" required>
      </div>
      <button type="submit" class="btn btn-primary">Import</button>
      <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">Kembali</a>
    </form>

    <hr/>
    <div class="text-muted small">
      Catatan:
      <ul>
        <li>Nilai shirt_size yang valid: XS, S, M, L, XL, XXL, XXXL (boleh kosong).</li>
        <li>Semua baris dengan invoice yang sama akan digabung menjadi satu transaksi sukses bertipe offline.</li>
        <li>Peserta akan otomatis dibuat dari setiap baris.</li>
      </ul>
    </div>
  </div>
</div>
@endsection
