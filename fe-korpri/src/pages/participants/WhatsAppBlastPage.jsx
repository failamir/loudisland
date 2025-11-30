import React, { Fragment, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarHeading } from '@/partials/toolbar';
import { useLayout } from '@/providers';
import { DataGrid, DataGridColumnHeader, DataGridRowSelectAll, DataGridRowSelect, KeenIcon, useDataGrid } from '@/components';

const WhatsAppBlastPage = () => {
  const { currentLayout } = useLayout();
  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  const [participants, setParticipants] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [message, setMessage] = useState('');
  const [useTemplate, setUseTemplate] = useState(true);
  const [search, setSearch] = useState('');
  const [blastResult, setBlastResult] = useState(null);
  const [selectedCount, setSelectedCount] = useState(0);
  const [source, setSource] = useState('participants'); // participants | transactions
  const [testPhone, setTestPhone] = useState('');
  const [testName, setTestName] = useState('');
  const [testParticipantId, setTestParticipantId] = useState('');
  const [testTicket, setTestTicket] = useState('');
  const [testParticipantEmail, setTestParticipantEmail] = useState('');
  const [testPurchaserEmail, setTestPurchaserEmail] = useState('');
  const [testSending, setTestSending] = useState(false);
  const [testResult, setTestResult] = useState(null);
  const [isInviteMode, setIsInviteMode] = useState(false);

  useEffect(() => {
    loadParticipants();
  }, [source]);

  const loadParticipants = async () => {
    try {
      setLoading(true);
      setError(null);
      if (source.startsWith('participants')) {
        const { data } = await axios.get(`${API_URL}/participants`);
        const items = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
        setParticipants(items);
      } else {
        const { data } = await axios.get(`${API_URL}/transactions/simple`, { params: { status: 'success', per_page: 1000 } });
        const items = Array.isArray(data?.data) ? data.data : [];
        setParticipants(items);
      }
    } catch (e) {
      setError(e?.message || 'Gagal memuat peserta');
    } finally {
      setLoading(false);
    }
  };

  const handleResendFailed = async (tableRef) => {
    try {
      if (!blastResult || !Array.isArray(blastResult.results)) return;
      const failed = blastResult.results.filter(r => r.status !== 'success');
      if (failed.length === 0) return;

      setLoading(true);
      setError(null);
      setBlastResult(null);

      const ids = source.startsWith('participants')
        ? failed.map(r => r.participant_id).filter(Boolean)
        : failed.map(r => r.transaction_id || r.id).filter(Boolean);

      if (ids.length === 0) {
        setError('Tidak ada ID untuk dikirim ulang');
        setLoading(false);
        return;
      }

      const payload = {
        ...(source.startsWith('participants') ? { participant_ids: ids } : { transaction_ids: ids }),
        use_default_template: useTemplate,
        text: useTemplate ? undefined : (message || ''),
        is_invite: isInviteMode,
      };
      const url = source.startsWith('participants')
        ? `${API_URL}/participants/whatsapp-blast`
        : `${API_URL}/transactions/whatsapp-blast`;
      const res = await axios.post(url, payload);
      setBlastResult(res?.data || { status: 'ok' });
    } catch (e) {
      setError(e?.response?.data?.message || e?.message || 'Gagal mengirim ulang');
    } finally {
      setLoading(false);
    }
  };

  const handleEmailBlast = async (tableRef) => {
    try {
      setLoading(true);
      setError(null);
      setBlastResult(null);
      const selectedRows = tableRef.getSelectedRowModel().rows.map(r => r.original);
      const participantIds = selectedRows.map(r => r.participant_id).filter(Boolean);
      if (participantIds.length === 0) {
        setError('Pilih minimal 1 peserta terlebih dahulu');
        setLoading(false);
        return;
      }

      const payload = {
        participant_ids: participantIds,
        use_default_template: useTemplate,
        text: useTemplate ? undefined : (message || ''),
      };
      const res = await axios.post(`${API_URL}/participants/email-blast`, payload);
      setBlastResult(res?.data || { status: 'ok' });
    } catch (e) {
      setError(e?.response?.data?.message || e?.message || 'Gagal mengirim Email');
    } finally {
      setLoading(false);
    }
  };

  const filteredParticipants = useMemo(() => {
    let data = participants;

    if (source.endsWith('_pending')) {
      if (isInviteMode) {
        data = data.filter(p => p.invite != 1); // invite can be null or 0
      } else {
        data = data.filter(p => p.blast == 0);
      }
    }

    if (!search) return data;
    const s = search.toLowerCase();
    if (source.startsWith('participants')) {
      return data.filter(p =>
        (p.name || '').toLowerCase().includes(s) ||
        (p.email || '').toLowerCase().includes(s) ||
        (p.participant_id || '').toLowerCase().includes(s) ||
        (p.phone || '').toLowerCase().includes(s)
      );
    }
    // transactions simple shape
    return data.filter(t =>
      (t.invoice || '').toLowerCase().includes(s) ||
      ((t.event?.nama_event || '').toLowerCase().includes(s)) ||
      (t.event?.id == 1 && 'tiket untuk asn'.includes(s)) ||
      (t.event?.id == 2 && 'tiket untuk umum'.includes(s)) ||
      (String(t.amount || '').toLowerCase().includes(s)) ||
      (String(t.payment_type || '').toLowerCase().includes(s)) ||
      (t.participant_name || '').toLowerCase().includes(s) ||
      (t.participant_phones || '').toLowerCase().includes(s)
    );
  }, [participants, search, source, isInviteMode]);

  const columns = useMemo(() => {
    if (source.startsWith('participants')) {
      return [
        {
          accessorKey: 'select',
          header: () => <DataGridRowSelectAll />,
          cell: ({ row }) => <DataGridRowSelect row={row} />,
          enableSorting: false,
          enableHiding: false,
          meta: { headerClassName: 'w-0' }
        },
        {
          accessorKey: 'participant_id',
          header: ({ column }) => <DataGridColumnHeader title="ID Peserta" column={column} />,
          cell: info => <div className="font-medium text-gray-900">{info.row.original.participant_id}</div>,
          meta: { headerClassName: 'min-w-[160px]' }
        },
        {
          accessorKey: 'name',
          header: ({ column }) => <DataGridColumnHeader title="Nama" column={column} />,
          cell: info => <div className="text-gray-800">{info.row.original.name}</div>,
          meta: { headerClassName: 'min-w-[200px]' }
        },
        {
          accessorKey: 'email',
          header: ({ column }) => <DataGridColumnHeader title="Email" column={column} />,
          cell: info => <div className="text-gray-700">{info.row.original.email}</div>,
          meta: { headerClassName: 'min-w-[220px]' }
        },
        {
          accessorKey: 'phone',
          header: ({ column }) => <DataGridColumnHeader title="No HP" column={column} />,
          cell: info => <div className="text-gray-700">{info.row.original.phone}</div>,
          meta: { headerClassName: 'min-w-[160px]' }
        },
        {
          accessorKey: 'shirt_size',
          header: ({ column }) => <DataGridColumnHeader title="Ukuran Jersey" column={column} />,
          cell: info => <div className="text-gray-700">{info.row.original.shirt_size || info.row.original.shirtSize || '-'}</div>,
          meta: { headerClassName: 'min-w-[120px]' }
        },
        {
          accessorKey: 'blast',
          header: ({ column }) => <DataGridColumnHeader title="Blast" column={column} />,
          cell: info => {
            const val = info.row.original.blast;
            return (
              <span className={`badge badge-sm ${val == 1 ? 'badge-success' : 'badge-light'}`}>
                {val == 1 ? 'Sudah' : 'Belum'}
              </span>
            );
          },
          meta: { headerClassName: 'min-w-[100px]' }
        },
        {
          accessorKey: 'invite',
          header: ({ column }) => <DataGridColumnHeader title="Invite" column={column} />,
          cell: info => {
            const val = info.row.original.invite;
            return (
              <span className={`badge badge-sm ${val == 1 ? 'badge-success' : 'badge-light'}`}>
                {val == 1 ? 'Sudah' : 'Belum'}
              </span>
            );
          },
          meta: { headerClassName: 'min-w-[100px]' }
        }
      ];
    }
    // transactions columns
    return [
      {
        accessorKey: 'select',
        header: () => <DataGridRowSelectAll />,
        cell: ({ row }) => <DataGridRowSelect row={row} />,
        enableSorting: false,
        enableHiding: false,
        meta: { headerClassName: 'w-0' }
      },
      {
        accessorKey: 'invoice',
        header: ({ column }) => <DataGridColumnHeader title="Invoice" column={column} />,
        cell: info => <div className="font-medium text-gray-900">{info.row.original.invoice}</div>,
        meta: { headerClassName: 'min-w-[160px]' }
      },
      {
        accessorKey: 'nama',
        header: ({ column }) => <DataGridColumnHeader title="Nama Pembeli" column={column} />,
        cell: info => <div className="text-gray-800">{info.row.original?.nama || '-'}</div>,
        meta: { headerClassName: 'min-w-[200px]' }
      },
      {
        accessorKey: 'participant_name',
        header: ({ column }) => <DataGridColumnHeader title="Nama Peserta" column={column} />,
        cell: info => <div className="text-gray-800">{info.row.original?.participant_name || '-'}</div>,
        meta: { headerClassName: 'min-w-[200px]' }
      },
      {
        accessorKey: 'no_hp',
        header: ({ column }) => <DataGridColumnHeader title="No HP" column={column} />,
        cell: info => {
          const noHp = info.row.original?.no_hp;
          const participantPhones = info.row.original?.participant_phones;
          return (
            <div className="text-gray-700">
              {noHp || '-'}
              {participantPhones && (
                <div className="text-xs text-gray-500 mt-1">
                  {participantPhones}
                </div>
              )}
            </div>
          );
        },
        meta: { headerClassName: 'min-w-[160px]' }
      },
      {
        accessorKey: 'event',
        header: ({ column }) => <DataGridColumnHeader title="Event" column={column} />,
        cell: info => {
          const event = info.row.original?.event;
          let eventName = event?.nama_event || '-';
          if (event?.id == 1) {
            eventName = 'TIKET UNTUK ASN';
          } else if (event?.id == 2) {
            eventName = 'TIKET UNTUK UMUM';
          } else {
            eventName = event?.nama_event || '-';
          }
          return <div className="text-gray-800">{eventName}</div>;
        },
        meta: { headerClassName: 'min-w-[200px]' }
      },
      {
        accessorKey: 'amount',
        header: ({ column }) => <DataGridColumnHeader title="Harga" column={column} />,
        cell: info => <div className="text-gray-700">{new Intl.NumberFormat('id-ID').format(Number(info.row.original.amount || 0))}</div>,
        meta: { headerClassName: 'min-w-[140px]' }
      },
      {
        accessorKey: 'payment_type',
        header: ({ column }) => <DataGridColumnHeader title="Metode" column={column} />,
        cell: info => <div className="text-gray-700">{info.row.original.payment_type || '-'}</div>,
        meta: { headerClassName: 'min-w-[140px]' }
      },
      {
        accessorKey: 'created_at',
        header: ({ column }) => <DataGridColumnHeader title="Waktu" column={column} />,
        cell: info => <div className="text-gray-700">{info.row.original.created_at || '-'}</div>,
        meta: { headerClassName: 'min-w-[180px]' }
      },
      {
        accessorKey: 'blast',
        header: ({ column }) => <DataGridColumnHeader title="Blast" column={column} />,
        cell: info => {
          const val = info.row.original.blast;
          return (
            <span className={`badge badge-sm ${val == 1 ? 'badge-success' : 'badge-light'}`}>
              {val == 1 ? 'Sudah' : 'Belum'}
            </span>
          );
        },
        meta: { headerClassName: 'min-w-[100px]' }
      }
    ];
  }, [source]);

  const handleRowSelectionChange = (selection, table) => {
    const rows = table.getSelectedRowModel().rows.map(r => r.original);
    setSelectedCount(rows.length);
  };

  // Ambil label tiket dari data peserta jika tersedia
  const getTicketLabel = (row) => {
    return (
      row?.ticket_label ||
      row?.ticket ||
      row?.ticketType ||
      row?.kategori ||
      row?.category ||
      row?.paket ||
      'TIKET UNTUK UMUM'
    );
  };

  const handleBlast = async (tableRef) => {
    try {
      setLoading(true);
      setError(null);
      setBlastResult(null);
      // Ambil peserta terpilih langsung dari tableRef
      const selectedRows = tableRef.getSelectedRowModel().rows.map(r => r.original);
      const ids = source.startsWith('participants')
        ? selectedRows.map(r => r.participant_id).filter(Boolean)
        : selectedRows.map(r => r.id).filter(Boolean);
      if (ids.length === 0) {
        setError('Pilih minimal 1 peserta terlebih dahulu');
        setLoading(false);
        return;
      }
      // Selalu serahkan ke backend (participants atau transactions)
      const payload = {
        ...(source.startsWith('participants') ? { participant_ids: ids } : { transaction_ids: ids }),
        use_default_template: useTemplate,
        text: useTemplate ? undefined : (message || ''),
        is_invite: isInviteMode,
      };
      const url = source.startsWith('participants')
        ? `${API_URL}/participants/whatsapp-blast`
        : `${API_URL}/transactions/whatsapp-blast`;
      const res = await axios.post(url, payload);
      setBlastResult(res?.data || { status: 'ok' });
      // Reload data jika perlu
    } catch (e) {
      setError(e?.response?.data?.message || e?.message || 'Gagal mengirim WhatsApp');
    } finally {
      setLoading(false);
    }
  };

  const handleBlastAll = async () => {
    try {
      setLoading(true);
      setError(null);
      setBlastResult(null);
      const payload = {
        send_all: true,
        search: search || undefined,
        use_default_template: useTemplate,
        text: useTemplate ? undefined : (message || ''),
        is_invite: isInviteMode,
      };
      const url = source.startsWith('participants')
        ? `${API_URL}/participants/whatsapp-blast`
        : `${API_URL}/transactions/whatsapp-blast`;
      const res = await axios.post(url, payload);
      setBlastResult(res?.data || { status: 'ok' });
    } catch (e) {
      setError(e?.response?.data?.message || e?.message || 'Gagal mengirim WhatsApp');
    } finally {
      setLoading(false);
    }
  };

  const handleEmailBlastAll = async () => {
    try {
      setLoading(true);
      setError(null);
      setBlastResult(null);
      const payload = {
        send_all: true,
        search: search || undefined,
        use_default_template: useTemplate,
        text: useTemplate ? undefined : (message || ''),
      };
      const res = await axios.post(`${API_URL}/participants/email-blast`, payload);
      setBlastResult(res?.data || { status: 'ok' });
    } catch (e) {
      setError(e?.response?.data?.message || e?.message || 'Gagal mengirim Email');
    } finally {
      setLoading(false);
    }
  };

  const normalizePhoneLocal = (input) => {
    const digits = String(input || '').replace(/\D/g, '');
    if (!digits) return '';
    if (digits.startsWith('0')) return '62' + digits.slice(1);
    if (!digits.startsWith('62')) return '62' + digits.replace(/^0+/, '');
    return digits;
  };

  const buildWhatsappTicketTextFE = (name, participantId, ticketLabel, participantEmail, purchaserEmail) => {
    const nama = name || 'Peserta';
    const id = participantId || '-';
    const tiket = ticketLabel || '-';
    const emailPeserta = participantEmail || '-';
    const emailPemesan = purchaserEmail || '-';
    return [
      `Halo ${nama} 👋`,
      '',
      'Selamat! Kamu sudah TERDAFTAR sebagai peserta KORPRI RUN MANDALIKA 🎉',
      '',
      `ID Peserta: ${id}`,
      `Tiket: ${tiket}`,
      `Email Peserta: ${emailPeserta}`,
      `Email Pemesan: ${emailPemesan}`,
      '',
      'Silakan tunjukkan QR kamu saat penukaran racepack di lokasi acara ya 🎽🏁',
      '',
      'Untuk info terbaru seputar jadwal, lokasi, dan update penting lainnya, jangan lupa follow Instagram resmi kami 📲:',
      'https://www.instagram.com/korprirun.mandalika/',
      '',
      'Sampai jumpa di Mandalika! 🏃‍♂️🏃‍♀️',
    ].join('\n');
  };

  const handleTestSend = async () => {
    try {
      setTestSending(true);
      setTestResult(null);
      setError(null);

      const chatId = normalizePhoneLocal(testPhone);
      if (!chatId || chatId.length < 8) {
        setError('Nomor WhatsApp tidak valid');
        setTestSending(false);
        return;
      }

      const text = useTemplate
        ? buildWhatsappTicketTextFE(testName, testParticipantId, testTicket, testParticipantEmail, testPurchaserEmail)
        : (message || '').trim();

      if (!text) {
        setError('Pesan tidak boleh kosong');
        setTestSending(false);
        return;
      }

      const res = await axios.post(`${API_URL}/waha/sendText`, {
        chatId,
        text,
      });
      setTestResult({ status: 'ok', data: res?.data });
    } catch (e) {
      setTestResult({ status: 'error', message: e?.response?.data?.message || e?.message || 'Gagal kirim uji coba' });
    } finally {
      setTestSending(false);
    }
  };

  const ToolbarContent = () => {
    // Akses instance table dari DataGrid melalui context hook
    const { table } = useDataGrid();
    const isFiltered = table.getState().columnFilters.length > 0;
    const selected = table.getSelectedRowModel().rows.length;

    return (
      <div className="card-header px-5 py-5 border-b-0 flex-wrap gap-2 items-center">
        <div className="flex items-center gap-2.5">
          <div className="relative">
            <KeenIcon icon="magnifier" className="leading-none text-md text-gray-500 absolute top-1/2 start-0 -translate-y-1/2 ms-3" />
            <input
              type="text"
              placeholder="Cari nama, email, ID, atau HP..."
              className="input input-sm ps-8"
              value={search}
              onChange={e => setSearch(e.target.value)}
            />
          </div>
          <button className="btn btn-sm btn-light" onClick={() => { setSearch(''); }} disabled={loading}>Clear</button>

          <button className="btn btn-sm btn-light" onClick={() => { setSearch(''); }} disabled={loading}>Clear</button>

          {loading && (
            <div className="flex items-center gap-2 text-gray-600 text-sm">
              <KeenIcon icon="loading" className="animate-spin" /> Memproses...
            </div>
          )}
        </div>

        <div className="flex items-center gap-2.5 ml-auto">
          <span className="text-sm text-gray-700">Dipilih: {selected}</span>
          <button className="btn btn-sm btn-light" onClick={() => table.toggleAllPageRowsSelected(true)} disabled={loading}>Pilih semua</button>
          <button className="btn btn-sm btn-outline btn-primary" onClick={() => handleBlast(table)} disabled={loading || selected === 0}>
            <KeenIcon icon="whatsapp" /> Kirim WhatsApp
          </button>
          <button className="btn btn-sm btn-primary" onClick={handleBlastAll} disabled={loading}>
            <KeenIcon icon="whatsapp" /> Kirim semua hasil filter
          </button>
          {blastResult?.failed > 0 && Array.isArray(blastResult?.results) && (
            <button
              className="btn btn-sm btn-outline btn-danger"
              onClick={() => handleResendFailed(table)}
              disabled={loading}
              title="Kirim ulang ke nomor yang gagal pada blast terakhir"
            >
              <KeenIcon icon="refresh" /> Kirim ulang gagal ({blastResult.failed})
            </button>
          )}
          <span className="divider-vertical h-5 mx-2" />
          <button className="btn btn-sm btn-outline" onClick={() => handleEmailBlast(table)} disabled={loading || selected === 0}>
            <KeenIcon icon="paper-plane" /> Kirim Email
          </button>
          <button className="btn btn-sm btn-secondary" onClick={handleEmailBlastAll} disabled={loading}>
            <KeenIcon icon="paper-plane" /> Email semua hasil filter
          </button>
        </div>
      </div>
    );
  };

  return (
    <Fragment>
      {currentLayout?.name === 'demo1-layout' && (
        <Container>
          <Toolbar>
            <ToolbarHeading title="WhatsApp Blast Peserta" description="Kirim pesan WhatsApp ke peserta terpilih" />
            <ToolbarActions>
              <button className="btn btn-sm btn-light" onClick={loadParticipants} disabled={loading}>Refresh</button>
            </ToolbarActions>
          </Toolbar>
        </Container>
      )}

      <Container>
        {error && <div className="text-red-600 mb-3">{error}</div>}
        {blastResult && (
          <div className="mb-3 p-4 rounded bg-green-50 text-green-800">
            <div className="font-medium mb-1">Hasil Blast</div>
            <div>Total: {blastResult.total}</div>
            <div>Sukses: {blastResult.success}</div>
            <div>Gagal: {blastResult.failed}</div>
            {blastResult.skipped !== undefined && <div>Dilewati: {blastResult.skipped}</div>}
            {Array.isArray(blastResult.results) && blastResult.results.length > 0 && (
              <div className="mt-3 max-h-60 overflow-auto pr-2">
                {blastResult.results.map((r, idx) => (
                  <div key={idx} className="text-sm flex items-center gap-2 py-0.5">
                    <span className={r.status === 'success' ? 'text-green-700' : (r.status === 'skipped' ? 'text-yellow-600' : 'text-red-700')}>
                      {r.status === 'success' ? '✔' : (r.status === 'skipped' ? 'SKIP' : '✖')}
                    </span>
                    <span className="text-gray-800">
                      {source.startsWith('participants')
                        ? `${r.participant_id || '-'} · ${r.phone || '-'}`
                        : `${r.invoice || '-'} · ${r.phone || '-'}`}
                    </span>
                    {r.error && <span className="text-gray-600">— {r.error}</span>}
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        <div className="card mb-6">
          <div className="card-header px-5 py-5 border-b-0 flex-wrap gap-2 items-center">
            <h3 className="card-title">Pesan WhatsApp</h3>
            <div className="flex items-center gap-3 ml-auto">
              <div className="flex items-center gap-2">
                <span className="text-sm text-gray-700">Sumber:</span>
                <select className="select select-sm" value={source} onChange={(e) => setSource(e.target.value)} disabled={loading}>
                  <option value="participants">Peserta (Semua)</option>
                  <option value="participants_pending">Peserta (Belum Blast)</option>
                  <option value="transactions">Transaksi sukses (Semua)</option>
                  <option value="transactions_pending">Transaksi sukses (Belum Blast)</option>
                </select>
              </div>
              <label className="switch switch-sm">
                <input
                  name="isInviteMode"
                  type="checkbox"
                  className="order-2"
                  checked={isInviteMode}
                  onChange={e => {
                    const val = e.target.checked;
                    setIsInviteMode(val);
                    if (val) setUseTemplate(false); // Force custom message for invite
                  }}
                />
                <span className="switch-label order-1 font-semibold text-primary">Mode Undangan Grup</span>
              </label>
              <div className="border-l h-6 mx-2"></div>
              <label className="switch switch-sm">
                <input
                  name="useTemplate"
                  type="checkbox"
                  className="order-2"
                  checked={useTemplate}
                  onChange={e => setUseTemplate(e.target.checked)}
                  disabled={isInviteMode}
                />
                <span className="switch-label order-1">Gunakan template sukses pembayaran</span>
              </label>
            </div>
          </div>
          <div className="card-body px-5 py-5">
            <textarea
              className="textarea w-full"
              rows={5}
              placeholder="Tulis pesan custom (jika tidak pakai template)"
              value={message}
              onChange={e => setMessage(e.target.value)}
              disabled={useTemplate}
            />
            <div className="text-xs text-muted mt-2">Jika menggunakan template, sistem akan membuat pesan untuk setiap peserta berdasarkan tiketnya.</div>
          </div>
        </div>

        {/* Form uji kirim satu nomor */}
        <div className="card mb-6">
          <div className="card-header px-5 py-5 border-b-0 flex-wrap gap-2 items-center">
            <h3 className="card-title">Uji Kirim ke Satu Nomor</h3>
          </div>
          <div className="card-body px-5 py-5">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="form-label">Nomor WhatsApp</label>
                <input
                  type="text"
                  className="input w-full"
                  placeholder="Contoh: 08xxxxx atau 62xxxxx"
                  value={testPhone}
                  onChange={e => setTestPhone(e.target.value)}
                />
                <div className="text-xs text-muted mt-1">Akan dinormalisasi ke format 62.</div>
              </div>
              {useTemplate && (
                <div className="grid grid-cols-1 gap-4">
                  <div>
                    <label className="form-label">Nama</label>
                    <input type="text" className="input w-full" value={testName} onChange={e => setTestName(e.target.value)} placeholder="Nama penerima" />
                  </div>
                  <div>
                    <label className="form-label">ID Peserta</label>
                    <input type="text" className="input w-full" value={testParticipantId} onChange={e => setTestParticipantId(e.target.value)} placeholder="Contoh: MKR-1234" />
                  </div>
                  <div>
                    <label className="form-label">Jenis Tiket</label>
                    <input type="text" className="input w-full" value={testTicket} onChange={e => setTestTicket(e.target.value)} placeholder="Contoh: 5K, 10K, Family" />
                  </div>
                  <div>
                    <label className="form-label">Email Peserta</label>
                    <input type="email" className="input w-full" value={testParticipantEmail} onChange={e => setTestParticipantEmail(e.target.value)} placeholder="email peserta (opsional)" />
                  </div>
                  <div>
                    <label className="form-label">Email Pemesan</label>
                    <input type="email" className="input w-full" value={testPurchaserEmail} onChange={e => setTestPurchaserEmail(e.target.value)} placeholder="email pemesan (opsional)" />
                  </div>
                </div>
              )}
            </div>
            <div className="mt-4 flex items-center gap-2">
              <button className="btn btn-primary" onClick={handleTestSend} disabled={testSending}>
                <KeenIcon icon="whatsapp" /> Kirim Uji Coba
              </button>
              {testSending && <span className="text-sm text-muted">Mengirim...</span>}
            </div>
            {testResult && (
              <div className={`mt-3 p-3 rounded ${testResult.status === 'ok' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'}`}>
                {testResult.status === 'ok' ? 'Pesan uji coba terkirim' : `Gagal: ${testResult.message || 'Gagal mengirim'}`}
              </div>
            )}
          </div>
        </div>

        <DataGrid
          columns={columns}
          data={filteredParticipants}
          rowSelection={true}
          onRowSelectionChange={handleRowSelectionChange}
          getRowId={(row) => source.startsWith('participants') ? row.participant_id : row.id}
          pagination={{ size: 50 }}
          toolbar={<ToolbarContent />}
          layout={{ card: true }}
          messages={{ empty: loading ? 'Loading...' : 'Tidak ada data' }}
        />
      </Container>
    </Fragment>
  );
};

export default WhatsAppBlastPage;