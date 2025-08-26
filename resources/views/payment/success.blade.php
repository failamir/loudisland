<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment Success</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <style>
    body { background:#f8fafc; }
    .card { max-width: 640px; margin: 40px auto; }
    .qr { width: 240px; height: 240px; object-fit: contain; }
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="card shadow-sm">
      <div class="card-body text-center">
        <h3 class="mb-3">Pembayaran Berhasil</h3>
        <p class="text-muted">Terima kasih telah mendaftar Mandalika Run 2025.</p>
        @if($qr_url)
          <img src="{{ $qr_url }}" alt="QR Ticket" class="qr mb-3" />
        @else
          <div class="alert alert-warning">QR belum tersedia.</div>
        @endif
        <div class="text-start mx-auto" style="max-width:420px;">
          <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between"><span>Invoice</span><strong>{{ $trx->invoice ?? '-' }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>No Tiket</span><strong>{{ $no_tiket ?? '-' }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Nama</span><strong>{{ $pendaftar->nama ?? '-' }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Email</span><strong>{{ $pendaftar->email ?? '-' }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Status</span><strong class="text-success">{{ $pendaftar->status_payment ?? 'success' }}</strong></li>
          </ul>
        </div>
        <a class="btn btn-primary" href="/">Kembali ke Beranda</a>
      </div>
    </div>
  </div>
</body>
</html>
