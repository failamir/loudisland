import React, { useMemo, useState } from 'react';
import { generateReferralCode, isValidCodeFormat, normalizeCode, parseCodeType } from '@/utils';

const ReferralCodesPage = () => {
  const [prefix, setPrefix] = useState('REF');
  const [length, setLength] = useState(8);
  const [count, setCount] = useState(1);
  const [codes, setCodes] = useState([]);
  const [check, setCheck] = useState('');

  const normalizedPrefix = useMemo(() => normalizeCode(prefix), [prefix]);

  const onGenerate = () => {
    const n = Math.max(1, Math.min(100, Number(count) || 1));
    const L = Math.max(4, Math.min(24, Number(length) || 8));
    const list = Array.from({ length: n }, () => generateReferralCode(L, normalizedPrefix));
    setCodes(list);
  };

  const onCopy = async (value) => {
    try {
      await navigator.clipboard.writeText(value);
    } catch (_) {}
  };

  const checked = normalizeCode(check);
  const isValid = checked ? isValidCodeFormat(checked, { prefix: normalizedPrefix, length: Number(length) || 8 }) : null;
  const type = checked ? parseCodeType(checked) : null;

  return (
    <div className="container mx-auto p-4 space-y-6">
      <h1 className="text-xl font-semibold">Referral Codes</h1>

      <div className="grid sm:grid-cols-3 gap-4">
        <label className="flex flex-col gap-1">
          <span className="text-sm text-gray-600">Prefix</span>
          <input
            value={prefix}
            onChange={(e) => setPrefix(e.target.value)}
            className="border rounded px-3 py-2"
            placeholder="REF"
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
                <th className="text-left px-3 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              {codes.map((c, idx) => (
                <tr key={idx} className="border-t">
                  <td className="px-3 py-2">{idx + 1}</td>
                  <td className="px-3 py-2 font-mono">{c}</td>
                  <td className="px-3 py-2">
                    <button onClick={() => onCopy(c)} className="text-blue-600 hover:underline">Copy</button>
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

export default ReferralCodesPage;
