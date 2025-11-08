import { Container } from '@/components/container';
import { Toolbar, ToolbarHeading } from '@/layouts/demo1/toolbar';
import { useEffect, useState } from 'react';
import { useAuthContext } from '@/auth';
import axios from 'axios';
import { getAuth } from '@/auth';

const WithdrawalPage = () => {
  const { currentUser } = useAuthContext();
  const [form, setForm] = useState({ amount: '', bank: '', accountName: '', accountNumber: '', note: '' });
  const [submitting, setSubmitting] = useState(false);
  const [summary, setSummary] = useState({ total_income: 0, total_withdrawn: 0, available_balance: 0 });
  const [summaryLoading, setSummaryLoading] = useState(false);
  const [referral, setReferral] = useState({ total_earning: 0, total_withdrawn: 0, available_balance: 0 });
  const [list, setList] = useState([]);
  const [listLoading, setListLoading] = useState(false);
  const [listError, setListError] = useState('');
  const [listTotal, setListTotal] = useState(0);
  const [actionLoadingId, setActionLoadingId] = useState(null);

  const onChange = (e) => {
    const { name, value } = e.target;
    setForm((s) => ({ ...s, [name]: value }));
  };

  const updateStatus = async (row, action) => {
    const title =
      action === 'approved' ? 'Setujui withdrawal ini?' :
        action === 'paid' ? 'Tandai sebagai sudah dibayar?' :
          action === 'rejected' ? 'Tolak withdrawal ini?' :
            action === 'canceled' ? 'Batalkan withdrawal ini?' :
              'Lanjutkan aksi?';
    if (!window.confirm(title)) return;

    const note = window.prompt('Catatan (opsional):', '') || undefined;
    try {
      setActionLoadingId(row.id);
      const auth = getAuth();
      await axios.patch(
        `${API_URL}/withdrawals/${row.id}/status`,
        { action, note },
        { headers: auth?.access_token ? { Authorization: `Bearer ${auth.access_token}` } : undefined }
      );
      await fetchSummary();
      await fetchList();
    } catch (err) {
      const msg = err?.response?.data?.message || 'Gagal memperbarui status';
      alert(msg);
    } finally {
      setActionLoadingId(null);
    }
  };

  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  const fetchSummary = async () => {
    try {
      setSummaryLoading(true);
      // Align total income source with Dashboard: use /total-income
      const auth = getAuth();
      const [incomeRes, summaryRes, referralRes] = await Promise.all([
        axios.get(`${API_URL}/total-income`),
        axios.get(`${API_URL}/withdrawals/summary`),
        axios.get(`${API_URL}/referral/balance`, {
          headers: auth?.access_token ? { Authorization: `Bearer ${auth.access_token}` } : undefined,
        }).catch((e) => ({ data: { data: { total_earning: 0, total_withdrawn: 0, available_balance: 0 } }, __err: e })),
      ]);
      const incomeVal = incomeRes?.data?.total_income ?? 0;
      const s = summaryRes?.data?.data || {};
      const r = referralRes?.data?.data || { total_earning: 0, total_withdrawn: 0, available_balance: 0 };
      const totalIncome = Number(incomeVal) || 0;
      const totalWithdrawn = Number(s.total_withdrawn || 0);
      const availableBase = Math.max(0, totalIncome - totalWithdrawn);
      const referralAvail = Number(r.available_balance || 0);
      const available = Math.max(0, availableBase - referralAvail);
      setSummary({
        total_income: totalIncome,
        total_withdrawn: totalWithdrawn,
        available_balance: available,
      });
      setReferral({
        total_earning: Number(r.total_earning || 0),
        total_withdrawn: Number(r.total_withdrawn || 0),
        available_balance: referralAvail,
      });
    } catch (err) {
      const status = err?.response?.status;
      const msg = err?.response?.data?.message || err?.message || 'Gagal mengambil ringkasan';
      console.error('[withdrawals:summary] error', status, msg, err?.response?.data);
      alert(`Gagal mengambil ringkasan${status ? ` (${status})` : ''}: ${msg}`);
      setSummary({ total_income: 0, total_withdrawn: 0, available_balance: 0 });
      setReferral({ total_earning: 0, total_withdrawn: 0, available_balance: 0 });
    } finally {
      setSummaryLoading(false);
    }
  };

  const fetchList = async () => {
    try {
      setListLoading(true);
      setListError('');
      const auth = getAuth();
      const { data } = await axios.get(`${API_URL}/withdrawals`, {
        params: { per_page: 20 },
        headers: auth?.access_token ? { Authorization: `Bearer ${auth.access_token}` } : undefined,
      });
      console.log('[withdrawals:list] raw response', data);
      const rows = Array.isArray(data) ? data : data?.data?.data || data?.data || [];
      const total = Array.isArray(data) ? rows.length : (data?.data?.total ?? rows.length ?? 0);
      setList(rows);
      setListTotal(total);
    } catch (err) {
      const status = err?.response?.status;
      const msg = err?.response?.data?.message || err?.message || 'Gagal mengambil daftar withdrawal';
      console.error('[withdrawals:list] error', status, msg, err?.response?.data);
      setListError(`${msg}${status ? ` (status ${status})` : ''}`);
      setList([]);
      setListTotal(0);
    } finally {
      setListLoading(false);
    }
  };

  useEffect(() => {
    fetchSummary();
    fetchList();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      const payload = {
        amount: Number.parseInt(form.amount || '0', 10),
        bank: form.bank,
        account_name: form.accountName,
        account_number: form.accountNumber,
        note: form.note || undefined,
      };
      const auth = getAuth();
      await axios.post(`${API_URL}/withdrawals`, payload, {
        headers: auth?.access_token ? { Authorization: `Bearer ${auth.access_token}` } : undefined,
      });
      alert('Withdrawal berhasil diajukan');
      setForm({ amount: '', bank: '', accountName: '', accountNumber: '', note: '' });
      // refresh summary & list
      fetchSummary();
      fetchList();
    } catch (err) {
      const message = err?.response?.data?.message || 'Gagal mengajukan withdrawal';
      const available = err?.response?.data?.data?.available;
      if (available !== undefined) {
        alert(`${message}. Saldo tersedia: ${new Intl.NumberFormat('id-ID').format(available)} IDR`);
      } else {
        alert(message);
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <>
      <Container>
        <Toolbar>
          <ToolbarHeading title="Withdrawal" description="Ajukan penarikan saldo Anda" />
        </Toolbar>
      </Container>

      <Container>
        <div className="card max-w-2xl">
          <div className="card-header">
            <h3 className="card-title">Form Withdrawal</h3>
          </div>
          <div className="card-body">
            <form onSubmit={onSubmit} className="grid gap-4">
              <div>
                <label className="form-label">Jumlah (IDR)</label>
                <input
                  type="number"
                  name="amount"
                  value={form.amount}
                  onChange={onChange}
                  className="input"
                  placeholder="cth: 1000000"
                  min="0"
                  required
                />
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="form-label">Bank</label>
                  <select
                    name="bank"
                    value={form.bank}
                    onChange={onChange}
                    className="input"
                    required
                  >
                    <option value="">Pilih Bank</option>
                    <option value="BCA">BCA</option>
                    <option value="BRI">BRI</option>
                    <option value="BNI">BNI</option>
                    <option value="Mandiri">Mandiri</option>
                    <option value="CIMB Niaga">CIMB Niaga</option>
                    <option value="Danamon">Danamon</option>
                    <option value="Permata">Permata</option>
                    <option value="BTN">BTN</option>
                    <option value="Maybank">Maybank</option>
                    <option value="OCBC NISP">OCBC NISP</option>
                    <option value="Lainnya">Lainnya</option>
                  </select>
                </div>
                <div>
                  <label className="form-label">Nomor Rekening</label>
                  <input
                    type="text"
                    name="accountNumber"
                    value={form.accountNumber}
                    onChange={onChange}
                    className="input"
                    placeholder="cth: 1234567890"
                    required
                  />
                </div>
              </div>
              <div>
                <label className="form-label">Nama Pemilik Rekening</label>
                <input
                  type="text"
                  name="accountName"
                  value={form.accountName}
                  onChange={onChange}
                  className="input"
                  placeholder="cth: Budi Santoso"
                  required
                />
              </div>
              <div>
                <label className="form-label">Catatan (opsional)</label>
                <textarea
                  name="note"
                  value={form.note}
                  onChange={onChange}
                  className="textarea"
                  placeholder="Catatan tambahan"
                  rows={3}
                />
              </div>
              <div className="flex gap-3">
                <button disabled={submitting} type="submit" className="btn btn-primary">
                  {submitting ? 'Mengirim…' : 'Ajukan Withdrawal'}
                </button>
                <button type="button" className="btn btn-light" onClick={() => setForm({ amount: '', bank: '', accountName: '', accountNumber: '', note: '' })}>
                  Reset
                </button>
              </div>
            </form>
          </div>
        </div>
      </Container>
      <br />
      <Container>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          {/* <div className="card">
            <div className="card-body">
              <div className="text-gray-600">Total Pemasukan</div>
              <div className="text-xl font-semibold">{summaryLoading ? '...' : new Intl.NumberFormat('id-ID').format(summary.total_income)} IDR</div>
            </div>
          </div> */}

          <div className="card">
            <div className="card-body">
              <div className="text-gray-600">Saldo Tersedia</div>
              <div className="text-xl font-semibold">{summaryLoading ? '...' : new Intl.NumberFormat('id-ID').format(summary.available_balance)} IDR</div>
            </div>
          </div>

          <div className="card">
            <div className="card-body">
              <div className="text-gray-600">Total Withdrawal</div>
              <div className="text-xl font-semibold">{summaryLoading ? '...' : new Intl.NumberFormat('id-ID').format(summary.total_withdrawn)} IDR</div>
            </div>
          </div>

          <div className="card">
            <div className="card-body">
              <div className="text-gray-600">Total Referral</div>
              <div className="text-xl font-semibold">{summaryLoading ? '...' : new Intl.NumberFormat('id-ID').format(referral.available_balance)} IDR</div>
            </div>
          </div>

        </div>

        <div className="card">
          <div className="card-header">
            <h3 className="card-title">Riwayat Withdrawal Terbaru</h3>
            <div className="card-header-actions">
              <button type="button" className="btn btn-sm btn-light" onClick={() => { fetchSummary(); fetchList(); }} disabled={summaryLoading || listLoading}>
                Refresh
              </button>
            </div>
          </div>
          <div className="card-body">
            {listError && <div className="mb-2 text-red-600">{listError}</div>}
            {listLoading ? (
              <div>Memuat...</div>
            ) : (
              <div className="overflow-x-auto">
                <table className="table">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Jumlah</th>
                      <th>Status</th>
                      <th>Bank</th>
                      <th>Rekening</th>
                      <th>Nama Pemilik Rekening</th>
                      <th>Diajukan Oleh</th>
                    </tr>
                  </thead>
                  <tbody>
                    {list.length === 0 && (
                      <tr>
                        <td colSpan={7} className="text-center text-gray-500">Belum ada data</td>
                      </tr>
                    )}
                    {list.map((row) => (
                      <tr key={row.id}>
                        <td>{row.created_at ? new Date(row.created_at).toLocaleString() : '-'}</td>
                        <td>{new Intl.NumberFormat('id-ID').format(row.amount || 0)}</td>
                        <td>
                          <span className={`badge ${row.status === 'approved' || row.status === 'paid' ? 'badge-success' : row.status === 'rejected' || row.status === 'canceled' ? 'badge-danger' : 'badge-warning'} badge-outline rounded-[30px]`}>
                            {row.status === 'approved' ? 'inprogress' : row.status}
                          </span>
                        </td>
                        <td>{row.bank || '-'}</td>
                        <td>{row.account_number || '-'}</td>
                        <td>{row.created_by?.name || '-'}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
                <div className="text-xs text-gray-500 mt-2">Total baris: {listTotal}</div>
              </div>
            )}
          </div>
        </div>
      </Container>

      {currentUser?.email === 'admin@superadmin.com' && (
        <Container>
          <div className="card">
            <div className="card-header">
              <h3 className="card-title">Aksi Admin</h3>
              <div className="card-header-actions">
                <button type="button" className="btn btn-sm btn-light" onClick={() => { fetchSummary(); fetchList(); }} disabled={summaryLoading || listLoading}>
                  Refresh
                </button>
              </div>
            </div>
            <div className="card-body">
              <div className="overflow-x-auto">
                <table className="table">
                <thead>
                  <tr>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Bank</th>
                    <th>Rekening</th>
                    <th>Nama Pemilik</th>
                    <th>Pemohon</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  {list.length === 0 && (
                    <tr>
                      <td colSpan={8} className="text-center text-gray-500">Belum ada data</td>
                    </tr>
                  )}
                  {list.map((row) => {
                    const canApprove = row.status === 'queued';
                    const canMarkPaid = row.status === 'approved';
                    const canReject = row.status === 'queued' || row.status === 'approved';
                    const canCancel = row.status === 'queued';
                    return (
                      <tr key={`act-${row.id}`}>
                        <td>{row.created_at ? new Date(row.created_at).toLocaleString() : '-'}</td>
                        <td>{new Intl.NumberFormat('id-ID').format(row.amount || 0)}</td>
                        <td>{row.status}</td>
                        <td>{row.bank || '-'}</td>
                        <td>{row.account_number || '-'}</td>
                        <td>{row.created_by?.name || '-'}</td>
                        <td>
                          <div className="flex flex-wrap gap-2">
                            <button disabled={!canApprove || actionLoadingId === row.id} className="btn btn-xs btn-primary" onClick={() => updateStatus(row, 'approved')}>Approve</button>
                            <button disabled={!canMarkPaid || actionLoadingId === row.id} className="btn btn-xs btn-success" onClick={() => updateStatus(row, 'paid')}>Mark Paid</button>
                            <button disabled={!canReject || actionLoadingId === row.id} className="btn btn-xs btn-danger" onClick={() => updateStatus(row, 'rejected')}>Reject</button>
                            <button disabled={!canCancel || actionLoadingId === row.id} className="btn btn-xs btn-light" onClick={() => updateStatus(row, 'canceled')}>Cancel</button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
                </table>
              </div>
            </div>
          </div>
        </Container>
      )}
    </>
  );
};

export default WithdrawalPage;
