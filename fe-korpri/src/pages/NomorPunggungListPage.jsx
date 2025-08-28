import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import PairingForm from './PairingForm';
import { Container } from '@/components/container';
import { DataGrid, DataGridColumnHeader } from '@/components';
import { KeenIcon } from '@/components';
let QrReader;
try {
  QrReader = require('react-qr-reader').default;
} catch {}

const NomorPunggungListPage = () => {
  const [data, setData] = useState([]);
  const [search, setSearch] = useState('');
  const [filtered, setFiltered] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showScanner, setShowScanner] = useState(false);
  const [scannedNomor, setScannedNomor] = useState('');
  const [pairSuccess, setPairSuccess] = useState(false);
  // Generate controls
  const [genStart, setGenStart] = useState(1);
  const [genEnd, setGenEnd] = useState(10000);
  const [genSize, setGenSize] = useState(300);
  const [genLoading, setGenLoading] = useState(false);
  const [genMsg, setGenMsg] = useState('');
  const baseUrl = import.meta.env.VITE_APP_API_URL || 'https://korpri.ifailamir.my.id/api/v1';
  // Pagination
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(10);
  const [total, setTotal] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const handleGenerate = async () => {
    setGenMsg('');
    if (!Number.isInteger(+genStart) || !Number.isInteger(+genEnd) || +genStart < 1 || +genEnd < +genStart) {
      setGenMsg('Range tidak valid. Pastikan start >= 1 dan end >= start.');
      return;
    }
    setGenLoading(true);
    try {
      const payload = { start: +genStart, end: +genEnd, size: +genSize };
      const res = await axios.post(`${baseUrl}/nomor-punggung/generate`, payload);
      const created = res?.data?.created ?? 0;
      const skipped = res?.data?.skipped ?? 0;
      setGenMsg(`Selesai. Dibuat: ${created}, dilewati: ${skipped}.`);
      await fetchList();
    } catch (e) {
      setGenMsg(e?.response?.data?.message || 'Gagal generate.');
    } finally {
      setGenLoading(false);
    }
  };
  const fetchList = async (opts = {}) => {
    try {
      setLoading(true);
      const currentPage = opts.page ?? page;
      const currentPerPage = opts.per_page ?? perPage;
      const qParam = Object.prototype.hasOwnProperty.call(opts, 'q') ? opts.q : (search.trim() || undefined);
      const res = await axios.get(`${baseUrl}/nomor-punggung`, { params: { per_page: currentPerPage, page: currentPage, q: qParam } });
      // API returns shape: { data: [...], pagination: {...} }
      const items = Array.isArray(res.data) ? res.data : (res.data && Array.isArray(res.data.data) ? res.data.data : []);
      setData(items);
      setFiltered(items);
      const p = res?.data?.pagination;
      if (p) {
        setTotal(p.total ?? 0);
        setLastPage(p.last_page ?? 1);
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchList();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    fetchList({ page, per_page: perPage });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [page, perPage]);

  useEffect(() => {
    // server-side search: reset to first page and fetch with q
    setPage(1);
    fetchList({ page: 1, per_page: perPage, q: search.trim() || undefined });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search]);

  if (loading) return <div>Loading...</div>;

  const columns = useMemo(
    () => [
      {
        accessorFn: (_row, i) => i,
        id: 'no',
        header: ({ column }) => <DataGridColumnHeader title="No" column={column} />,
        enableSorting: false,
        cell: ({ row }) => (page - 1) * perPage + row.index + 1,
        meta: { headerClassName: 'w-[80px]' },
      },
      {
        accessorKey: 'nomor_punggung',
        id: 'nomor_punggung',
        header: ({ column }) => <DataGridColumnHeader title="Nomor Punggung" column={column} />,
        enableSorting: false,
        cell: ({ row }) => <span className="font-semibold text-gray-900">{row.original.nomor_punggung}</span>,
        meta: { headerClassName: 'min-w-[160px]' },
      },
      {
        accessorKey: 'qr_url',
        id: 'qr',
        header: ({ column }) => <DataGridColumnHeader title="QR" column={column} />,
        enableSorting: false,
        cell: ({ row }) => (
          <img
            src={row.original.qr_url}
            alt={row.original.nomor_punggung}
            className="w-24 h-24 object-contain"
          />
        ),
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorKey: 'pairing',
        id: 'pairing',
        header: ({ column }) => <DataGridColumnHeader title="Pairing" column={column} />,
        enableSorting: false,
        cell: ({ row }) => (
          <PairingForm
            nomorPunggung={row.original.nomor_punggung}
            initialPaired={!!row.original.paired}
            initialPendaftarId={row.original.pendaftar_id}
            onSuccess={() => fetchList({ page, per_page: perPage, q: search.trim() || undefined })}
            onUnpaired={() => fetchList({ page, per_page: perPage, q: search.trim() || undefined })}
          />
        ),
        meta: { headerClassName: 'min-w-[220px]' },
      },
    ],
    [page, perPage, search]
  );

  const ToolbarContent = () => (
    <div className="card-header flex-wrap gap-2 border-b-0 px-5 w-full">
      <div className="flex items-center gap-2 flex-wrap">
        <label className="input input-sm">
          <KeenIcon icon="magnifier" />
          <input
            type="text"
            placeholder="Cari nomor..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') fetchList({ page: 1, per_page: perPage, q: e.currentTarget.value.trim() || undefined });
            }}
          />
        </label>
        <button
          className="btn btn-sm btn-primary"
          onClick={() => fetchList({ page: 1, per_page: perPage, q: search.trim() || undefined })}
          disabled={loading}
        >
          <KeenIcon icon="magnifier" />
          Cari
        </button>
        {search && (
          <button
            className="btn btn-sm btn-light"
            onClick={() => {
              setSearch('');
              fetchList({ page: 1, per_page: perPage, q: undefined });
            }}
            disabled={loading}
          >
            Clear
          </button>
        )}
      </div>
    </div>
  );

  return (
    <Container>
      <div className="flex items-center justify-between flex-wrap gap-2 py-4">
        <h2 className="text-xl font-semibold">Daftar Nomor Punggung & QR</h2>
        <button className="btn btn-sm btn-info" onClick={() => setShowScanner((s) => !s)}>
          {showScanner ? 'Tutup QR Scanner' : 'Scan QR untuk Pairing'}
        </button>
      </div>

      {showScanner && QrReader && (
        <div className="mb-4">
          <QrReader
            delay={300}
            onError={console.error}
            onScan={(data) => {
              if (data) {
                setScannedNomor(data);
                setShowScanner(false);
              }
            }}
            style={{ width: 240 }}
          />
        </div>
      )}

      {scannedNomor && (
        <div className="mb-4 bg-gray-50 p-3 rounded-lg">
          <b>Nomor hasil scan:</b> {scannedNomor}
          <PairingForm
            nomorPunggung={scannedNomor}
            onSuccess={() => {
              setPairSuccess(true);
              setTimeout(() => {
                setPairSuccess(false);
                setScannedNomor('');
              }, 2000);
            }}
          />
          {pairSuccess && <div className="text-success">Pairing berhasil!</div>}
        </div>
      )}

      <div className="flex items-center gap-2 mb-3 flex-wrap">
        <label className="text-sm">Start:</label>
        <input
          type="number"
          value={genStart}
          onChange={(e) => setGenStart(e.target.value)}
          min={1}
          className="input input-sm w-[110px]"
        />
        <label className="text-sm">End:</label>
        <input
          type="number"
          value={genEnd}
          onChange={(e) => setGenEnd(e.target.value)}
          min={1}
          className="input input-sm w-[110px]"
        />
        <label className="text-sm">Size:</label>
        <input
          type="number"
          value={genSize}
          onChange={(e) => setGenSize(e.target.value)}
          min={64}
          max={1024}
          className="input input-sm w-[110px]"
        />
        <button onClick={handleGenerate} disabled={genLoading} className="btn btn-sm btn-info">
          {genLoading ? 'Generating...' : 'Generate QR'}
        </button>
        {genMsg && <span className="text-2sm ms-2">{genMsg}</span>}
      </div>

      <DataGrid
        columns={columns}
        data={filtered}
        pagination={{ size: 10 }}
        layout={{ card: true }}
        toolbar={<ToolbarContent />}
      />

      <div className="flex items-center gap-3 mt-4 flex-wrap">
        <button className="btn btn-sm btn-light" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>
          Prev
        </button>
        <span>
          Page {page} / {lastPage}
        </span>
        <button
          className="btn btn-sm btn-light"
          onClick={() => setPage((p) => Math.min(lastPage, p + 1))}
          disabled={page >= lastPage}
        >
          Next
        </button>
        <span>Total: {total}</span>
        <label className="flex items-center gap-2">
          <span>Per page:</span>
          <select
            className="select select-sm"
            value={perPage}
            onChange={(e) => {
              setPage(1);
              setPerPage(parseInt(e.target.value, 10));
            }}
          >
            <option value={10}>10</option>
            <option value={50}>50</option>
            <option value={100}>100</option>
            <option value={200}>200</option>
          </select>
        </label>
      </div>
    </Container>
  );
};

export default NomorPunggungListPage;

