import React, { useState, useEffect } from 'react';
import { KeenIcon } from '@/components';
import {
  createOmnichannelBroadcast,
  getOmnichannelBroadcastHistory,
} from '@/api/omnichannel';

const BroadcastModal = ({ isOpen, onClose }) => {
  const [history, setHistory] = useState([]);
  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [form, setForm] = useState({
    title: '',
    message: '',
    target_category: 'all',
  });
  const [activeTab, setActiveTab] = useState('create'); // 'create' or 'history'

  useEffect(() => {
    if (isOpen) {
      fetchHistory();
    }
  }, [isOpen]);

  const fetchHistory = async () => {
    setLoading(true);
    const res = await getOmnichannelBroadcastHistory();
    setLoading(false);
    if (res.ok && res.data?.data) {
      setHistory(res.data.data);
    }
  };

  const handleSend = async (e) => {
    e.preventDefault();
    if (!form.title || !form.message || submitting) return;

    setSubmitting(true);
    const res = await createOmnichannelBroadcast(form);
    setSubmitting(false);

    if (res.ok) {
      alert('Broadcast berhasil dikirim!');
      setForm({ title: '', message: '', target_category: 'all' });
      setActiveTab('history');
      fetchHistory();
    } else {
      alert('Gagal mengirim broadcast');
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div className="card w-full max-w-2xl bg-white dark:bg-coal-600 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        <div className="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg bg-green-500/20 text-green-600 flex items-center justify-center">
              <KeenIcon icon="whatsapp" />
            </div>
            <div>
              <h3 className="font-bold text-sm text-gray-900 dark:text-white">
                Broadcast WhatsApp Massal
              </h3>
              <p className="text-[11px] text-gray-500">
                Kirim pengumuman resmi ke seluruh nomor peserta terdaftar
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="btn btn-xs btn-icon btn-light rounded-full text-gray-400 hover:text-gray-700"
          >
            ✕
          </button>
        </div>

        {/* Tab Selector */}
        <div className="flex border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-coal-700/30 px-4 pt-2 gap-4 text-xs font-semibold">
          <button
            onClick={() => setActiveTab('create')}
            className={`pb-2 border-b-2 transition-colors ${
              activeTab === 'create'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-800'
            }`}
          >
            Buat Pesan Broadcast
          </button>
          <button
            onClick={() => setActiveTab('history')}
            className={`pb-2 border-b-2 transition-colors ${
              activeTab === 'history'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-800'
            }`}
          >
            Riwayat Pengiriman ({history.length})
          </button>
        </div>

        <div className="p-5 max-h-[480px] overflow-y-auto">
          {activeTab === 'create' ? (
            <form onSubmit={handleSend} className="space-y-4 text-xs">
              <div>
                <label className="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Judul Kampanye Broadcast
                </label>
                <input
                  type="text"
                  required
                  placeholder="Contoh: Pengingat Pengambilan Racepack Hari Ini"
                  value={form.title}
                  onChange={(e) => setForm({ ...form, title: e.target.value })}
                  className="input input-sm w-full rounded-lg"
                />
              </div>

              <div>
                <label className="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Kategori Target Peserta
                </label>
                <select
                  value={form.target_category}
                  onChange={(e) => setForm({ ...form, target_category: e.target.value })}
                  className="select select-sm w-full rounded-lg"
                >
                  <option value="all">Semua Kategori (5K, 10K, 21K)</option>
                  <option value="5K">Khusus Pelari 5K Fun Run</option>
                  <option value="10K">Khusus Pelari 10K Race</option>
                  <option value="21K">Khusus Pelari Half Marathon 21K</option>
                </select>
              </div>

              <div>
                <label className="block font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Isi Pesan WhatsApp
                </label>
                <textarea
                  rows={5}
                  required
                  placeholder="Ketik isi pengumuman... Anda dapat menggunakan format bold *teks* atau emoji."
                  value={form.message}
                  onChange={(e) => setForm({ ...form, message: e.target.value })}
                  className="textarea textarea-sm w-full rounded-lg"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={onClose}
                  className="btn btn-sm btn-light"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={submitting}
                  className="btn btn-sm btn-primary"
                >
                  {submitting ? 'Mengirim...' : 'Kirim Sekarang'}
                </button>
              </div>
            </form>
          ) : (
            <div className="space-y-3">
              {loading ? (
                <div className="text-center py-8 text-xs text-gray-400">Memuat riwayat...</div>
              ) : history.length === 0 ? (
                <div className="text-center py-8 text-xs text-gray-400">Belum ada riwayat broadcast</div>
              ) : (
                history.map((item) => (
                  <div
                    key={item.id}
                    className="p-3 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-coal-700/40 text-xs space-y-1"
                  >
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-gray-900 dark:text-white">{item.title}</span>
                      <span className="badge badge-xs badge-success uppercase">{item.status}</span>
                    </div>
                    <p className="text-gray-600 dark:text-gray-300">{item.message}</p>
                    <div className="flex items-center justify-between text-[10px] text-gray-400 pt-1">
                      <span>Terkirim: {item.success_count} / {item.total_recipients} nomor</span>
                      <span>{item.created_at}</span>
                    </div>
                  </div>
                ))
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default BroadcastModal;

