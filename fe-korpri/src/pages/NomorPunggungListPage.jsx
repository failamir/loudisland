import React, { useEffect, useState } from 'react';
import axios from 'axios';
import PairingForm from './PairingForm';
let QrReader;
try {
  QrReader = require('react-qr-reader').default;
} catch {}

const NomorPunggungListPage = () => {
  const [data, setData] = useState([]);
  const [search, setSearch] = useState('');
  const [filtered, setFiltered] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showScanner, setShowScanner] = useState(false);
  const [scannedNomor, setScannedNomor] = useState('');
  const [pairSuccess, setPairSuccess] = useState(false);
  // Generate controls
  const [genStart, setGenStart] = useState(1);
  const [genEnd, setGenEnd] = useState(10000);
  const [genSize, setGenSize] = useState(300);
  const [genLoading, setGenLoading] = useState(false);
  const [genMsg, setGenMsg] = useState('');
  const baseUrl = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';
  // Pagination
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(10);
  const [total, setTotal] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const handleGenerate = async () => {
    setGenMsg('');
    if (!Number.isInteger(+genStart) || !Number.isInteger(+genEnd) || +genStart < 1 || +genEnd < +genStart) {
      setGenMsg('Range tidak valid. Pastikan start >= 1 dan end >= start.');
      return;
    }
    setGenLoading(true);
    try {
      const payload = { start: +genStart, end: +genEnd, size: +genSize };
      const res = await axios.post(`${baseUrl}/nomor-punggung/generate`, payload);
      const created = res?.data?.created ?? 0;
      const skipped = res?.data?.skipped ?? 0;
      setGenMsg(`Selesai. Dibuat: ${created}, dilewati: ${skipped}.`);
      await fetchList();
    } catch (e) {
      setGenMsg(e?.response?.data?.message || 'Gagal generate.');
    } finally {
      setGenLoading(false);
    }
  };
  const fetchList = async (opts = {}) => {
    try {
      setLoading(true);
      const currentPage = opts.page ?? page;
      const currentPerPage = opts.per_page ?? perPage;
      const qParam = Object.prototype.hasOwnProperty.call(opts, 'q') ? opts.q : (search.trim() || undefined);
      const res = await axios.get(`${baseUrl}/nomor-punggung`, { params: { per_page: currentPerPage, page: currentPage, q: qParam } });
      // API returns shape: { data: [...], pagination: {...} }
      const items = Array.isArray(res.data) ? res.data : (res.data && Array.isArray(res.data.data) ? res.data.data : []);
      setData(items);
      setFiltered(items);
      const p = res?.data?.pagination;
      if (p) {
        setTotal(p.total ?? 0);
        setLastPage(p.last_page ?? 1);
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchList();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    fetchList({ page, per_page: perPage });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [page, perPage]);

  useEffect(() => {
    // server-side search: reset to first page and fetch with q
    setPage(1);
    fetchList({ page: 1, per_page: perPage, q: search.trim() || undefined });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search]);

  if (loading) return <div>Loading...</div>;

  return (
    <div style={{padding: 24}}>
      <h2>Daftar Nomor Punggung & QR</h2>
      <button onClick={() => setShowScanner(s => !s)} style={{marginBottom: 16}}>
        {showScanner ? 'Tutup QR Scanner' : 'Scan QR untuk Pairing'}
      </button>
      {showScanner && QrReader && (
        <div style={{marginBottom: 16}}>
          <QrReader
            delay={300}
            onError={console.error}
            onScan={data => {
              if (data) {
                setScannedNomor(data);
                setShowScanner(false);
              }
            }}
            style={{ width: 240 }}
          />
        </div>
      )}
      {scannedNomor && (
        <div style={{marginBottom: 16, background: '#f8f8f8', padding: 12, borderRadius: 8}}>
          <b>Nomor hasil scan:</b> {scannedNomor}
          <PairingForm nomorPunggung={scannedNomor} onSuccess={() => {setPairSuccess(true); setTimeout(()=>{setPairSuccess(false); setScannedNomor('');}, 2000);}} />
          {pairSuccess && <div style={{color: 'green'}}>Pairing berhasil!</div>}
        </div>
      )}
      <div style={{display: 'flex', alignItems: 'center', gap: 8, marginBottom: 12, flexWrap: 'wrap'}}>
        <label>
          Start:
          <input type="number" value={genStart} onChange={e => setGenStart(e.target.value)} min={1} style={{marginLeft: 6, padding: 6, width: 110}} />
        </label>
        <label>
          End:
          <input type="number" value={genEnd} onChange={e => setGenEnd(e.target.value)} min={1} style={{marginLeft: 6, padding: 6, width: 110}} />
        </label>
        <label>
          Size:
          <input type="number" value={genSize} onChange={e => setGenSize(e.target.value)} min={64} max={1024} style={{marginLeft: 6, padding: 6, width: 110}} />
        </label>
        <button
          onClick={handleGenerate}
          disabled={genLoading}
          className="btn btn-info"
          style={{padding: '8px 12px'}}
        >
          {genLoading ? 'Generating...' : 'Generate QR'}
        </button>
        {genMsg && <span style={{marginLeft: 8, fontSize: 12}}>{genMsg}</span>}
      </div>
      <input
        type="text"
        placeholder="Cari nomor..."
        value={search}
        onChange={e => setSearch(e.target.value)}
        style={{marginBottom: 16, padding: 8, width: 200}}
      />
      <table style={{width: '100%', borderCollapse: 'collapse'}}>
        <thead>
          <tr>
            <th style={{textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px'}}>No</th>
            <th style={{textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px'}}>Nomor Punggung</th>
            <th style={{textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px'}}>QR</th>
            <th style={{textAlign: 'left', borderBottom: '1px solid #ddd', padding: '8px'}}>Pairing</th>
          </tr>
        </thead>
        <tbody>
          {filtered.map((item, idx) => (
            <tr key={item.nomor_punggung}>
              <td style={{borderBottom: '1px solid #f0f0f0', padding: '8px'}}>{(page - 1) * perPage + idx + 1}</td>
              <td style={{borderBottom: '1px solid #f0f0f0', padding: '8px', fontWeight: 'bold'}}>{item.nomor_punggung}</td>
              <td style={{borderBottom: '1px solid #f0f0f0', padding: '8px'}}>
                <img src={item.qr_url} alt={item.nomor_punggung} style={{width: 100, height: 100, objectFit: 'contain'}} />
              </td>
              <td style={{borderBottom: '1px solid #f0f0f0', padding: '8px'}}>
                <PairingForm
                  nomorPunggung={item.nomor_punggung}
                  initialPaired={!!item.paired}
                  initialPendaftarId={item.pendaftar_id}
                  onSuccess={() => fetchList({ page, per_page: perPage, q: search.trim() || undefined })}
                  onUnpaired={() => fetchList({ page, per_page: perPage, q: search.trim() || undefined })}
                />
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <div style={{display: 'flex', alignItems: 'center', gap: 12, marginTop: 12, flexWrap: 'wrap'}}>
        <button onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page <= 1}>Prev</button>
        <span>Page {page} / {lastPage}</span>
        <button onClick={() => setPage(p => Math.min(lastPage, p + 1))} disabled={page >= lastPage}>Next</button>
        <span>Total: {total}</span>
        <label>
          Per page:
          <select value={perPage} onChange={e => { setPage(1); setPerPage(parseInt(e.target.value, 10)); }} style={{marginLeft: 6}}>
            <option value={10}>10</option>
            <option value={50}>50</option>
            <option value={100}>100</option>
            <option value={200}>200</option>
          </select>
        </label>
      </div>
    </div>
  );
};

export default NomorPunggungListPage;

