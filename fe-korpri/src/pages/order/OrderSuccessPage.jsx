import React from 'react';
import { useLocation, Link } from 'react-router-dom';

function useQuery() {
  const { search } = useLocation();
  return React.useMemo(() => new URLSearchParams(search), [search]);
}

const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

export default function OrderSuccessPage() {
  const query = useQuery();
  const invoice = query.get('invoice');

  return (
    <div className="p-6">
      <h1 className="text-2xl font-semibold mb-2">Pembayaran Berhasil</h1>
      <p className="mb-4">Terima kasih. Pesanan kamu sudah diterima.</p>
      {invoice && (
        <div className="mb-6">
          <div className="text-sm text-gray-500">Invoice</div>
          <div className="font-mono text-lg">{invoice}</div>
          <div className="mt-2">
            <a className="text-blue-600 underline" href={`${API_URL}/payment/${invoice}`} target="_blank" rel="noreferrer">
              Lihat status pembayaran (JSON)
            </a>
          </div>
        </div>
      )}
      <Link className="text-blue-600 underline" to="/transactions">Kembali ke daftar transaksi</Link>
    </div>
  );
}
