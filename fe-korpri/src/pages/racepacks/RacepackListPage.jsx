import { Fragment, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarDescription, ToolbarHeading, ToolbarPageTitle } from '@/partials/toolbar';
import { useLayout } from '@/providers';
import { DataGrid, DataGridColumnHeader, DataGridRowSelect, DataGridRowSelectAll, KeenIcon } from '@/components';
import { Input } from '@/components/ui/input';

export default function RacepackListPage() {
  const { currentLayout } = useLayout();
  const baseUrl = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  const [headerTotal, setHeaderTotal] = useState(0);
  const [totalSudah, setTotalSudah] = useState(0);
  const [totalBelum, setTotalBelum] = useState(0);
  const [loading, setLoading] = useState(false);
  const [gridKey, setGridKey] = useState(0);

  const [status, setStatus] = useState(''); // '', 'sudah', 'belum'
  const [staffName, setStaffName] = useState('');
  const [staffId, setStaffId] = useState('');
  const [staffOptions, setStaffOptions] = useState([]);
  const [search, setSearch] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');

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
      time = time.replace('.', ':');
      const capWeekday = weekday.charAt(0).toUpperCase() + weekday.slice(1);
      return `${capWeekday}, ${day}-${month}-${year} : ${time}`;
    } catch (_) {
      return d.toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
    }
  };

  const fetchServerItems = async ({ pageIndex, pageSize }) => {
    const { data } = await axios.get(`${baseUrl}/racepacks`, {
      params: {
        page: pageIndex + 1,
        per_page: pageSize,
        status: status || undefined,
        staff_name: staffName || undefined,
        staff_id: staffId || undefined,
        search: search || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
      },
    });
    const rows = data?.data ?? [];
    const total = data?.meta?.total ?? rows.length;
    setHeaderTotal(total);
    setTotalSudah(data?.meta?.total_sudah ?? 0);
    setTotalBelum(data?.meta?.total_belum ?? 0);
    return { data: rows, totalCount: total };
  };

  const ColumnInputFilter = ({ column }) => (
    <Input
      placeholder="Filter..."
      value={column.getFilterValue() ?? ''}
      onChange={(event) => column.setFilterValue(event.target.value)}
      className="h-9 w-full max-w-40"
    />
  );

  useEffect(() => {
    const loadStaffs = async () => {
      try {
        const { data } = await axios.get(`${baseUrl}/staffs`);
        setStaffOptions(data || []);
      } catch (e) {
        setStaffOptions([]);
      }
    };
    loadStaffs();
  }, []);

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
        accessorKey: 'participant_id',
        id: 'participant_id',
        header: ({ column }) => (
          <DataGridColumnHeader title="Participant ID" filter={<ColumnInputFilter column={column} />} column={column} />
        ),
        enableSorting: true,
        cell: ({ row }) => row.original.participant_id,
        meta: { headerClassName: 'min-w-[180px]' },
      },
      {
        accessorKey: 'name',
        id: 'name',
        header: ({ column }) => (
          <DataGridColumnHeader title="Name" filter={<ColumnInputFilter column={column} />} column={column} />
        ),
        enableSorting: true,
        cell: ({ row }) => (
          <div className="flex flex-col gap-0.5">
            <span className="text-sm font-medium text-gray-900">{row.original.name}</span>
            <span className="text-2sm text-gray-700">{row.original.email} • {row.original.phone}</span>
          </div>
        ),
        meta: { headerClassName: 'min-w-[260px]' },
      },
      {
        accessorKey: 'status_racepack',
        id: 'status_racepack',
        header: ({ column }) => <DataGridColumnHeader title="Status" column={column} />,
        enableSorting: true,
        cell: ({ row }) => (
          <span className={`badge ${row.original.status_racepack === 'sudah' ? 'badge-success' : 'badge-secondary'} badge-outline rounded-[30px]`}>
            {row.original.status_racepack === 'sudah' ? 'Sudah' : 'Belum'}
          </span>
        ),
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorKey: 'racepack_by',
        id: 'racepack_by',
        header: ({ column }) => <DataGridColumnHeader title="Staff" column={column} />,
        enableSorting: true,
        cell: ({ row }) => row.original.racepack_by || (row.original.staff?.name ?? '-'),
        meta: { headerClassName: 'min-w-[160px]' },
      },
      {
        accessorKey: 'racepack_at',
        id: 'racepack_at',
        header: ({ column }) => <DataGridColumnHeader title="Racepack At" column={column} />,
        enableSorting: true,
        cell: ({ row }) => formatIndoDateTime(row.original.racepack_at),
        meta: { headerClassName: 'min-w-[200px]' },
      },
    ],
    []
  );

  // Memoized toolbar element to prevent remounts on each keystroke (which caused input focus loss)
  const toolbar = useMemo(() => (
    <div data-toolbar className="card-header flex-wrap gap-2 border-b-0 px-5 w-full">
      <div className="flex items-center gap-2 flex-wrap">
        <label className="input input-sm">
          <KeenIcon icon="magnifier" />
          <input
            type="text"
            placeholder="Cari participant, nama, email, phone"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => { if (e.key === 'Enter') setGridKey((k) => k + 1); }}
          />
        </label>
        <select className="select select-sm" value={status} onChange={(e) => setStatus(e.target.value)}>
          <option value="">Semua Status</option>
          <option value="sudah">Sudah</option>
          <option value="belum">Belum</option>
        </select>
        <select className="select select-sm" value={staffId} onChange={(e) => setStaffId(e.target.value)}>
          <option value="">Semua Staff</option>
          {staffOptions.map((s) => (
            <option key={s.id || s.name} value={s.id || ''}>{s.name}</option>
          ))}
        </select>
        <label className="input input-sm">
          <KeenIcon icon="user" />
          <input
            type="text"
            placeholder="Nama Staff"
            value={staffName}
            onChange={(e) => setStaffName(e.target.value)}
            onKeyDown={(e) => { if (e.key === 'Enter') setGridKey((k) => k + 1); }}
          />
        </label>
        <div className="flex items-center gap-2">
          <input className="input input-sm" type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} />
          <span className="text-2sm text-gray-600">s/d</span>
          <input className="input input-sm" type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} />
        </div>
        <button className="btn btn-sm btn-primary" onClick={() => setGridKey((k) => k + 1)} disabled={loading}>
          <KeenIcon icon="magnifier" /> Filter
        </button>
        {(status || staffId || staffName || search || dateFrom || dateTo) && (
          <button
            className="btn btn-sm btn-light"
            onClick={() => { setStatus(''); setStaffId(''); setStaffName(''); setSearch(''); setDateFrom(''); setDateTo(''); setGridKey((k) => k + 1); }}
            disabled={loading}
          >
            Clear
          </button>
        )}
      </div>
    </div>
  ), [search, status, staffId, staffName, staffOptions, dateFrom, dateTo, loading]);

  return (
    <Fragment>
      {currentLayout?.name === 'demo1-layout' && (
        <Container>
          <Toolbar>
            <ToolbarHeading>
              <ToolbarPageTitle />
              <ToolbarDescription>
                <div className="flex items-center flex-wrap gap-1.5 font-medium">
                  <span className="text-md text-gray-700">Total:</span>
                  <span className="text-md text-gray-800 font-medium me-2">{headerTotal}</span>
                  <span className="divider-vertical h-4 mx-2" />
                  <span className="badge badge-success badge-outline rounded-[30px] flex items-center gap-1">
                    <KeenIcon icon="check" /> Sudah: {totalSudah}
                  </span>
                  <span className="badge badge-secondary badge-outline rounded-[30px] flex items-center gap-1">
                    <KeenIcon icon="clock" /> Belum: {totalBelum}
                  </span>
                </div>
              </ToolbarDescription>
            </ToolbarHeading>
            <ToolbarActions>
              <button className="btn btn-sm btn-light" onClick={() => setGridKey((k) => k + 1)} disabled={loading}>Refresh</button>
            </ToolbarActions>
          </Toolbar>
        </Container>
      )}

      <Container>
        <DataGrid
          key={gridKey}
          columns={columns}
          rowSelection={true}
          serverSide={true}
          pagination={{
            size: 10,
            sizes: [10, 25, 50, 100],
            info: 'Menampilkan {from}-{to} dari {count}',
            moreLimit: 5,
          }}
          onFetchData={async ({ pageIndex, pageSize }) => {
            try {
              setLoading(true);
              return await fetchServerItems({ pageIndex, pageSize });
            } finally {
              setLoading(false);
            }
          }}
          toolbar={toolbar}
          layout={{ card: true }}
        />
      </Container>
    </Fragment>
  );
}
