import React, { Fragment, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarDescription, ToolbarHeading, ToolbarPageTitle } from '@/partials/toolbar';
import { useLayout } from '@/providers';
import { DataGrid, DataGridColumnHeader, DataGridRowSelect, DataGridRowSelectAll, KeenIcon } from '@/components';
import { Modal, ModalBody, ModalContent, ModalHeader, ModalTitle } from '@/components/modal';
import { Input } from '@/components/ui/input';

const TransactionsListPage = () => {
  const { currentLayout } = useLayout();
  const [transactions, setTransactions] = useState([]);
  const [meta, setMeta] = useState({ total: 0 });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [search, setSearch] = useState('');
  const [detailOpen, setDetailOpen] = useState(false);
  const [detailLoading, setDetailLoading] = useState(false);
  const [detailError, setDetailError] = useState(null);
  const [detail, setDetail] = useState(null);
  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  const fetchData = async (params = {}) => {
    try {
      setLoading(true);
      setError(null);
      const res = await axios.get(`${API_URL}/transactions`, { params });
      const data = Array.isArray(res.data) ? res.data : res.data.data;
      setTransactions(data || []);
      const pagination = res?.data?.meta || res?.data?.pagination;
      if (pagination) setMeta({ total: pagination.total ?? data?.length ?? 0 });
      else setMeta({ total: data?.length ?? 0 });
    } catch (e) {
      setError('Gagal mengambil data transaksi.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const openDetail = async (id) => {
    try {
      setDetailOpen(true);
      setDetailLoading(true);
      setDetailError(null);
      setDetail(null);
      const res = await axios.get(`${API_URL}/transactions/show`, { params: { id } });
      setDetail(res?.data?.data || null);
    } catch (e) {
      setDetailError('Gagal mengambil detail transaksi.');
    } finally {
      setDetailLoading(false);
    }
  };

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
        meta: { headerClassName: 'w-0' },
      },
      {
        accessorKey: 'invoice',
        id: 'invoice',
        header: ({ column }) => (
          <DataGridColumnHeader title="Invoice" filter={<ColumnInputFilter column={column} />} column={column} />
        ),
        enableSorting: true,
        cell: ({ row }) => {
          const inv = row.original.invoice;
          return inv ? (
            <a href={`${API_URL}/payment/${inv}`} target="_blank" rel="noreferrer" className="text-primary">
              {inv}
            </a>
          ) : '-';
        },
        meta: { headerClassName: 'min-w-[160px]' },
      },
      {
        accessorKey: 'amount',
        id: 'amount',
        header: ({ column }) => <DataGridColumnHeader title="Jumlah" column={column} />,
        enableSorting: true,
        cell: ({ row }) => new Intl.NumberFormat('id-ID').format(row.original.amount || 0),
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorKey: 'status',
        id: 'status',
        header: ({ column }) => <DataGridColumnHeader title="Status" column={column} />,
        enableSorting: true,
        cell: ({ row }) => {
          const s = (row.original.status || '').toLowerCase();
          const cls =
            s === 'success'
              ? 'badge-success'
              : s === 'pending'
              ? 'badge-warning'
              : s === 'failed' || s === 'cancel'
              ? 'badge-danger'
              : 'badge-secondary';
          return <span className={`badge ${cls} badge-outline rounded-[30px]`}>{row.original.status || '-'}</span>;
        },
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorFn: (row) => row,
        id: 'nama',
        header: ({ column }) => <DataGridColumnHeader title="Nama" column={column} />,
        enableSorting: false,
        cell: ({ row }) => row.original?.peserta?.name || row.original?.created_by?.name || '-',
        meta: { headerClassName: 'min-w-[200px]' },
      },
      {
        accessorKey: 'created_at',
        id: 'created_at',
        header: ({ column }) => <DataGridColumnHeader title="Tanggal" column={column} />,
        enableSorting: true,
        cell: ({ row }) => (row.original.created_at ? new Date(row.original.created_at).toLocaleString() : '-'),
        meta: { headerClassName: 'min-w-[180px]' },
      },
      {
        accessorKey: 'actions',
        id: 'actions',
        header: () => <span>Aksi</span>,
        enableSorting: false,
        cell: ({ row }) => (
          <button type="button" className="text-primary underline" onClick={() => openDetail(row.original.id)}>
            Detail
          </button>
        ),
        meta: { headerClassName: 'min-w-[100px]' },
      },
    ],
    []
  );

  const ToolbarContent = () => (
    <div className="card-header flex-wrap gap-2 border-b-0 px-5 w-full">
      <div className="flex items-center gap-2">
        <label className="input input-sm">
          <KeenIcon icon="magnifier" />
          <input
            type="text"
            placeholder="Cari invoice..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') fetchData({ invoice: e.currentTarget.value || undefined });
            }}
          />
        </label>
        <button className="btn btn-sm btn-primary" onClick={() => fetchData({ invoice: search || undefined })} disabled={loading}>
          <KeenIcon icon="magnifier" />
          Cari
        </button>
        {search && (
          <button
            className="btn btn-sm btn-light"
            onClick={() => {
              setSearch('');
              fetchData({ invoice: undefined });
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
    <Fragment>
      {currentLayout?.name === 'demo1-layout' && (
        <Container>
          <Toolbar>
            <ToolbarHeading>
              <ToolbarPageTitle />
              <ToolbarDescription>
                <div className="flex items-center flex-wrap gap-1.5 font-medium">
                  <span className="text-md text-gray-700">Total Transaksi:</span>
                  <span className="text-md text-gray-800 font-medium me-2">{meta.total}</span>
                </div>
              </ToolbarDescription>
            </ToolbarHeading>
            <ToolbarActions>
              <button className="btn btn-sm btn-light" onClick={() => fetchData()} disabled={loading}>
                Refresh
              </button>
            </ToolbarActions>
          </Toolbar>
        </Container>
      )}

      <Container>
        {error && <div className="text-red-600 mb-2">{error}</div>}
        <DataGrid
          columns={columns}
          data={transactions}
          rowSelection={true}
          pagination={{ size: 10 }}
          toolbar={<ToolbarContent />}
          layout={{ card: true }}
        />
      </Container>

      {/* Detail Modal */}
      <Modal open={detailOpen} onClose={() => setDetailOpen(false)}>
        <ModalContent className="max-w-xl">
          <ModalHeader>
            <ModalTitle>Detail Transaksi {detail?.id ? `#${detail.id}` : ''}</ModalTitle>
            <button className="btn btn-sm btn-light" onClick={() => setDetailOpen(false)}>Tutup</button>
          </ModalHeader>
          <ModalBody>
            {detailLoading && <div>Memuat...</div>}
            {detailError && <div className="text-red-600">{detailError}</div>}
            {!detailLoading && !detailError && (
              <div className="space-y-2">
                <div className="flex justify-between"><span className="text-gray-600">Invoice:</span><span className="font-medium">{detail?.invoice || '-'}</span></div>
                <div className="flex justify-between"><span className="text-gray-600">Status:</span><span className="font-medium">{detail?.status || '-'}</span></div>
                <div className="flex justify-between"><span className="text-gray-600">Jumlah:</span><span className="font-medium">{new Intl.NumberFormat('id-ID').format(detail?.amount || 0)}</span></div>
                <div className="flex justify-between"><span className="text-gray-600">Tipe Pembayaran:</span><span className="font-medium">{detail?.payment_type || '-'}</span></div>
                <div className="flex justify-between"><span className="text-gray-600">Dibuat:</span><span className="font-medium">{detail?.created_at || '-'}</span></div>
                <div className="flex justify-between"><span className="text-gray-600">Peserta:</span><span className="font-medium">{detail?.peserta?.name || '-'}</span></div>
              </div>
            )}
          </ModalBody>
        </ModalContent>
      </Modal>
    </Fragment>
  );
};

export default TransactionsListPage;
