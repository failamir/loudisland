import React, { useMemo, useState, useEffect } from 'react';
import { generatePromoCode, isValidCodeFormat, normalizeCode, parseCodeType } from '@/utils';
import * as authHelper from '@/auth/_helpers';

const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

const PromoCodesPage = () => {
  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [code, setCode] = useState('');
  const [discountType, setDiscountType] = useState('percent');
  const [amount, setAmount] = useState(10);
  const [usageLimit, setUsageLimit] = useState('');
  const [expiresAt, setExpiresAt] = useState(''); // datetime-local
  const [tnc, setTnc] = useState('');
  const [minPurchase, setMinPurchase] = useState('');
  const [maxPurchase, setMaxPurchase] = useState('');
  const [active, setActive] = useState(true);

  const auth = useMemo(() => authHelper.getAuth(), []);

  const headers = useMemo(() => ({
    'Content-Type': 'application/json',
    ...(auth?.access_token ? { Authorization: `Bearer ${auth.access_token}` } : {}),
  }), [auth]);

  const toLaravelDateTime = (value) => {
    if (!value) return null;
    // value like '2025-10-30T12:34'
    return value.includes('T') ? `${value}:00`.replace('T', ' ') : value;
  };

  const fromLaravelDateTime = (value) => {
    if (!value) return '';
    // expects 'YYYY-MM-DD HH:mm:ss' -> 'YYYY-MM-DDTHH:mm'
    return value.replace(' ', 'T').slice(0, 16);
  };

  const resetForm = () => {
    setEditingId(null);
    setCode('');
    setDiscountType('percent');
    setAmount(10);
    setUsageLimit('');
    setExpiresAt('');
    setTnc('');
    setMinPurchase('');
    setMaxPurchase('');
    setActive(true);
  };

  const fetchPromos = async () => {
    setLoading(true);
    try {
      const res = await fetch(`${API_URL}/promo-codes?per_page=100`, { headers });
      const data = await res.json();
      // data can be pagination object or array depending on backend; handle both
      setList(Array.isArray(data) ? data : data.data || []);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchPromos(); }, []);

  const onSave = async () => {
    const payload = {
      code,
      discount_type: discountType,
      amount: Number(amount) || 0,
      usage_limit: usageLimit === '' ? null : Math.max(0, Number(usageLimit) || 0),
      expires_at: toLaravelDateTime(expiresAt),
      tnc: tnc || null,
      min_purchase: minPurchase === '' ? null : Number(minPurchase),
      max_purchase: maxPurchase === '' ? null : Number(maxPurchase),
      active,
    };
    const url = editingId ? `${API_URL}/promo-codes/${editingId}` : `${API_URL}/promo-codes`;
    const method = editingId ? 'PUT' : 'POST';
    const res = await fetch(url, { method, headers, body: JSON.stringify(payload) });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || 'Failed to save');
    }
    resetForm();
    fetchPromos();
  };

  const onEdit = (row) => {
    setEditingId(row.id);
    setCode(row.code || '');
    setDiscountType(row.discount_type || 'percent');
    setAmount(row.amount ?? 0);
    setUsageLimit(row.usage_limit ?? '');
    setExpiresAt(fromLaravelDateTime(row.expires_at));
    setTnc(row.tnc || '');
    setMinPurchase(row.min_purchase ?? '');
    setMaxPurchase(row.max_purchase ?? '');
    setActive(Boolean(row.active));
  };

  const onDelete = async (id) => {
    if (!confirm('Delete this promo code?')) return;
    const res = await fetch(`${API_URL}/promo-codes/${id}`, { method: 'DELETE', headers });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || 'Failed to delete');
    }
    if (editingId === id) resetForm();
    fetchPromos();
  };

  return (
    <div className="container mx-auto p-4 space-y-6">
      <h1 className="text-xl font-semibold">Promo Codes</h1>

      <div className="grid sm:grid-cols-3 gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Code</span>
          <input
            value={code}
            onChange={(e) => setCode(e.target.value)}
            className="border rounded px-3 py-2"
            placeholder="PROMO-ABC123"
          />
        </label>
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Discount Type</span>
          <select
            value={discountType}
            onChange={(e) => setDiscountType(e.target.value)}
            className="border rounded px-3 py-2"
          >
            <option value="percent">Percent (%)</option>
            <option value="fixed">Fixed Amount</option>
          </select>
        </label>
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Amount</span>
          <input
            type="number"
            value={amount}
            onChange={(e) => setAmount(e.target.value)}
            className="border rounded px-3 py-2"
            min={0}
          />
        </label>
      </div>

      <div className="grid sm:grid-cols-3 gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Usage Limit</span>
          <input
            type="number"
            value={usageLimit}
            onChange={(e) => setUsageLimit(e.target.value)}
            className="border rounded px-3 py-2"
            placeholder="e.g. 100 or empty"
            min={0}
          />
        </label>
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Min Purchase (qty)</span>
          <input
            type="number"
            value={minPurchase}
            onChange={(e) => setMinPurchase(e.target.value)}
            className="border rounded px-3 py-2"
            placeholder="e.g. 1"
            min={0}
          />
        </label>
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Max Purchase (qty)</span>
          <input
            type="number"
            value={maxPurchase}
            onChange={(e) => setMaxPurchase(e.target.value)}
            className="border rounded px-3 py-2"
            placeholder="e.g. 3"
            min={0}
          />
        </label>
      </div>

      <div className="grid sm:grid-cols-3 gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Expiry (expiresAt)</span>
          <input
            type="datetime-local"
            value={expiresAt}
            onChange={(e) => setExpiresAt(e.target.value)}
            className="border rounded px-3 py-2"
          />
        </label>
        <label className="flex flex-col gap-1 sm:col-span-2">
          <span className="text-sm text-gray-600">Terms & Conditions (tnc)</span>
          <textarea
            value={tnc}
            onChange={(e) => setTnc(e.target.value)}
            className="border rounded px-3 py-2 min-h-[42px]"
            placeholder="Syarat & Ketentuan"
          />
        </label>
        <label className="flex items-center gap-2">
          <input type="checkbox" checked={active} onChange={(e) => setActive(e.target.checked)} />
          <span className="text-sm text-gray-600">Active</span>
        </label>
      </div>

      <div className="flex items-center gap-3">
        <button onClick={onSave} className="bg-blue-600 text-white rounded px-4 py-2">
          {editingId ? 'Update' : 'Save'}
        </button>
        {editingId && (
          <button onClick={resetForm} className="border rounded px-4 py-2">Cancel</button>
        )}
      </div>

      {loading && <div className="text-sm text-gray-600">Loading...</div>}
      {list.length > 0 && (
        <div className="overflow-x-auto border rounded">
          <table className="min-w-full text-sm">
            <thead className="bg-gray-50">
              <tr>
                <th className="text-left px-3 py-2">#</th>
                <th className="text-left px-3 py-2">Code</th>
                <th className="text-left px-3 py-2">Discount</th>
                <th className="text-left px-3 py-2">Usage</th>
                <th className="text-left px-3 py-2">Min/Max Qty</th>
                <th className="text-left px-3 py-2">Expires</th>
                <th className="text-left px-3 py-2">Active</th>
                <th className="text-left px-3 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              {list.map((row, idx) => (
                <tr key={idx} className="border-t">
                  <td className="px-3 py-2">{idx + 1}</td>
                  <td className="px-3 py-2 font-mono">{row.code}</td>
                  <td className="px-3 py-2">{row.discount_type === 'percent' ? `${row.amount}%` : row.amount}</td>
                  <td className="px-3 py-2">{row.used_count ?? 0}/{row.usage_limit ?? '-'}</td>
                  <td className="px-3 py-2">{(row.min_purchase ?? '-')}/{(row.max_purchase ?? '-')} </td>
                  <td className="px-3 py-2">{row.expires_at || '-'}</td>
                  <td className="px-3 py-2">{row.active ? 'Yes' : 'No'}</td>
                  <td className="px-3 py-2 flex gap-3">
                    <button onClick={() => onEdit(row)} className="text-blue-600 hover:underline">Edit</button>
                    <button onClick={() => onDelete(row.id)} className="text-red-600 hover:underline">Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default PromoCodesPage;
