import React, { useState } from 'react';
import axios from 'axios';

const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

export default function NomorPunggungPairPage() {
  const [nomorPunggung, setNomorPunggung] = useState('');
  const [pendaftarId, setPendaftarId] = useState('');
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState(null);

  async function handlePair() {
    setLoading(true);
    setMessage(null);
    try {
      const res = await axios.post(`${API_URL}/nomor-punggung/pair`, {
        nomor_punggung: nomorPunggung,
        pendaftar_id: pendaftarId,
      });
      setMessage(res.data?.message || 'Pairing berhasil');
    } catch (e) {
      setMessage(e?.response?.data?.message || 'Pairing gagal');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="p-6 space-y-3">
      <h1 className="text-2xl font-semibold">Pair Nomor Punggung</h1>
      <div>
        <label className="block text-sm text-gray-500">Nomor Punggung</label>
        <input
          className="border rounded px-2 py-1"
          value={nomorPunggung}
          onChange={(e) => setNomorPunggung(e.target.value)}
          placeholder="mis. 101"
        />
      </div>
      <div>
        <label className="block text-sm text-gray-500">Pendaftar ID</label>
        <input
          className="border rounded px-2 py-1"
          value={pendaftarId}
          onChange={(e) => setPendaftarId(e.target.value)}
          placeholder="mis. 123"
        />
      </div>
      <button
        className="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-60"
        onClick={handlePair}
        disabled={loading}
      >
        {loading ? 'Memproses...' : 'Pair'}
      </button>
      {message && <div className="mt-2">{message}</div>}
    </div>
  );
}
