// Silakan isi komponen sesuai kebutuhan
import { Fragment, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarDescription, ToolbarHeading, ToolbarPageTitle } from '@/partials/toolbar';
import { useLayout } from '@/providers';
import { DataGrid, DataGridColumnHeader, DataGridRowSelect, DataGridRowSelectAll, KeenIcon } from '@/components';
import { Input } from '@/components/ui/input';

export default function UsersListPage() {
  const { currentLayout } = useLayout();

  const [users, setUsers] = useState([]);
  const [meta, setMeta] = useState({ total: 0 });
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState('');
  const baseUrl = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';
  const fetchUsers = async (params = {}) => {
    try {
      setLoading(true);
      const { data } = await axios.get(`${baseUrl}/users`, {
        params: {
          per_page: 50,
          search: params.search ?? (search || undefined),
        },
      });
      setUsers(data?.data ?? []);
      setMeta(data?.meta ?? { total: 0 });
    } catch (e) {
      console.error('Failed to fetch users', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchUsers();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

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
        accessorKey: 'created_at',
        id: 'created_at',
        header: ({ column }) => <DataGridColumnHeader title="Created" column={column} />,
        enableSorting: true,
        cell: ({ row }) => new Date(row.original.created_at).toLocaleString(),
        meta: { headerClassName: 'min-w-[180px]' },
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
              if (e.key === 'Enter') fetchUsers({ search: e.currentTarget.value });
            }}
          />
        </label>
        <button className="btn btn-sm btn-primary" onClick={() => fetchUsers({ search })} disabled={loading}>
          <KeenIcon icon="magnifier" />
          Search
        </button>
        {search && (
          <button
            className="btn btn-sm btn-light"
            onClick={() => {
              setSearch('');
              fetchUsers({ search: '' });
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
                  <span className="text-md text-gray-800 font-medium me-2">{meta.total}</span>
                </div>
              </ToolbarDescription>
            </ToolbarHeading>
            <ToolbarActions>
              <button className="btn btn-sm btn-light" onClick={() => fetchUsers()} disabled={loading}>
                Refresh
              </button>
            </ToolbarActions>
          </Toolbar>
        </Container>
      )}

      <Container>
        <DataGrid
          columns={columns}
          data={users}
          rowSelection={true}
          pagination={{ size: 10 }}
          toolbar={<ToolbarContent />}
          layout={{ card: true }}
        />
      </Container>
    </Fragment>
  );
}