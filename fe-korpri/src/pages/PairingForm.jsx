import React, { useEffect, useState, useRef } from 'react';
import axios from 'axios';

const PairingForm = ({ nomorPunggung, onSuccess, onUnpaired, initialPaired = false, initialPendaftarId = null }) => {
  const [pendaftarId, setPendaftarId] = useState(initialPendaftarId ? String(initialPendaftarId) : '');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [paired, setPaired] = useState(!!initialPaired);
  const inputRef = useRef();
  const baseUrl = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  // keep local state in sync when the selected nomor changes or props update
  useEffect(() => {
    setPaired(!!initialPaired);
    setPendaftarId(initialPendaftarId ? String(initialPendaftarId) : '');
  }, [nomorPunggung, initialPaired, initialPendaftarId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      await axios.post(`${baseUrl}/nomor-punggung/pair`, {
        nomor_punggung: nomorPunggung,
        pendaftar_id: pendaftarId,
      });
      setPaired(true);
      // hide entered id
      setPendaftarId('');
      if (onSuccess) onSuccess();
    } catch (err) {
      setError(err?.response?.data?.message || 'Pairing failed');
    } finally {
      setLoading(false);
      if (!paired) inputRef.current?.focus();
    }
  };

  const handleUnpair = async () => {
    setLoading(true);
    setError('');
    try {
      await axios.post(`${baseUrl}/nomor-punggung/unpair`, {
        nomor_punggung: nomorPunggung,
      });
      setPaired(false);
      if (onUnpaired) onUnpaired();
      // focus back to input for next pairing
      setTimeout(() => inputRef.current?.focus(), 0);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unpair failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} style={{marginTop: 8}}>
      {!paired && (
        <>
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
        </>
      )}
      {paired && (
        <button type="button" onClick={handleUnpair} disabled={loading} style={{padding: 6}}>
          {loading ? 'Unpairing...' : 'Unpair'}
        </button>
      )}
      {error && <div style={{color: 'red', fontSize: 12}}>{error}</div>}
    </form>
  );
};

export default PairingForm;
