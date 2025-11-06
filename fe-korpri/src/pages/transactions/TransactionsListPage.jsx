import React, { Fragment, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarDescription, ToolbarHeading, ToolbarPageTitle } from '@/partials/toolbar';
import { useLayout } from '@/providers';
import { DataGrid, DataGridColumnHeader, DataGridRowSelect, DataGridRowSelectAll, KeenIcon } from '@/components';
import { Modal, ModalBody, ModalContent, ModalHeader, ModalTitle } from '@/components/modal';
import { Input } from '@/components/ui/input';
import { useAuthContext } from '@/auth';

const TransactionsListPage = () => {
  const { currentUser } = useAuthContext();
  const { currentLayout } = useLayout();
  const [transactions, setTransactions] = useState([]);
  const [meta, setMeta] = useState({ total: 0 });
  const [summary, setSummary] = useState({ total: 0, success: 0, pending: 0, expired: 0, success_amount: 0 });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [detailOpen, setDetailOpen] = useState(false);
  const [detailLoading, setDetailLoading] = useState(false);
  const [detailError, setDetailError] = useState(null);
  const [detail, setDetail] = useState(null);
  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  // Format: "hari, dd-mm-yyyy : HH:mm" in Asia/Jakarta
  const formatIndoDateTime = (value) => {
    if (!value) return '-';
    const d = new Date(value);
    try {
      const weekday = new Intl.DateTimeFormat('id-ID', { weekday: 'long', timeZone: 'Asia/Jakarta' }).format(d);
      const day = new Intl.DateTimeFormat('id-ID', { day: '2-digit', timeZone: 'Asia/Jakarta' }).format(d);
      const month = new Intl.DateTimeFormat('id-ID', { month: '2-digit', timeZone: 'Asia/Jakarta' }).format(d);
      const year = new Intl.DateTimeFormat('id-ID', { year: 'numeric', timeZone: 'Asia/Jakarta' }).format(d);
      let time = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Jakarta' }).format(d);
      // Normalize separator to colon (id-ID often uses dot)
      time = time.replace('.', ':');
      const capWeekday = weekday.charAt(0).toUpperCase() + weekday.slice(1);
      return `${capWeekday}, ${day}-${month}-${year} : ${time}`;
    } catch (_) {
      return d.toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
    }
  };

  // PPN only applies for transactions created after 24 Oct 2025 23:59:59 Asia/Jakarta
  const isPPNApplicable = (createdAt) => {
    if (!createdAt) return false;
    try {
      const t = new Date(createdAt);
      // 2025-10-24 23:59:59 Asia/Jakarta equals 2025-10-24 16:59:59Z
      const threshold = new Date('2025-10-24T16:59:59Z');
      return t.getTime() > threshold.getTime();
    } catch (_) {
      return false;
    }
  };

  const fetchData = async (params = {}) => {
    try {
      setLoading(true);
      setError(null);
      // Merge current filters with incoming params
      const finalParams = {
        status: statusFilter || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
        keyword: search || undefined,
        ...params,
      };
      const res = await axios.get(`${API_URL}/transactions`, { params: finalParams });
      const data = Array.isArray(res.data) ? res.data : res.data.data;
      setTransactions(data || []);
      const pagination = res?.data?.meta || res?.data?.pagination;
      if (pagination) setMeta({ total: pagination.total ?? data?.length ?? 0 });
      else setMeta({ total: data?.length ?? 0 });
      const sum = res?.data?.summary;
      if (sum) setSummary({
        total: Number(sum.total ?? 0),
        success: Number(sum.success ?? 0),
        pending: Number(sum.pending ?? 0),
        expired: Number(sum.expired ?? 0),
        success_amount: Number(sum.success_amount ?? 0),
      });
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

  // Open detail by invoice using payment status endpoint, includes participants
  const openInvoiceDetail = async (invoice) => {
    try {
      setDetailOpen(true);
      setDetailLoading(true);
      setDetailError(null);
      setDetail(null);
      const res = await axios.get(`${API_URL}/payment/${invoice}`);
      // Shape into similar structure with participants support
      const p = res?.data;
      if (p) {
        setDetail({
          id: undefined,
          invoice: p.invoice,
          status: p.status,
          amount: p.amount,
          created_at: undefined,
          peserta: p.user ? { name: p.user.nama, email: p.user.email } : null,
          participants: Array.isArray(p.participants) ? p.participants : [],
        });
      } else {
        setDetail(null);
      }
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
        accessorKey: 'created_at',
        id: 'created_at',
        header: ({ column }) => <DataGridColumnHeader title="Tanggal" column={column} />,
        enableSorting: true,
        cell: ({ row }) => formatIndoDateTime(row.original.created_at),
        meta: { headerClassName: 'min-w-[180px]' },
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
            <button type="button" className="text-primary underline" onClick={() => openInvoiceDetail(inv)}>
              {inv}
            </button>
          ) : '-';
        },
        meta: { headerClassName: 'min-w-[160px]' },
      },
      {
        accessorFn: (row) => row,
        id: 'nama',
        header: ({ column }) => <DataGridColumnHeader title="Nama Pemesan" column={column} />,
        enableSorting: false,
        cell: ({ row }) => {
          const name = row.original?.peserta?.name || row.original?.created_by?.name;
          return name ? String(name).toUpperCase() : '-';
        },
        meta: { headerClassName: 'min-w-[200px]' },
      },
      {
        accessorKey: 'amount',
        id: 'amount',
        header: ({ column }) => <DataGridColumnHeader title="Total Harga" column={column} />,
        enableSorting: true,
        cell: ({ row }) => new Intl.NumberFormat('id-ID').format(row.original.amount || 0),
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorFn: (row) => row?.amount ?? 0,
        id: 'ppn',
        header: ({ column }) => <DataGridColumnHeader title="PPN (11%)" column={column} />,
        enableSorting: false,
        cell: ({ row }) => {
          const amt = Number(row.original?.amount || 0);
          const ppn = isPPNApplicable(row.original?.created_at) ? Math.round(amt * 0.11) : 0;
          return new Intl.NumberFormat('id-ID').format(ppn);
        },
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorFn: (row) => row?.amount ?? 0,
        id: 'total_bayar',
        header: ({ column }) => <DataGridColumnHeader title="Total Bayar" column={column} />,
        enableSorting: false,
        cell: ({ row }) => {
          const amt = Number(row.original?.amount || 0);
          const ppn = isPPNApplicable(row.original?.created_at) ? Math.round(amt * 0.11) : 0;
          const total = amt + ppn;
          return new Intl.NumberFormat('id-ID').format(total);
        },
        meta: { headerClassName: 'min-w-[160px]' },
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
      <div className="flex items-center gap-2 flex-wrap">
        {/* Keyword: invoice or participant name */}
        <label className="input input-sm">
          <KeenIcon icon="magnifier" />
          <input
            type="text"
            placeholder="Cari invoice / nama peserta..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') fetchData();
            }}
          />
        </label>

        {/* Status filter */}
        <select
          className="select select-sm"
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
        >
          <option value="">Semua Status</option>
          <option value="success">Success</option>
          <option value="pending">Pending</option>
          <option value="expired">Expired</option>
          <option value="failed">Failed</option>
          <option value="cancel">Cancel</option>
        </select>

        {/* Date range */}
        <input
          type="date"
          className="input input-sm"
          value={dateFrom}
          onChange={(e) => setDateFrom(e.target.value)}
        />
        <span className="text-gray-500">s/d</span>
        <input
          type="date"
          className="input input-sm"
          value={dateTo}
          onChange={(e) => setDateTo(e.target.value)}
        />

        <button className="btn btn-sm btn-primary" onClick={() => fetchData()} disabled={loading}>
          <KeenIcon icon="magnifier" />
          Cari
        </button>
        <button
          className="btn btn-sm btn-light"
          onClick={() => {
            setSearch('');
            setStatusFilter('');
            setDateFrom('');
            setDateTo('');
            fetchData({ status: undefined, date_from: undefined, date_to: undefined, keyword: undefined, invoice: undefined });
          }}
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
            <ToolbarHeading>
              <ToolbarPageTitle />
              <ToolbarDescription>
                <div className="flex items-center flex-wrap gap-1.5 font-medium">
                  <span className="text-md text-gray-700">Total Transaksi:</span>
                  <span className="text-md text-gray-800 font-medium me-2">{meta.total}</span>
                  <span className="hidden md:inline text-md text-gray-600">|</span>
                  <span className="text-md text-gray-700 ms-1">Success:</span>
                  <span className="badge badge-light-success font-medium me-2">{summary.success}</span>
                  <span className="text-md text-gray-700">Pending:</span>
                  <span className="badge badge-light-warning font-medium me-2">{summary.pending}</span>
                  <span className="text-md text-gray-700">Expired:</span>
                  <span className="badge badge-light-danger font-medium me-3">{summary.expired}</span>
                  {currentUser?.email === 'admin@superadmin.com' && (
                    <>
                      <span className="text-md text-gray-700">Total Amount Success:</span>
                  <span className="font-semibold">
                    {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(summary.success_amount || 0)}
                      </span>
                    </>
                  )}
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
              <div className="space-y-3">
                <div className="grid grid-cols-2 gap-2">
                  <div className="flex justify-between"><span className="text-gray-600">Invoice:</span><span className="font-medium">{detail?.invoice || '-'}</span></div>
                  <div className="flex justify-between"><span className="text-gray-600">Status:</span><span className="font-medium">{detail?.status || '-'}</span></div>
                  <div className="flex justify-between"><span className="text-gray-600">Jumlah:</span><span className="font-medium">{new Intl.NumberFormat('id-ID').format(detail?.amount || 0)}</span></div>
                  <div className="flex justify-between"><span className="text-gray-600">Dibuat:</span><span className="font-medium">{formatIndoDateTime(detail?.created_at)}</span></div>
                  <div className="flex justify-between col-span-2"><span className="text-gray-600">Pemesan:</span><span className="font-medium">{detail?.peserta?.name ? String(detail.peserta.name).toUpperCase() : '-'}</span></div>
                </div>

                {Array.isArray(detail?.participants) && detail.participants.length > 0 && (
                  <div>
                    <div className="font-semibold mb-1">Participants</div>
                    <div className="border rounded-md divide-y">
                      {detail.participants.map((p, idx) => (
                        <div key={idx} className="p-2 text-sm space-y-1">
                          <div className="flex justify-between"><span className="text-gray-600">Nama</span><span className="font-medium">{p?.name ? String(p.name).toUpperCase() : '-'}</span></div>
                          <div className="flex justify-between"><span className="text-gray-600">NIK</span><span>{p.nik || '-'}</span></div>
                          <div className="flex justify-between"><span className="text-gray-600">Email</span><span>{p.email || '-'}</span></div>
                          <div className="flex justify-between"><span className="text-gray-600">Phone</span><span>{p.phone || '-'}</span></div>
                          <div className="flex justify-between"><span className="text-gray-600">Ticket ID</span><span>{p.ticket_id || '-'}</span></div>
                          <div className="flex justify-between"><span className="text-gray-600">Status Racepack</span><span>{p.status_racepack || '-'}</span></div>
                          {p.qr_url && (
                            <div className="flex items-center gap-2"><span className="text-gray-600">QR</span><a className="text-primary underline" href={p.qr_url} target="_blank" rel="noreferrer">Lihat</a></div>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            )}
          </ModalBody>
        </ModalContent>
      </Modal>
    </Fragment>
  );
};

export default TransactionsListPage;
