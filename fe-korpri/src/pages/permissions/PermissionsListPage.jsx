import { Fragment, useMemo, useState } from 'react';
import axios from 'axios';
import { toast } from 'sonner';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarDescription, ToolbarHeading, ToolbarPageTitle } from '@/partials/toolbar';
import { useLayout } from '@/providers';
import { DataGrid, DataGridColumnHeader, DataGridRowSelect, DataGridRowSelectAll, KeenIcon } from '@/components';
import { Input } from '@/components/ui/input';
import PermissionFormModal from './PermissionFormModal';

export default function PermissionsListPage() {
  const { currentLayout } = useLayout();

  const [headerTotal, setHeaderTotal] = useState(0);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [gridKey, setGridKey] = useState(0);
  const baseUrl = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  const fetchServerItems = async ({ pageIndex, pageSize }) => {
    const { data } = await axios.get(`${baseUrl}/permissions`, {
      params: {
        page: pageIndex + 1,
        per_page: pageSize,
        search: search || undefined,
      },
    });
    const rows = data?.data ?? data ?? [];
    const total = data?.meta?.total ?? rows.length;
    setHeaderTotal(total);
    return { data: rows, totalCount: total };
  };

  const onAdd = () => {
    setEditingItem(null);
    setModalOpen(true);
  };
  const onEdit = (item) => {
    setEditingItem(item);
    setModalOpen(true);
  };
  const onDelete = async (item) => {
    if (!confirm(`Hapus permission ${item.title || item.name}?`)) return;
    try {
      await axios.delete(`${baseUrl}/permissions/${item.id}`);
      toast.success('Permission deleted');
      setGridKey((k) => k + 1);
    } catch (e) {
      console.error('Delete failed', e);
      toast.error('Gagal menghapus permission');
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
        accessorKey: 'title',
        id: 'title',
        header: ({ column }) => (
          <DataGridColumnHeader title="Permission" filter={<ColumnInputFilter column={column} />} column={column} />
        ),
        enableSorting: true,
        cell: ({ row }) => (
          <div className="flex flex-col gap-0.5">
            <span className="text-sm font-medium text-gray-900">{row.original.title || row.original.name}</span>
          </div>
        ),
        meta: { headerClassName: 'min-w-[240px]' },
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
            placeholder="Search permissions"
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
                  <span className="text-md text-gray-700">Total Permissions:</span>
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
              return await fetchServerItems({ pageIndex, pageSize });
            } finally {
              setLoading(false);
            }
          }}
          toolbar={<ToolbarContent />}
          layout={{ card: true }}
        />
      </Container>

      <PermissionFormModal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        editItem={editingItem}
        onSaved={() => setGridKey((k) => k + 1)}
      />
    </Fragment>
  );
}
