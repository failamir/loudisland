// Silakan isi komponen sesuai kebutuhan
import { Fragment, useMemo, useState } from 'react';
import axios from 'axios';
import { toast } from 'sonner';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarDescription, ToolbarHeading, ToolbarPageTitle } from '@/partials/toolbar';
import { useLayout } from '@/providers';
import { DataGrid, DataGridColumnHeader, DataGridRowSelect, DataGridRowSelectAll, KeenIcon } from '@/components';
import { Input } from '@/components/ui/input';
import UsersFormModal from './UsersFormModal';

export default function UsersListPage() {
  const { currentLayout } = useLayout();

  const [headerTotal, setHeaderTotal] = useState(0);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState(null);
  const [gridKey, setGridKey] = useState(0);
  const baseUrl = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';
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
  const fetchServerUsers = async ({ pageIndex, pageSize }) => {
    const { data } = await axios.get(`${baseUrl}/users`, {
      params: {
        page: pageIndex + 1,
        per_page: pageSize,
        search: search || undefined,
      },
    });
    const rows = data?.data ?? [];
    const total = data?.meta?.total ?? rows.length;
    setHeaderTotal(total);
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

  const onAdd = () => {
    setEditingUser(null);
    setModalOpen(true);
  };

  const onEdit = (user) => {
    setEditingUser(user);
    setModalOpen(true);
  };

  const onDelete = async (user) => {
    if (!confirm(`Hapus user ${user.name}?`)) return;
    try {
      await axios.delete(`${baseUrl}/users/${user.id}`);
      toast.success('User deleted');
      setGridKey((k) => k + 1); // reload grid
    } catch (e) {
      console.error('Delete failed', e);
      toast.error('Gagal menghapus user');
    }
  };

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
        accessorFn: (row) => row,
        id: 'user',
        header: ({ column }) => (
          <DataGridColumnHeader title="User" filter={<ColumnInputFilter column={column} />} column={column} />
        ),
        enableSorting: false,
        cell: ({ row }) => {
          const u = row.original;
          return (
            <div className="flex flex-col gap-0.5">
              <span className="text-sm font-medium text-gray-900">{u.name}</span>
              <span className="text-2sm text-gray-700">{u.email}</span>
            </div>
          );
        },
        meta: {
          className: 'min-w-[260px]',
          cellClassName: 'text-gray-800 font-normal',
        },
      },
      {
        accessorKey: 'approved',
        id: 'approved',
        header: ({ column }) => <DataGridColumnHeader title="Approved" column={column} />,
        enableSorting: true,
        cell: ({ row }) => (
          <span
            className={`badge ${row.original.approved ? 'badge-success' : 'badge-secondary'} badge-outline rounded-[30px]`}
          >
            {row.original.approved ? 'Approved' : 'Pending'}
          </span>
        ),
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorKey: 'roles',
        id: 'roles',
        header: ({ column }) => <DataGridColumnHeader title="Roles" column={column} />,
        enableSorting: false,
        cell: ({ row }) => (row.original.roles || []).map((r) => r.title || r.name).join(', '),
        meta: { headerClassName: 'min-w-[160px]' },
      },
      {
        accessorKey: 'nik',
        id: 'nik',
        header: ({ column }) => <DataGridColumnHeader title="NIK" column={column} />,
        enableSorting: true,
        cell: ({ row }) => row.original.nik || '-',
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorKey: 'no_hp',
        id: 'no_hp',
        header: ({ column }) => <DataGridColumnHeader title="No HP" column={column} />,
        enableSorting: true,
        cell: ({ row }) => row.original.no_hp || '-',
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorKey: 'uid',
        id: 'uid',
        header: ({ column }) => <DataGridColumnHeader title="UID" column={column} />,
        enableSorting: false,
        cell: ({ row }) => row.original.uid || '-',
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorKey: 'device_name',
        id: 'device_name',
        header: ({ column }) => <DataGridColumnHeader title="Device" column={column} />,
        enableSorting: false,
        cell: ({ row }) => row.original.device_name || '-',
        meta: { headerClassName: 'min-w-[160px]' },
      },
      {
        accessorKey: 'region',
        id: 'region',
        header: ({ column }) => <DataGridColumnHeader title="Region" column={column} />,
        enableSorting: true,
        cell: ({ row }) => row.original.region || '-',
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorKey: 'village',
        id: 'village',
        header: ({ column }) => <DataGridColumnHeader title="Village" column={column} />,
        enableSorting: true,
        cell: ({ row }) => row.original.village || '-',
        meta: { headerClassName: 'min-w-[140px]' },
      },
      {
        accessorKey: 'created_at',
        id: 'created_at',
        header: ({ column }) => <DataGridColumnHeader title="Created" column={column} />,
        enableSorting: true,
        cell: ({ row }) => formatIDDate(row.original.created_at),
        meta: { headerClassName: 'min-w-[180px]' },
      },
      {
        id: 'actions',
        header: ({ column }) => <DataGridColumnHeader title="Actions" column={column} />,
        enableSorting: false,
        cell: ({ row }) => (
          <div className="flex gap-1">
            <button className="btn btn-xs btn-light" onClick={() => onEdit(row.original)}>
              <KeenIcon icon="pencil" /> Edit
            </button>
            <button className="btn btn-xs btn-danger" onClick={() => onDelete(row.original)}>
              <KeenIcon icon="trash" /> Hapus
            </button>
          </div>
        ),
        meta: { headerClassName: 'min-w-[160px]' },
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
            placeholder="Search users"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') setGridKey((k) => k + 1);
            }}
          />
        </label>
        <button className="btn btn-sm btn-primary" onClick={() => setGridKey((k) => k + 1)} disabled={loading}>
          <KeenIcon icon="magnifier" />
          Search
        </button>
        <button className="btn btn-sm btn-success" onClick={onAdd}>
          <KeenIcon icon="plus" /> Tambah
        </button>
        {search && (
          <button
            className="btn btn-sm btn-light"
            onClick={() => {
              setSearch('');
              setGridKey((k) => k + 1);
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
                  <span className="text-md text-gray-700">Total Users:</span>
                  <span className="text-md text-gray-800 font-medium me-2">{headerTotal}</span>
                </div>
              </ToolbarDescription>
            </ToolbarHeading>
            <ToolbarActions>
              <button className="btn btn-sm btn-light" onClick={() => setGridKey((k) => k + 1)} disabled={loading}>
                Refresh
              </button>
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
              return await fetchServerUsers({ pageIndex, pageSize });
            } finally {
              setLoading(false);
            }
          }}
          toolbar={<ToolbarContent />}
          layout={{ card: true }}
        />
      </Container>

      <UsersFormModal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        editUser={editingUser}
        onSaved={() => setGridKey((k) => k + 1)}
      />
    </Fragment>
  );
}