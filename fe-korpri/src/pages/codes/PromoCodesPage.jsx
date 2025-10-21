import React, { useMemo, useState } from 'react';
import { generatePromoCode, isValidCodeFormat, normalizeCode, parseCodeType } from '@/utils';

const PromoCodesPage = () => {
  const [prefix, setPrefix] = useState('PROMO');
  const [length, setLength] = useState(8);
  const [count, setCount] = useState(1);
  const [codes, setCodes] = useState([]);
  const [discountType, setDiscountType] = useState('percent'); // 'percent' | 'fixed'
  const [amount, setAmount] = useState(10);
  const [check, setCheck] = useState('');

  const normalizedPrefix = useMemo(() => normalizeCode(prefix), [prefix]);

  const onGenerate = () => {
    const n = Math.max(1, Math.min(100, Number(count) || 1));
    const L = Math.max(4, Math.min(24, Number(length) || 8));
    const amt = Math.max(0, Number(amount) || 0);
    const list = Array.from({ length: n }, () => ({
      code: generatePromoCode(L, normalizedPrefix),
      discountType,
      amount: amt,
    }));
    setCodes(list);
  };

  const onCopy = async (value) => {
    try {
      await navigator.clipboard.writeText(value);
      // no-op UI toast here; leave minimal to avoid extra deps
    } catch (_) {}
  };

  const checked = normalizeCode(check);
  const isValid = checked ? isValidCodeFormat(checked, { prefix: normalizedPrefix, length: Number(length) || 8 }) : null;
  const type = checked ? parseCodeType(checked) : null;

  return (
    <div className="container mx-auto p-4 space-y-6">
      <h1 className="text-xl font-semibold">Promo Codes</h1>

      <div className="grid sm:grid-cols-3 gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Prefix</span>
          <input
            value={prefix}
            onChange={(e) => setPrefix(e.target.value)}
            className="border rounded px-3 py-2"
            placeholder="PROMO"
          />
        </label>
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Length</span>
          <input
            type="number"
            value={length}
            onChange={(e) => setLength(e.target.value)}
            className="border rounded px-3 py-2"
            min={4}
            max={24}
          />
        </label>
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Count</span>
          <input
            type="number"
            value={count}
            onChange={(e) => setCount(e.target.value)}
            className="border rounded px-3 py-2"
            min={1}
            max={100}
          />
        </label>
      </div>

      <div className="grid sm:grid-cols-3 gap-4">
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

      <div className="flex items-center gap-3">
        <button onClick={onGenerate} className="bg-blue-600 text-white rounded px-4 py-2">
          Generate
        </button>
      </div>

      {codes.length > 0 && (
        <div className="overflow-x-auto border rounded">
          <table className="min-w-full text-sm">
            <thead className="bg-gray-50">
              <tr>
                <th className="text-left px-3 py-2">#</th>
                <th className="text-left px-3 py-2">Code</th>
                <th className="text-left px-3 py-2">Discount</th>
                <th className="text-left px-3 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              {codes.map((row, idx) => (
                <tr key={idx} className="border-t">
                  <td className="px-3 py-2">{idx + 1}</td>
                  <td className="px-3 py-2 font-mono">{row.code}</td>
                  <td className="px-3 py-2">
                    {row.discountType === 'percent' ? `${row.amount}%` : row.amount}
                  </td>
                  <td className="px-3 py-2">
                    <button onClick={() => onCopy(row.code)} className="text-blue-600 hover:underline">Copy</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <div className="space-y-2">
        <h2 className="text-lg font-medium">Validate a Code</h2>
        <div className="flex gap-2">
          <input
            value={check}
            onChange={(e) => setCheck(e.target.value)}
            className="border rounded px-3 py-2 flex-1"
            placeholder={`${normalizedPrefix}-XXXXXXXX`}
          />
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

      <p className="text-xs text-gray-500">Client-side only. Backend should enforce uniqueness and redemption rules.</p>
    </div>
  );
};

export default PromoCodesPage;
