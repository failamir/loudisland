import React, { useState } from 'react';
import axios from 'axios';
import { Container } from '@/components/container';
import { Toolbar, ToolbarHeading, ToolbarPageTitle } from '@/partials/toolbar';
import { useAuthContext } from '@/auth';

const ImportOfflinePage = () => {
  const { auth } = useAuthContext();
  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  const [file, setFile] = useState(null);
  const [ticketId, setTicketId] = useState(1); // ASN default
  const [province, setProvince] = useState('DKI Jakarta');
  const [city, setCity] = useState('Jakarta Pusat');
  const [invoice, setInvoice] = useState('');
  const [userUid, setUserUid] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [sendNotifs, setSendNotifs] = useState(true);
  const [message, setMessage] = useState('');
  const [errors, setErrors] = useState([]);

  const onSubmit = async (e) => {
    e.preventDefault();
    setMessage('');
    setErrors([]);
    if (!file) return;
    try {
      setSubmitting(true);
      const form = new FormData();
      form.append('file', file);
      if (ticketId) form.append('ticket_id', String(ticketId));
      if (province) form.append('province', province);
      if (city) form.append('city', city);
      if (invoice) form.append('invoice', invoice);
      if (userUid) form.append('user_uid', userUid);
      if (sendNotifs) form.append('send_notifications', '1');
      const headers = auth?.access_token ? { Authorization: `Bearer ${auth.access_token}` } : {};
      const res = await axios.post(`${API_URL}/offline-import`, form, { headers });
      setMessage(res?.data?.message || 'Import selesai');
      if (Array.isArray(res?.data?.errors)) setErrors(res.data.errors);
    } catch (err) {
      const msg = err?.response?.data?.message || 'Gagal import';
      const errs = err?.response?.data?.errors || [];
      setMessage(msg);
      setErrors(Array.isArray(errs) ? errs : []);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <>
      <Container>
        <Toolbar>
          <ToolbarHeading>
            <ToolbarPageTitle />
          </ToolbarHeading>
        </Toolbar>
      </Container>

      <Container>
        <div className="card p-5">
          <h2 className="text-lg font-semibold mb-3">Import Pembelian Offline (CSV)</h2>
          <p className="text-sm text-gray-600 mb-2">
            Unduh contoh CSV:&nbsp;
            <a className="text-primary underline" href="/samples/offline_purchase_import_sample.csv" target="_blank" rel="noreferrer">
              offline_purchase_import_sample.csv
            </a>
          </p>
          <p className="text-xs text-gray-500 mb-4">
            Header wajib (urutannya harus sama): invoice,user_uid,ticket_id,participant_name,participant_email,participant_phone,participant_nik,participant_province,participant_city,shirt_size,amount,status_racepack
          </p>

          <form onSubmit={onSubmit}>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
              <label className="flex flex-col gap-1">
                <span className="text-sm text-gray-700">Ticket ID (wajib untuk format minimal)</span>
                <input type="number" value={ticketId} onChange={(e) => setTicketId(Number(e.target.value))} className="input" />
              </label>
              <label className="flex flex-col gap-1">
                <span className="text-sm text-gray-700">Invoice (opsional)</span>
                <input type="text" value={invoice} onChange={(e) => setInvoice(e.target.value)} className="input" placeholder="Kosongkan untuk auto" />
              </label>
              <label className="flex flex-col gap-1">
                <span className="text-sm text-gray-700">User UID (opsional)</span>
                <input type="text" value={userUid} onChange={(e) => setUserUid(e.target.value)} className="input" placeholder="Kosongkan untuk auto dari email" />
              </label>
              <label className="flex flex-col gap-1">
                <span className="text-sm text-gray-700">Provinsi (opsional)</span>
                <input type="text" value={province} onChange={(e) => setProvince(e.target.value)} className="input" />
              </label>
              <label className="flex flex-col gap-1">
                <span className="text-sm text-gray-700">Kota/Kab (opsional)</span>
                <input type="text" value={city} onChange={(e) => setCity(e.target.value)} className="input" />
              </label>
            </div>
            <input
              type="file"
              accept=".csv,text/csv,.txt"
              onChange={(e) => setFile(e.target.files?.[0] || null)}
              className="block mb-3"
            />
            <div className="flex items-center gap-2">
              <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" checked={sendNotifs} onChange={(e) => setSendNotifs(e.target.checked)} />
                Kirim WA & Email setelah import
              </label>
              <button type="submit" className="btn btn-primary" disabled={submitting || !file}>
                {submitting ? 'Mengunggah...' : 'Import'}
              </button>
              <a className="btn btn-light" href="/transactions">Kembali</a>
            </div>
          </form>

          {message && (
            <div className="mt-4">
              <div className="alert alert-info">{message}</div>
              {errors?.length > 0 && (
                <ul className="list-disc list-inside text-sm text-red-600 mt-2">
                  {errors.map((e, i) => (
                    <li key={i}>{e}</li>
                  ))}
                </ul>
              )}
            </div>
          )}
        </div>
      </Container>
    </>
  );
};

export default ImportOfflinePage;
