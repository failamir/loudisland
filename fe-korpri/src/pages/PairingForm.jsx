import React, { useEffect, useState, useRef } from 'react';
import axios from 'axios';

const PairingForm = ({ nomorPunggung, onSuccess, onUnpaired, initialPaired = false, initialPendaftarId = null }) => {
  // accept either numeric transaction_id or alphanumeric invoice code
  const [trxOrInvoice, setTrxOrInvoice] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [paired, setPaired] = useState(!!initialPaired);
  const inputRef = useRef();
  const baseUrl = import.meta.env.VITE_APP_API_URL || 'https://mandalikakorprirun.com/api/v1';

  // keep local state in sync when the selected nomor changes or props update
  useEffect(() => {
    setPaired(!!initialPaired);
    // clear input when nomor changes
    setTrxOrInvoice('');
  }, [nomorPunggung, initialPaired, initialPendaftarId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const trimmed = String(trxOrInvoice).trim();
      if (!trimmed) {
        setError('Mohon isi ID transaksi atau invoice');
        return;
      }
      const payload = { nomor_punggung: nomorPunggung };
      if (/^\d+$/.test(trimmed)) {
        payload.transaction_id = parseInt(trimmed, 10);
      } else {
        payload.invoice = trimmed;
      }
      await axios.post(`${baseUrl}/nomor-punggung/pair`, payload, { headers: { 'Content-Type': 'application/json' } });
      setPaired(true);
      // hide entered id
      setTrxOrInvoice('');
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
    <form onSubmit={handleSubmit} style={{ marginTop: 8 }}>
      {!paired && (
        <>
          <input
            ref={inputRef}
            type="text"
            placeholder="ID Transaksi atau Invoice (mis. 12345 atau TRX-XXXX)"
            value={trxOrInvoice}
            onChange={e => setTrxOrInvoice(e.target.value)}
            required
            style={{ padding: 6, width: 120, marginRight: 8 }}
          />
          <button type="submit" disabled={loading} className="btn btn-primary px-3 py-2">
            {loading ? 'Pairing...' : 'Pair'}
          </button>
        </>
      )}
      {paired && (
        <button type="button" onClick={handleUnpair} disabled={loading} className="btn btn-outline px-3 py-2">
          {loading ? 'Unpairing...' : 'Unpair'}
        </button>
      )}
      {error && <div style={{ color: 'red', fontSize: 12 }}>{error}</div>}
    </form>
  );
};

export default PairingForm;
