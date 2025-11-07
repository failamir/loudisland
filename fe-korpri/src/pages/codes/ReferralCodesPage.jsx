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
  const isSuperAdmin = useMemo(() => auth?.user?.email === 'admin@superadmin.com', [auth]);

  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  // admin panels
  const [adminList, setAdminList] = useState([]);
  const [adminEnabled, setAdminEnabled] = useState(false);
  const [adminLoading, setAdminLoading] = useState(false);
  const [adminActiveList, setAdminActiveList] = useState([]);
  const [adminActiveLoading, setAdminActiveLoading] = useState(false);
  const [wdList, setWdList] = useState([]);
  const [wdLoading, setWdLoading] = useState(false);
  // admin withdrawals: fetch all statuses
  // my referral withdrawals (history)
  const [myWd, setMyWd] = useState([]);
  const [myWdLoading, setMyWdLoading] = useState(false);

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

  const onAdminApprove = async (row, val=true) => {
    try {
      const res = await fetch(`${API_URL}/admin/referral-codes/${row.id}/status`, {
        method: 'PATCH',
        headers,
        body: JSON.stringify({ active: Boolean(val) }),
      });
      if (!res.ok) {
        const err = await res.json().catch(()=>({}));
        alert(err.message || 'Gagal approve');
        return;
      }
      fetchAdmin();
    } catch (_) {
      alert('Gagal approve');
    }
  };

  const onWdAction = async (w, action) => {
    try {
      const res = await fetch(`${API_URL}/admin/referral-withdrawals/${w.id}/status`, {
        method: 'PATCH',
        headers,
        body: JSON.stringify({ action }),
      });
      if (!res.ok) {
        const err = await res.json().catch(()=>({}));
        alert(err.message || 'Gagal update status withdrawal');
        return;
      }
      fetchWithdrawals();
    } catch (_) {
      alert('Gagal update status withdrawal');
    }
  };

  useEffect(() => { fetchMine(); }, []);

  const fetchAdmin = async () => {
    setAdminLoading(true);
    try {
      const res = await fetch(`${API_URL}/admin/referral-codes?per_page=100&active=false`, { headers });
      if (res.status === 403) {
        setAdminEnabled(false);
        setAdminLoading(false);
        return;
      }
      const data = await res.json().catch(()=>({}));
      if (res.ok) {
        setAdminEnabled(true);
        setAdminList(Array.isArray(data?.data) ? data.data : []);
      }
    } finally {
      setAdminLoading(false);
    }
  };

  const fetchAdminActive = async () => {
    setAdminActiveLoading(true);
    try {
      const res = await fetch(`${API_URL}/admin/referral-codes?per_page=100&active=true`, { headers });
      if (res.status === 403) {
        setAdminEnabled(false);
        setAdminActiveLoading(false);
        return;
      }
      const data = await res.json().catch(()=>({}));
      if (res.ok) {
        setAdminEnabled(true);
        setAdminActiveList(Array.isArray(data?.data) ? data.data : []);
      }
    } finally {
      setAdminActiveLoading(false);
    }
  };

  const fetchWithdrawals = async () => {
    setWdLoading(true);
    try {
      const qs = new URLSearchParams({ per_page: '50' });
      const res = await fetch(`${API_URL}/admin/referral-withdrawals?${qs.toString()}`, { headers });
      const data = await res.json().catch(()=>({}));
      if (res.ok) {
        setWdList(Array.isArray(data?.data) ? data.data : []);
      }
    } finally {
      setWdLoading(false);
    }
  };

  const fetchMyWithdrawals = async () => {
    setMyWdLoading(true);
    try {
      const res = await fetch(`${API_URL}/referral/withdrawals?per_page=50`, { headers });
      const data = await res.json().catch(()=>({}));
      if (res.ok) {
        setMyWd(Array.isArray(data?.data) ? data.data : []);
      }
    } finally {
      setMyWdLoading(false);
    }
  };

  useEffect(() => {
    if (isSuperAdmin) {
      fetchAdmin();
      fetchAdminActive();
      fetchWithdrawals();
    }
    fetchMyWithdrawals();
  }, [isSuperAdmin]);

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

      <div className="grid md:grid-cols-2 gap-4">
        {adminEnabled && isSuperAdmin && (
          <div className="space-y-3">
            <h2 className="text-lg font-medium">Admin: Approval Referral (pending)</h2>
            {adminLoading && <div className="text-sm text-gray-600">Loading...</div>}
            <div className="overflow-x-auto border rounded">
              <table className="min-w-full text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="text-left px-3 py-2">#</th>
                  <th className="text-left px-3 py-2">User</th>
                  <th className="text-left px-3 py-2">Email</th>
                  <th className="text-left px-3 py-2">Code</th>
                  <th className="text-left px-3 py-2">Usage</th>
                  <th className="text-left px-3 py-2">Valid</th>
                  <th className="text-left px-3 py-2">Active</th>
                  <th className="text-left px-3 py-2">Actions</th>
                </tr>
              </thead>
              <tbody>
                {adminList.length === 0 && (
                  <tr><td className="px-3 py-3 text-gray-600" colSpan={8}>Tidak ada pengajuan.</td></tr>
                )}
                {adminList.map((r, idx) => (
                  <tr key={r.id || idx} className="border-t">
                    <td className="px-3 py-2">{idx + 1}</td>
                    <td className="px-3 py-2">{r.user?.name || '-'}</td>
                    <td className="px-3 py-2">{r.user?.email || '-'}</td>
                    <td className="px-3 py-2 font-mono">{r.code}</td>
                    <td className="px-3 py-2">{r.used_count ?? 0}/{r.usage_limit ?? '-'}</td>
                    <td className="px-3 py-2">{(r.valid_from || '-') + ' s/d ' + (r.valid_to || '-')}</td>
                    <td className="px-3 py-2">{r.active ? 'Yes' : 'No'}</td>
                    <td className="px-3 py-2 flex gap-3">
                      {!r.active && (
                        <button onClick={() => onAdminApprove(r, true)} className="text-blue-600 hover:underline">Approve</button>
                      )}
                      {r.active && (
                        <button onClick={() => onAdminApprove(r, false)} className="text-red-600 hover:underline">Deactivate</button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
              </table>
            </div>
          </div>
        )}

        {adminEnabled && (
          <div className="space-y-3">
            <h2 className="text-lg font-medium">Admin: Verify Withdrawals (all)</h2>
            {wdLoading && <div className="text-sm text-gray-600">Loading...</div>}
            <div className="overflow-x-auto border rounded">
              <table className="min-w-full text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="text-left px-3 py-2">#</th>
                  <th className="text-left px-3 py-2">ID</th>
                  <th className="text-left px-3 py-2">Amount</th>
                  <th className="text-left px-3 py-2">Bank</th>
                  <th className="text-left px-3 py-2">Account</th>
                  <th className="text-left px-3 py-2">Status</th>
                  <th className="text-left px-3 py-2">Actions</th>
                </tr>
              </thead>
              <tbody>
                {wdList.length === 0 && (
                  <tr><td className="px-3 py-3 text-gray-600" colSpan={7}>Tidak ada pengajuan.</td></tr>
                )}
                {wdList.map((w, idx) => (
                  <tr key={w.id || idx} className="border-t">
                    <td className="px-3 py-2">{idx + 1}</td>
                    <td className="px-3 py-2">{w.id}</td>
                    <td className="px-3 py-2">{new Intl.NumberFormat('id-ID').format(w.amount || 0)}</td>
                    <td className="px-3 py-2">{w.bank}</td>
                    <td className="px-3 py-2">{w.account_name} / {w.account_number}</td>
                    <td className="px-3 py-2">{w.status}</td>
                    <td className="px-3 py-2 flex gap-3">
                      <button onClick={() => onWdAction(w, 'approved')} className="text-blue-600 hover:underline">Approve</button>
                      <button onClick={() => onWdAction(w, 'paid')} className="text-green-700 hover:underline">Mark Paid</button>
                      <button onClick={() => onWdAction(w, 'rejected')} className="text-red-600 hover:underline">Reject</button>
                    </td>
                  </tr>
                ))}
              </tbody>
              </table>
            </div>
          </div>
        )}
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
            {((adminEnabled && isSuperAdmin) ? adminActiveList : list).length === 0 && (
              <tr><td className="px-3 py-3 text-gray-600" colSpan={6}>Belum ada referral code.</td></tr>
            )}
            {((adminEnabled && isSuperAdmin) ? adminActiveList : list).map((row, idx) => (
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

      <div className="space-y-3">
        <h2 className="text-lg font-medium">Riwayat Tarik Saldo (Referral){(adminEnabled && isSuperAdmin) ? ' - Admin' : ''}</h2>
        {(!(adminEnabled && isSuperAdmin) && myWdLoading) && <div className="text-sm text-gray-600">Loading...</div>}
        {((adminEnabled && isSuperAdmin) && wdLoading) && <div className="text-sm text-gray-600">Loading...</div>}
        <div className="overflow-x-auto border rounded">
          <table className="min-w-full text-sm">
            <thead className="bg-gray-50">
              <tr>
                <th className="text-left px-3 py-2">#</th>
                <th className="text-left px-3 py-2">Tanggal</th>
                <th className="text-left px-3 py-2">Amount</th>
                <th className="text-left px-3 py-2">Bank</th>
                <th className="text-left px-3 py-2">Account</th>
                <th className="text-left px-3 py-2">Status</th>
              </tr>
            </thead>
            <tbody>
              {((adminEnabled && isSuperAdmin) ? wdList : myWd).length === 0 && (
                <tr><td className="px-3 py-3 text-gray-600" colSpan={6}>Tidak ada riwayat penarikan.</td></tr>
              )}
              {((adminEnabled && isSuperAdmin) ? wdList : myWd).map((w, idx) => (
                <tr key={w.id || idx} className="border-t">
                  <td className="px-3 py-2">{idx + 1}</td>
                  <td className="px-3 py-2">{w.created_at ? new Date(w.created_at).toLocaleString('id-ID') : '-'}</td>
                  <td className="px-3 py-2">{new Intl.NumberFormat('id-ID').format(w.amount || 0)}</td>
                  <td className="px-3 py-2">{w.bank}</td>
                  <td className="px-3 py-2">{w.account_name} / {w.account_number}</td>
                  <td className="px-3 py-2">{w.status}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/*   <div className="space-y-2">
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
      </div> */}

      <p className="text-xs text-gray-500">Manage kode milik Anda. Untuk approval admin, gunakan endpoint admin atau halaman admin (jika tersedia).</p>
    </div>
  );
};

export default ReferralCodesPage;
