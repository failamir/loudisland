import React, { useEffect, useMemo, useState } from 'react';
import { isValidCodeFormat, normalizeCode, parseCodeType } from '@/utils';
import * as authHelper from '@/auth/_helpers';

const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

const ReferralCodesPage = () => {
  const auth = useMemo(() => authHelper.getAuth(), []);
  const headers = useMemo(() => ({
    'Content-Type': 'application/json',
    ...(auth?.access_token ? { Authorization: `Bearer ${auth.access_token}` } : {}),
  }), [auth]);

  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const [check, setCheck] = useState('');
  const [prefix, setPrefix] = useState('REF');
  const normalizedPrefix = useMemo(() => normalizeCode(prefix), [prefix]);

  const fetchMine = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(`${API_URL}/referral-codes`, { headers });
      const data = await res.json();
      setList(Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []));
    } catch (e) {
      setError('Failed to load');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchMine(); }, []);

  const onToggle = async (row) => {
    try {
      const res = await fetch(`${API_URL}/referral-codes/${row.id}`, {
        method: 'PATCH',
        headers,
        body: JSON.stringify({ active: !row.active })
      });
      if (!res.ok) {
        const err = await res.json().catch(()=>({}));
        alert(err.message || 'Gagal mengubah status');
        return;
      }
      fetchMine();
    } catch (e) {
      alert('Gagal mengubah status');
    }
  };

  const checked = normalizeCode(check);
  const isValid = checked ? isValidCodeFormat(checked, { prefix: normalizedPrefix, length: 8 }) : null;
  const type = checked ? parseCodeType(checked) : null;

  return (
    <div className="container mx-auto p-4 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Referral Codes</h1>
        <div className="flex items-center gap-3">
          <a href="/referral" className="text-blue-600 hover:underline text-sm">Dashboard</a>
          <a href="/referral/register" className="text-blue-600 hover:underline text-sm">Register</a>
        </div>
      </div>

      {loading && <div className="text-sm text-gray-600">Loading...</div>}
      {error && <div className="text-sm text-red-700">{error}</div>}

      <div className="overflow-x-auto border rounded">
        <table className="min-w-full text-sm">
          <thead className="bg-gray-50">
            <tr>
              <th className="text-left px-3 py-2">#</th>
              <th className="text-left px-3 py-2">Code</th>
              <th className="text-left px-3 py-2">Usage</th>
              <th className="text-left px-3 py-2">Valid</th>
              <th className="text-left px-3 py-2">Active</th>
              <th className="text-left px-3 py-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            {list.length === 0 && (
              <tr><td className="px-3 py-3 text-gray-600" colSpan={6}>Belum ada referral code.</td></tr>
            )}
            {list.map((row, idx) => (
              <tr key={row.id || idx} className="border-t">
                <td className="px-3 py-2">{idx + 1}</td>
                <td className="px-3 py-2 font-mono">{row.code}</td>
                <td className="px-3 py-2">{row.used_count ?? 0}/{row.usage_limit ?? '-'}</td>
                <td className="px-3 py-2">{(row.valid_from || '-') + ' s/d ' + (row.valid_to || '-')}</td>
                <td className="px-3 py-2">{row.active ? 'Yes' : 'No'}</td>
                <td className="px-3 py-2 flex gap-3">
                  <button onClick={() => onToggle(row)} className="text-blue-600 hover:underline">
                    {row.active ? 'Nonaktifkan' : 'Aktifkan'}
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="space-y-2">
        <h2 className="text-lg font-medium">Validate a Code (client-side)</h2>
        <div className="grid sm:grid-cols-3 gap-4">
          <label className="flex flex-col gap-1">
            <span className="text-sm text-gray-600">Prefix</span>
            <input value={prefix} onChange={(e)=>setPrefix(e.target.value)} className="border rounded px-3 py-2" placeholder="REF" />
          </label>
          <label className="flex flex-col gap-1 sm:col-span-2">
            <span className="text-sm text-gray-600">Code</span>
            <input value={check} onChange={(e)=>setCheck(e.target.value)} className="border rounded px-3 py-2" placeholder={`${normalizedPrefix}-XXXXXXXX`} />
          </label>
        </div>
        {checked && (
          <div className="text-sm">
            <div>Type: <span className="font-medium">{type || '-'}</span></div>
            <div>
              Format: {isValid ? (
                <span className="text-green-700">valid</span>
              ) : (
                <span className="text-red-700">invalid</span>
              )}
            </div>
          </div>
        )}
      </div>

      <p className="text-xs text-gray-500">Manage kode milik Anda. Untuk approval admin, gunakan endpoint admin atau halaman admin (jika tersedia).</p>
    </div>
  );
};

export default ReferralCodesPage;
