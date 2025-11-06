import React, { useMemo, useState } from 'react';
import { registerReferral } from '@/api/referral';
import * as authHelper from '@/auth/_helpers';

const toLaravelDateTime = (value) => {
  if (!value) return null;
  return value.includes('T') ? `${value}:00`.replace('T', ' ') : value;
};

const ReferralRegister = () => {
  const auth = useMemo(() => authHelper.getAuth(), []);
  const [code, setCode] = useState('');
  const [usageLimit, setUsageLimit] = useState('');
  const [validFrom, setValidFrom] = useState('');
  const [validTo, setValidTo] = useState('');
  const [fullName, setFullName] = useState('');
  const [bank, setBank] = useState('');
  const [accountNumber, setAccountNumber] = useState('');
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  if (!auth?.access_token) {
    return (
      <div className="container mx-auto p-4">
        <div className="text-sm text-gray-700">Silakan login terlebih dahulu.</div>
      </div>
    );
  }

  const onSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError(null);
    const payload = {
      code: code || null,
      usage_limit: usageLimit === '' ? null : Math.max(1, Number(usageLimit) || 1),
      valid_from: toLaravelDateTime(validFrom),
      valid_to: toLaravelDateTime(validTo),
      full_name: fullName || null,
      bank: bank || null,
      account_number: accountNumber || null,
    };
    const res = await registerReferral(payload);
    setSaving(false);
    if (!res.ok) {
      setError(res.data?.message || 'Gagal mendaftar');
      return;
    }
    // success -> go to dashboard
    window.location.assign('/referral');
  };

  return (
    <div className="container mx-auto p-4 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Daftar Referral</h1>
        <a href="/referral" className="text-sm text-blue-600 hover:underline">Ke Dashboard</a>
      </div>

      <form onSubmit={onSubmit} className="grid sm:grid-cols-2 gap-6">
        <div className="space-y-3">
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-gray-600">Kode (opsional)</span>
            <input className="border rounded px-3 py-2" value={code} onChange={e=>setCode(e.target.value)} placeholder="REF-XXXXXXXX" />
            <span className="text-xs text-gray-500">Biarkan kosong untuk auto-generate.</span>
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-gray-600">Usage Limit (opsional)</span>
            <input type="number" min={1} className="border rounded px-3 py-2" value={usageLimit} onChange={e=>setUsageLimit(e.target.value)} placeholder="mis. 100" />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-gray-600">Valid From (opsional)</span>
            <input type="datetime-local" className="border rounded px-3 py-2" value={validFrom} onChange={e=>setValidFrom(e.target.value)} />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="text-gray-600">Valid To (opsional)</span>
            <input type="datetime-local" className="border rounded px-3 py-2" value={validTo} onChange={e=>setValidTo(e.target.value)} />
          </label>
        </div>

        <div className="space-y-3">
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

          {error && <div className="text-sm text-red-700">{error}</div>}

          <div className="pt-1">
            <button type="submit" disabled={saving} className="bg-blue-600 text-white rounded px-4 py-2 text-sm">
              {saving ? 'Mendaftar...' : 'Daftar'}
            </button>
          </div>
        </div>
      </form>

      <p className="text-xs text-gray-500">Catatan: Kode akan nonaktif dulu. Admin akan melakukan approval/aktivasi.</p>
    </div>
  );
};

export default ReferralRegister;
