import React, { useState, useRef } from 'react';
import axios from 'axios';

const PairingForm = ({ nomorPunggung, onSuccess }) => {
  const [pendaftarId, setPendaftarId] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const inputRef = useRef();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      await axios.post('/api/v1/nomor-punggung/pair', {
        nomor_punggung: nomorPunggung,
        pendaftar_id: pendaftarId,
      });
      setPendaftarId('');
      if (onSuccess) onSuccess();
    } catch (err) {
      setError(err?.response?.data?.message || 'Pairing failed');
    } finally {
      setLoading(false);
      inputRef.current?.focus();
    }
  };

  return (
    <form onSubmit={handleSubmit} style={{marginTop: 8}}>
      <input
        ref={inputRef}
        type="text"
        placeholder="ID Pendaftar"
        value={pendaftarId}
        onChange={e => setPendaftarId(e.target.value)}
        required
        style={{padding: 6, width: 120, marginRight: 8}}
      />
      <button type="submit" disabled={loading} style={{padding: 6}}>
        {loading ? 'Pairing...' : 'Pair'}
      </button>
      {error && <div style={{color: 'red', fontSize: 12}}>{error}</div>}
    </form>
  );
};

export default PairingForm;
