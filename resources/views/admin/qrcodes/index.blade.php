@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>QR Codes</span>
        <div>
            <a class="btn btn-success" href="{{ route('admin.qrcodes.downloadAll') }}">
                Download All (ZIP)
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(empty($items))
            <div class="alert alert-warning">Belum ada QR code di folder public/qrcodes.</div>
        @else
            <div class="row g-3">
                @foreach($items as $it)
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="border rounded p-2 text-center h-100">
                            <div class="mb-2"><img src="{{ $it['url'] }}" alt="{{ $it['name'] }}" style="max-width: 100%; height: 180px; object-fit: contain;"/></div>
                            <div class="small text-truncate" title="{{ $it['name'] }}">{{ $it['name'] }}</div>
                            <div class="mt-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ $it['url'] }}" download>Download</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
