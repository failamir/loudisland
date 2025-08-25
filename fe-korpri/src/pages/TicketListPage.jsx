import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { DataGrid, DataGridColumnHeader } from '@/components/data-grid';
import { Container } from '@/components/container';
import { Input } from '@/components/ui/input';

const TicketListPage = () => {
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [page, setPage] = useState(1);
  const [perPage] = useState(10);
  const [meta, setMeta] = useState({});
  const [search, setSearch] = useState('');
  const [sort, setSort] = useState({ id: 'nama', desc: false });

  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  useEffect(() => {
    setLoading(true);
    setError(null);
    axios
      .get(`${API_URL}/pendaftars`, {
        params: {
          page,
          per_page: perPage,
          search,
          sort: sort.id,
          order: sort.desc ? 'desc' : 'asc',
        },
      })
      .then((response) => {
        setTickets(response.data.data || response.data);
        setMeta(response.data.meta || {});
      })
      .catch(() => {
        setError('Gagal mengambil data tiket');
      })
      .finally(() => setLoading(false));
  }, [page, perPage, search, sort]);

  const columns = useMemo(() => [
    {
      accessorKey: 'id',
      header: ({ column }) => <DataGridColumnHeader title="ID" column={column} />,
      enableSorting: true,
      meta: { headerClassName: 'min-w-[70px]' },
    },
    {
      accessorKey: 'nama',
      header: ({ column }) => <DataGridColumnHeader title="Nama" column={column} filter={<Input placeholder="Cari nama..." value={search} onChange={e => setSearch(e.target.value)} className="h-9 w-full max-w-40" />} />,
      cell: ({ row }) => row.original.nama || row.original.name,
      enableSorting: true,
      meta: { headerClassName: 'min-w-[160px]' },
    },
    {
      accessorKey: 'email',
      header: ({ column }) => <DataGridColumnHeader title="Email" column={column} />,
      enableSorting: true,
      meta: { headerClassName: 'min-w-[180px]' },
    },
    {
      accessorKey: 'status',
      header: ({ column }) => <DataGridColumnHeader title="Status" column={column} />,
      enableSorting: true,
      meta: { headerClassName: 'min-w-[100px]' },
    },
  ], [search]);

  const Toolbar = () => (
    <div className="card-header flex-wrap gap-2 border-b-0 px-5">
      <h3 className="card-title font-medium text-sm">
        {meta.total ? `Showing ${tickets.length} of ${meta.total} tickets` : `Daftar Tiket`}
      </h3>
      <div className="flex flex-wrap gap-2 lg:gap-5">
        <div className="flex">
          <label className="input input-sm">
            <Input
              type="text"
              placeholder="Cari nama..."
              value={search}
              onChange={e => { setSearch(e.target.value); setPage(1); }}
              className="h-9 w-full max-w-40"
            />
          </label>
        </div>
      </div>
    </div>
  );

  return (
    <Container>
      {loading ? (
        <div>Loading...</div>
      ) : error ? (
        <div className="text-red-500">{error}</div>
      ) : (
        <DataGrid
          columns={columns}
          data={tickets}
          pagination={{
            size: perPage,
            page,
            total: meta.total,
            onPageChange: setPage,
            pageCount: meta.last_page || 1,
          }}
          sorting={[sort]}
          onSortingChange={([newSort]) => setSort(newSort)}
          toolbar={<Toolbar />}
          layout={{ card: true }}
          serverSide={true}
        />
      )}
    </Container>
  );
};

export default TicketListPage;
