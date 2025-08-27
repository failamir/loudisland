import React from 'react';

export default function TransactionsFilters({ value = {}, onChange }) {
  const [state, setState] = React.useState({
    status: value.status || '',
    invoice: value.invoice || '',
  });

  function emit(next) {
    setState(next);
    onChange && onChange(next);
  }

  return (
    <div className="flex gap-3 items-end mb-4">
      <div>
        <label className="block text-sm text-gray-500">Status</label>
        <select
          className="border rounded px-2 py-1"
          value={state.status}
          onChange={(e) => emit({ ...state, status: e.target.value })}
        >
          <option value="">Semua</option>
          <option value="pending">Pending</option>
          <option value="success">Success</option>
          <option value="failed">Failed</option>
        </select>
      </div>
      <div>
        <label className="block text-sm text-gray-500">Invoice</label>
        <input
          className="border rounded px-2 py-1"
          placeholder="Cari invoice..."
          value={state.invoice}
          onChange={(e) => emit({ ...state, invoice: e.target.value })}
        />
      </div>
      <button
        className="bg-blue-600 text-white px-3 py-1 rounded"
        onClick={() => onChange && onChange(state)}
      >
        Terapkan
      </button>
    </div>
  );
}
