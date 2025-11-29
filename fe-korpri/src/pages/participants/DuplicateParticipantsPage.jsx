import React, { Fragment, useEffect, useState, useMemo } from 'react';
import axios from 'axios';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarHeading } from '@/partials/toolbar';
import { useLayout } from '@/providers';
import { DataGrid, DataGridColumnHeader, KeenIcon } from '@/components';

const DuplicateParticipantsPage = () => {
  const { currentLayout } = useLayout();
  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchData = async () => {
    try {
      setLoading(true);
      setError(null);
      const res = await axios.get(`${API_URL}/participants/duplicates`);
      const data = Array.isArray(res.data) ? res.data : res.data.data;
      setRows(data || []);
    } catch (e) {
      setError(e?.message || 'Gagal memuat data');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const columns = useMemo(() => [
    {
      accessorKey: 'name',
      id: 'name',
      header: ({ column }) => <DataGridColumnHeader title="Nama" column={column} />,
      cell: ({ row }) => row.original.name || '-',
      enableSorting: true,
      meta: { headerClassName: 'min-w-[200px]' },
    },
    {
      accessorKey: 'nik',
      id: 'nik',
      header: ({ column }) => <DataGridColumnHeader title="NIK" column={column} />,
      cell: ({ row }) => row.original.nik || '-',
      enableSorting: true,
      meta: { headerClassName: 'min-w-[180px]' },
    },
    {
      accessorKey: 'count',
      id: 'count',
      header: ({ column }) => <DataGridColumnHeader title="Jumlah" column={column} />,
      cell: ({ row }) => row.original.count || 0,
      enableSorting: true,
      meta: { headerClassName: 'min-w-[80px]' },
    },
    {
      accessorKey: 'participants',
      id: 'participants',
      header: () => <span>Detail Peserta</span>,
      enableSorting: false,
      cell: ({ row }) => (
        <div className="flex flex-col gap-1">
          {(row.original.participants || []).map((p) => (
            <div key={p.id} className="text-xs text-gray-700 flex flex-wrap gap-2">
              <span className="font-semibold">#{p.id}</span>
              <span>{p.name}</span>
              <span>{p.email}</span>
              <span>{p.phone}</span>
              <span>Ticket: {p.ticket_id ?? '-'}</span>
            </div>
          ))}
        </div>
      ),
      meta: { headerClassName: 'min-w-[320px]' },
    },
  ], []);

  const ToolbarContent = () => (
    <div className="card-header flex-wrap gap-2 border-b-0 px-5 w-full">
      <div className="flex items-center gap-2 flex-wrap">
        <button className="btn btn-sm btn-primary" onClick={() => fetchData()} disabled={loading}>
          <KeenIcon icon="magnifier" /> Reload
        </button>
      </div>
    </div>
  );

  return (
    <Fragment>
      {currentLayout?.name === 'demo1-layout' && (
        <Container>
          <Toolbar>
            <ToolbarHeading title="Duplicate Participants" description="Data peserta yang terdeteksi duplikat berdasarkan nama + NIK" />
            <ToolbarActions>
              <button className="btn btn-sm btn-light" onClick={() => fetchData()} disabled={loading}>Refresh</button>
            </ToolbarActions>
          </Toolbar>
        </Container>
      )}

      <Container>
        {error && <div className="text-red-600 mb-2">{error}</div>}
        <DataGrid
          columns={columns}
          data={rows}
          pagination={false}
          toolbar={<ToolbarContent />}
          layout={{ card: true }}
        />
      </Container>
    </Fragment>
  );
};

export default DuplicateParticipantsPage;
