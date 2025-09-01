import { Container } from '@/components/container';
import { Toolbar, ToolbarHeading } from '@/layouts/demo1/toolbar';
import { useState } from 'react';

const WithdrawalPage = () => {
  const [form, setForm] = useState({ amount: '', bank: '', accountName: '', accountNumber: '', note: '' });
  const [submitting, setSubmitting] = useState(false);

  const onChange = (e) => {
    const { name, value } = e.target;
    setForm((s) => ({ ...s, [name]: value }));
  };

  const onSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      // TODO: Integrate with API endpoint for withdrawal request
      // Example:
      // await axios.post(`${API_URL}/withdrawals`, form)
      await new Promise((r) => setTimeout(r, 800));
      alert('Withdrawal request submitted');
      setForm({ amount: '', bank: '', accountName: '', accountNumber: '', note: '' });
    } catch (err) {
      alert('Failed to submit withdrawal');
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
                  <input
                    type="text"
                    name="bank"
                    value={form.bank}
                    onChange={onChange}
                    className="input"
                    placeholder="cth: BCA / BRI / Mandiri"
                    required
                  />
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
    </>
  );
};

export default WithdrawalPage;
