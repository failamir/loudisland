import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import PairingForm from './PairingForm';
import { DataGrid, DataGridColumnHeader, DataGridRowSelect, DataGridRowSelectAll } from '@/components';
import { Input } from '@/components/ui/input';
import { Modal } from '@/components/modal/Modal';
import { ModalContent } from '@/components/modal/ModalContent';
let QrReader;
try {
  QrReader = require('react-qr-reader').default;
} catch {}

const NomorPunggungListPage = () => {
  const [search, setSearch] = useState('');
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
  const baseUrl = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';
  const [headerTotal, setHeaderTotal] = useState(0);
  const [gridKey, setGridKey] = useState(0);
  const [qrModalOpen, setQrModalOpen] = useState(false);
  const [qrModalData, setQrModalData] = useState({ nomor: '', url: '' });

  const formatIDDate = (value) => {
    if (!value) return '-';
    try {
      const s = new Date(value).toLocaleString('id-ID', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      });
      return `${s} WIB`;
    } catch {
      return String(value);
    }
  };
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
      // refresh grid data
      setGridKey((k) => k + 1);
    } catch (e) {
      setGenMsg(e?.response?.data?.message || 'Gagal generate.');
    } finally {
      setGenLoading(false);
    }
  };
  const fetchServerNomor = async ({ pageIndex, pageSize }) => {
    try {
      setLoading(true);
      const res = await axios.get(`${baseUrl}/nomor-punggung`, {
        params: {
          page: pageIndex + 1,
          per_page: pageSize,
          q: search.trim() || undefined,
        },
      });
      const items = Array.isArray(res.data)
        ? res.data
        : (res.data && Array.isArray(res.data.data) ? res.data.data : []);
      const total = res?.data?.pagination?.total ?? items.length;
      setHeaderTotal(total);
      return { data: items, totalCount: total };
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    // trigger grid reload on initial mount and when search changes
    setGridKey((k) => k + 1);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search]);

  const ColumnInputFilter = ({ column }) => (
    <Input
      placeholder="Filter..."
      value={column.getFilterValue() ?? ''}
      onChange={(event) => column.setFilterValue(event.target.value)}
      className="h-9 w-full max-w-40"
    />
  );

  const columns = useMemo(
    () => [
      {
        accessorKey: 'id',
        header: () => <DataGridRowSelectAll />,
        cell: ({ row }) => <DataGridRowSelect row={row} />,
        enableSorting: false,
        enableHiding: false,
        meta: { headerClassName: 'w-0 text-center', cellClassName: 'text-center' },
      },
      {
        accessorKey: 'nomor_punggung',
        id: 'nomor_punggung',
        header: ({ column }) => (
          <DataGridColumnHeader title="Nomor Punggung" column={column} />
        ),
        enableSorting: true,
        cell: ({ row }) => (
          <span className="text-3xl font-bold tracking-wide">{row.original.nomor_punggung}</span>
        ),
        meta: { headerClassName: 'min-w-[160px] text-center', cellClassName: 'text-center' },
      },
      {
        accessorKey: 'qr_url',
        id: 'qr_url',
        header: ({ column }) => (
          <DataGridColumnHeader title="QR" column={column} />
        ),
        enableSorting: false,
        cell: ({ row }) => (
          <div className="flex justify-center">
            <img
              src={row.original.qr_url}
              alt={row.original.nomor_punggung}
              className="w-16 h-16 object-contain cursor-pointer"
              title="Klik untuk perbesar & pairing"
              onClick={() => {
                setQrModalData({ nomor: row.original.nomor_punggung, url: row.original.qr_url });
                setQrModalOpen(true);
              }}
            />
          </div>
        ),
        meta: { headerClassName: 'min-w-[140px] text-center', cellClassName: 'text-center' },
      },
      {
        accessorKey: 'paired_at',
        id: 'paired_at',
        header: ({ column }) => (
          <DataGridColumnHeader title="Paired At" column={column} />
        ),
        enableSorting: true,
        cell: ({ row }) => formatIDDate(row.original.paired_at),
        meta: { headerClassName: 'min-w-[180px] text-center', cellClassName: 'text-center' },
      },
      {
        accessorKey: 'user_name',
        id: 'user',
        header: ({ column }) => (
          <DataGridColumnHeader title="User" column={column} />
        ),
        enableSorting: false,
        cell: ({ row }) => (
          <div className="flex flex-col gap-0.5">
            <span className="text-sm font-medium text-gray-900">{row.original.user_name || '-'}</span>
            <span className="text-2sm text-gray-700">{row.original.user_email || '-'}</span>
          </div>
        ),
        meta: { headerClassName: 'min-w-[220px] text-center', cellClassName: 'text-center' },
      },
      {
        accessorKey: 'user_id',
        id: 'user_id',
        header: ({ column }) => (
          <DataGridColumnHeader title="User ID" column={column} />
        ),
        enableSorting: true,
        cell: ({ row }) => row.original.user_id || '-',
        meta: { headerClassName: 'min-w-[120px] text-center', cellClassName: 'text-center' },
      },
      {
        id: 'pairing',
        header: ({ column }) => (
          <DataGridColumnHeader title="Pairing" column={column} />
        ),
        enableSorting: false,
        cell: ({ row }) => (
          <div className="flex justify-center">
            <PairingForm
              nomorPunggung={row.original.nomor_punggung}
              initialPaired={!!row.original.paired}
              initialPendaftarId={row.original.pendaftar_id}
              onSuccess={() => setGridKey((k) => k + 1)}
              onUnpaired={() => setGridKey((k) => k + 1)}
            />
          </div>
        ),
        meta: { headerClassName: 'min-w-[200px] text-center', cellClassName: 'text-center' },
      },
    ],
    []
  );

  const Toolbar = (
    <div data-toolbar>
      <div className="flex items-center justify-between gap-4 flex-wrap">
        <div className="flex flex-col">
          <h3 className="text-lg font-semibold">Daftar Nomor Lari & QR</h3>
          <span className="text-sm text-muted-foreground">Scan QR untuk Pairing</span>
        </div>
        <div className="flex items-center gap-2">
          <Input
            placeholder="Cari nomor..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="h-9 w-full max-w-64"
          />
          <label className="text-sm text-muted-foreground">
            Start
            <input type="number" value={genStart} onChange={e => setGenStart(e.target.value)} min={1} className="ml-2 px-3 py-2 w-28 border rounded" />
          </label>
          <label className="text-sm text-muted-foreground">
            End
            <input type="number" value={genEnd} onChange={e => setGenEnd(e.target.value)} min={1} className="ml-2 px-3 py-2 w-28 border rounded" />
          </label>
          <label className="text-sm text-muted-foreground">
            Size
            <input type="number" value={genSize} onChange={e => setGenSize(e.target.value)} min={64} max={1024} className="ml-2 px-3 py-2 w-24 border rounded" />
          </label>
          <button
            onClick={handleGenerate}
            disabled={genLoading}
            className="btn btn-info px-3 py-2"
            type="button"
          >
            {genLoading ? 'Generating...' : 'Generate QR'}
          </button>
        </div>
      </div>
      {genMsg && <div className="mt-2 text-xs text-muted-foreground">{genMsg}</div>}
      {/* Optional: QR Scanner trigger inside card header */}
      <div className="mt-3">
        <button onClick={() => setShowScanner(s => !s)} className="text-sm underline" type="button">
          {showScanner ? 'Tutup QR Scanner' : 'Scan QR untuk Pairing'}
        </button>
        {showScanner && QrReader && (
          <div className="mt-3">
            <QrReader
              delay={300}
              onError={console.error}
              onScan={data => {
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
          <div className="mt-2 rounded bg-muted/40 p-3">
            <b>Nomor hasil scan:</b> {scannedNomor}
            <div className="mt-2">
              <PairingForm nomorPunggung={scannedNomor} onSuccess={() => {setPairSuccess(true); setTimeout(()=>{setPairSuccess(false); setScannedNomor('');}, 2000);}} />
            </div>
            {pairSuccess && <div className="text-green-600 text-sm mt-1">Pairing berhasil!</div>}
          </div>
        )}
      </div>
    </div>
  );

  return (
    <div className="p-6">
      <DataGrid
        key={gridKey}
        columns={columns}
        rowSelection={true}
        serverSide={true}
        pagination={{
          size: 10,
          sizes: [10, 50, 100, 200],
          info: 'Menampilkan {from}-{to} dari {count}',
          moreLimit: 5,
        }}
        onFetchData={async ({ pageIndex, pageSize }) => fetchServerNomor({ pageIndex, pageSize })}
        layout={{ card: true }}
        toolbar={Toolbar}
      />

      {/* QR Modal */}
      <Modal open={qrModalOpen} onClose={() => setQrModalOpen(false)}>
        <ModalContent className="max-w-md mx-auto mt-20">
          <div className="p-5">
            <div className="flex items-start justify-between mb-4">
              <div>
                <h3 className="text-lg font-semibold">QR Nomor {qrModalData.nomor}</h3>
                <p className="text-sm text-muted-foreground">Perbesar QR & lakukan pairing di sini</p>
              </div>
              <button className="text-sm" onClick={() => setQrModalOpen(false)} type="button">Tutup</button>
            </div>
            <div className="flex justify-center mb-4">
              {/* larger preview */}
              {qrModalData.url && (
                <img src={qrModalData.url} alt={qrModalData.nomor} className="w-64 h-64 object-contain" />
              )}
            </div>
            {/* Pairing input inside modal */}
            {qrModalData.nomor && (
              <div className="flex justify-center">
                <PairingForm
                  nomorPunggung={qrModalData.nomor}
                  onSuccess={() => {
                    setGridKey(k => k + 1);
                    setQrModalOpen(false);
                  }}
                />
              </div>
            )}
          </div>
        </ModalContent>
      </Modal>
    </div>
  );
};

export default NomorPunggungListPage;

