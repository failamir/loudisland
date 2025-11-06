import React, { useEffect, useMemo, useState } from 'react';
import { getMine, updateMine, getTransactions } from '@/api/referral';
import * as authHelper from '@/auth/_helpers';

const number = (v) => new Intl.NumberFormat('id-ID').format(Number(v || 0));

const ReferralDashboard = () => {
  const auth = useMemo(() => authHelper.getAuth(), []);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [data, setData] = useState(null);
  const [saving, setSaving] = useState(false);

  // editable metadata
  const [fullName, setFullName] = useState('');
  const [bank, setBank] = useState('');
  const [accountNumber, setAccountNumber] = useState('');
  const [active, setActive] = useState(true);

  const fetchMine = async () => {
    setLoading(true);
    setError(null);
    const res = await getMine({ perPage: 50 });
    if (!res.ok) {
      setError(res.data?.message || 'Failed to load');
      setData(null);
    } else {
      setData(res.data?.data || null);
      const meta = (res.data?.data?.metadata) || {};
      setFullName(meta.full_name || '');
      setBank(meta.bank || '');
      setAccountNumber(meta.account_number || '');
      setActive(Boolean(res.data?.data?.active));
    }
    setLoading(false);
  };

  useEffect(() => { fetchMine(); }, []);

  const onSave = async () => {
    setSaving(true);
    const res = await updateMine({
      full_name: fullName || null,
      bank: bank || null,
      account_number: accountNumber || null,
      active,
    });
    setSaving(false);
    if (!res.ok) {
      alert(res.data?.message || 'Gagal menyimpan');
      return;
    }
    fetchMine();
  };

  if (!auth?.access_token) {
    return (
      <div className="container mx-auto p-4">
        <div className="text-sm text-gray-700">Silakan login terlebih dahulu.</div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="container mx-auto p-4">
        <div className="text-sm text-gray-600">Loading...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto p-4 space-y-4">
        <div className="text-red-700 text-sm">{error}</div>
        <a href="/referral/register" className="inline-block text-blue-600 hover:underline text-sm">Daftar Referral</a>
      </div>
    );
  }

  if (!data) return null;

  const meta = data.metadata || {};
  const uses = Array.isArray(data.uses) ? data.uses : [];

  return (
    <div className="container mx-auto p-4 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Referral Dashboard</h1>
        <a href="/referral/register" className="text-sm text-blue-600 hover:underline">Daftar Referral</a>
      </div>

      <div className="grid sm:grid-cols-2 gap-6">
        <div className="border rounded p-4 space-y-3">
          <div className="text-sm text-gray-600">Kode Referral</div>
          <div className="flex items-center gap-2">
            <code className="px-2 py-1 bg-gray-100 rounded text-sm">{data.code}</code>
            <button
              className="text-blue-600 text-sm hover:underline"
              onClick={async () => {
                try { await navigator.clipboard.writeText(data.code); } catch(_) {}
              }}
            >Copy</button>
          </div>
          <div className="grid grid-cols-2 gap-3 text-sm pt-2">
            <div>
              <div className="text-gray-600">Status</div>
              <div className="font-medium">{data.active ? 'Aktif' : 'Nonaktif'}</div>
            </div>
            <div>
              <div className="text-gray-600">Balance</div>
              <div className="font-medium">Rp {number(data.balance)}</div>
            </div>
            <div>
              <div className="text-gray-600">Dipakai</div>
              <div className="font-medium">{data.used_count ?? 0}{data.usage_limit ? ` / ${data.usage_limit}` : ''}</div>
            </div>
            <div>
              <div className="text-gray-600">Valid</div>
              <div className="font-medium">{data.valid_from || '-'} s/d {data.valid_to || '-'}</div>
            </div>
          </div>
        </div>

        <div className="border rounded p-4 space-y-3">
          <div className="text-sm text-gray-600">Profil & Pencairan</div>
          <div className="grid gap-3">
            <label className="flex flex-col gap-1 text-sm">
              <span className="text-gray-600">Nama Lengkap</span>
              <input className="border rounded px-3 py-2" value={fullName} onChange={e=>setFullName(e.target.value)} />
            </label>
            <label className="flex flex-col gap-1 text-sm">
              <span className="text-gray-600">Bank</span>
              <input className="border rounded px-3 py-2" value={bank} onChange={e=>setBank(e.target.value)} />
            </label>
            <label className="flex flex-col gap-1 text-sm">
              <span className="text-gray-600">No. Rekening</span>
              <input className="border rounded px-3 py-2" value={accountNumber} onChange={e=>setAccountNumber(e.target.value)} />
            </label>
            <label className="flex items-center gap-2 text-sm">
              <input type="checkbox" checked={active} onChange={e=>setActive(e.target.checked)} />
              <span className="text-gray-600">Aktifkan kode referral</span>
            </label>
            <div>
              <button onClick={onSave} disabled={saving} className="bg-blue-600 text-white rounded px-4 py-2 text-sm">
                {saving ? 'Menyimpan...' : 'Simpan'}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div className="space-y-2">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-medium">Riwayat Pemakaian (latest)</h2>
          <a href="/referral" className="text-xs text-gray-600">per_page=50</a>
        </div>
        <div className="overflow-x-auto border rounded">
          <table className="min-w-full text-sm">
            <thead className="bg-gray-50">
              <tr>
                <th className="text-left px-3 py-2">Tanggal</th>
                <th className="text-left px-3 py-2">Email Pemesan</th>
                <th className="text-left px-3 py-2">Komisi (Rp)</th>
              </tr>
            </thead>
            <tbody>
              {uses.length === 0 && (
                <tr><td className="px-3 py-3 text-gray-600" colSpan={3}>Belum ada pemakaian.</td></tr>
              )}
              {uses.map((u, idx) => (
                <tr key={idx} className="border-t">
                  <td className="px-3 py-2">{u.tanggal || '-'}</td>
                  <td className="px-3 py-2">{u.email_pemesan || '-'}</td>
                  <td className="px-3 py-2">{number(u.value)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default ReferralDashboard;
