import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';

const API_URL = import.meta.env.VITE_APP_API_URL || 'https://mandalikakorprirun.com/api/v1';

export default function TransactionDetailPage() {
  const { id } = useParams();
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let ignore = false;
    setLoading(true);
    axios
      .get(`${API_URL}/transactions/${id}`)
      .then((res) => {
        if (ignore) return;
        setData(res.data?.data || res.data);
      })
      .catch((e) => setError(e?.response?.data?.message || 'Gagal memuat detail transaksi'))
      .finally(() => setLoading(false));
    return () => {
      ignore = true;
    };
  }, [id]);

  if (loading) return <div className="p-6">Loading...</div>;
  if (error) return <div className="p-6 text-red-600">{error}</div>;
  if (!data) return <div className="p-6">Data tidak ditemukan.</div>;

  return (
    <div className="p-6 space-y-2">
      <h1 className="text-2xl font-semibold">Detail Transaksi #{data?.id}</h1>
      <div><b>Invoice:</b> {data?.invoice || '-'}</div>
      <div><b>Status:</b> {data?.status || '-'}</div>
      <div><b>Jumlah:</b> {data?.amount || '-'}</div>
      <div><b>Tipe Pembayaran:</b> {data?.payment_type || '-'}</div>
      <div><b>Dibuat:</b> {data?.created_at || '-'}</div>
      <div><b>Peserta:</b> {data?.peserta?.name || data?.created_by?.name || '-'}</div>
    </div>
  );
}
