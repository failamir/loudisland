import React, { Fragment, useEffect, useState, useMemo } from 'react';
import axios from 'axios';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarHeading } from '@/partials/toolbar';
import { useLayout } from '@/providers';
import { DataGrid, DataGridColumnHeader, KeenIcon } from '@/components';

const MissingParticipantsPage = () => {
  const { currentLayout } = useLayout();
  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  const [rows, setRows] = useState([]);
  const [meta, setMeta] = useState({ total: 0, current_page: 1, per_page: 50 });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [actionMsg, setActionMsg] = useState('');

  const [search, setSearch] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');

  const fetchData = async (params = {}) => {
    try {
      setLoading(true);
      setError(null);
      const finalParams = {
        search: search || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
        per_page: meta.per_page || 50,
        page: meta.current_page || 1,
        ...params,
      };
      const res = await axios.get(`${API_URL}/participants/missing`, { params: finalParams });
      const data = Array.isArray(res.data) ? res.data : res.data.data;
      setRows(data || []);
      const m = res?.data?.meta;
      if (m) setMeta({ ...meta, total: m.total ?? 0, current_page: m.current_page ?? 1, per_page: m.per_page ?? 50 });
      else setMeta((prev) => ({ ...prev, total: data?.length ?? 0 }));
    } catch (e) {
      setError(e?.message || 'Gagal memuat data');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData({ page: 1 });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const columns = useMemo(() => [
    {
      accessorKey: 'invoice',
      id: 'invoice',
      header: ({ column }) => <DataGridColumnHeader title="Invoice" column={column} />,
      cell: ({ row }) => row.original.invoice || '-',
      enableSorting: true,
      meta: { headerClassName: 'min-w-[160px]' },
    },
    {
      accessorKey: 'user.name',
      id: 'name',
      header: ({ column }) => <DataGridColumnHeader title="Nama Pemesan" column={column} />,
      cell: ({ row }) => row.original?.user?.name || '-',
      enableSorting: false,
      meta: { headerClassName: 'min-w-[200px]' },
    },
    {
      accessorKey: 'user.email',
      id: 'email',
      header: ({ column }) => <DataGridColumnHeader title="Email" column={column} />,
      cell: ({ row }) => row.original?.user?.email || '-',
      enableSorting: false,
      meta: { headerClassName: 'min-w-[220px]' },
    },
    {
      accessorKey: 'amount',
      id: 'amount',
      header: ({ column }) => <DataGridColumnHeader title="Jumlah" column={column} />,
      cell: ({ row }) => new Intl.NumberFormat('id-ID').format(row.original.amount || 0),
      enableSorting: true,
      meta: { headerClassName: 'min-w-[140px]' },
    },
    {
      accessorKey: 'created_at',
      id: 'created_at',
      header: ({ column }) => <DataGridColumnHeader title="Tanggal" column={column} />,
      cell: ({ row }) => row.original.created_at || '-',
      enableSorting: true,
      meta: { headerClassName: 'min-w-[180px]' },
    },
    {
      accessorKey: 'actions',
      id: 'actions',
      header: () => <span>Aksi</span>,
      enableSorting: false,
      cell: ({ row }) => (
        <button
          type="button"
          className="btn btn-xs btn-primary"
          onClick={async () => {
            try {
              setActionMsg('');
              await axios.post(`${API_URL}/participants/generate`, { invoice: row.original.invoice });
              setActionMsg(`Generated participants for ${row.original.invoice}`);
              // refresh list
              fetchData({ page: 1 });
            } catch (e) {
              setActionMsg(`Gagal generate: ${e?.response?.data?.message || e?.message || 'Unknown error'}`);
            }
          }}
        >
          Generate Participants
        </button>
      ),
      meta: { headerClassName: 'min-w-[200px]' },
    },
  ], []);

  const ToolbarContent = () => (
    <div className="card-header flex-wrap gap-2 border-b-0 px-5 w-full">
      <div className="flex items-center gap-2 flex-wrap">
        <label className="input input-sm">
          <KeenIcon icon="magnifier" />
          <input
            type="text"
            placeholder="Cari invoice / nama / email..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => { if (e.key === 'Enter') fetchData({ page: 1 }); }}
          />
        </label>
        <input type="date" className="input input-sm" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} />
        <span className="text-gray-500">s/d</span>
        <input type="date" className="input input-sm" value={dateTo} onChange={(e) => setDateTo(e.target.value)} />
        <button className="btn btn-sm btn-primary" onClick={() => fetchData({ page: 1 })} disabled={loading}>
          <KeenIcon icon="magnifier" /> Cari
        </button>
        <button
          className="btn btn-sm btn-light"
          onClick={() => { setSearch(''); setDateFrom(''); setDateTo(''); fetchData({ page: 1, search: undefined, date_from: undefined, date_to: undefined }); }}
          disabled={loading}
        >
          Clear
        </button>
      </div>
    </div>
  );

  return (
    <Fragment>
      {currentLayout?.name === 'demo1-layout' && (
        <Container>
          <Toolbar>
            <ToolbarHeading title="Missing Participants" description="Transaksi sukses tanpa participant" />
            <ToolbarActions>
              <button className="btn btn-sm btn-light" onClick={() => fetchData()} disabled={loading}>Refresh</button>
            </ToolbarActions>
          </Toolbar>
        </Container>
      )}

      <Container>
        {error && <div className="text-red-600 mb-2">{error}</div>}
        {actionMsg && <div className="text-green-700 mb-2">{actionMsg}</div>}
        <DataGrid
          columns={columns}
          data={rows}
          pagination={{ size: 50 }}
          toolbar={<ToolbarContent />}
          layout={{ card: true }}
        />
      </Container>
    </Fragment>
  );
};

export default MissingParticipantsPage;
