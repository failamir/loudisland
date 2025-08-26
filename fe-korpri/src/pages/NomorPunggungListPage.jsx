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

  useEffect(() => {
    axios.get('/api/v1/nomor-punggung')
      .then(res => {
        setData(res.data);
        setFiltered(res.data);
      })
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    if (search.trim() === '') setFiltered(data);
    else setFiltered(
      data.filter(item => item.nomor_punggung.includes(search.trim()))
    );
  }, [search, data]);

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
      <input
        type="text"
        placeholder="Cari nomor..."
        value={search}
        onChange={e => setSearch(e.target.value)}
        style={{marginBottom: 16, padding: 8, width: 200}}
      />
      <div style={{display: 'flex', flexWrap: 'wrap', gap: 16}}>
        {filtered.slice(0, 100).map(item => (
          <div key={item.nomor_punggung} style={{border: '1px solid #ddd', padding: 12, borderRadius: 8, minWidth: 180}}>
            <div style={{fontWeight: 'bold', fontSize: 18}}>{item.nomor_punggung}</div>
            <img src={item.qr_url} alt={item.nomor_punggung} style={{width: 120, height: 120}} />
            <PairingForm nomorPunggung={item.nomor_punggung} />
          </div>
        ))}
      </div>
      <div style={{marginTop: 16}}>
        <small>Menampilkan {filtered.length > 100 ? 100 : filtered.length} dari {filtered.length} nomor.</small>
      </div>
    </div>
  );
};

export default NomorPunggungListPage;
