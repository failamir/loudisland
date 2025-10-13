import { useState, useEffect } from 'react';
import axios from 'axios';

const API_URL = import.meta.env.VITE_APP_API_URL || 'https://mandalikakorprirun.com/api/v1';
const SHIRT_SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

export default function UpdateShirtSizePage() {
  const [participants, setParticipants] = useState([]);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState('');
  const [updates, setUpdates] = useState({});
  const [message, setMessage] = useState(null);

  useEffect(() => {
    loadParticipants();
  }, []);

  const loadParticipants = async () => {
    try {
      setLoading(true);
      // Get all participants without shirt_size or all participants
      const { data } = await axios.get(`${API_URL}/participants`);
      const items = Array.isArray(data) ? data : (data?.data || []);
      setParticipants(items);
    } catch (error) {
      console.error('Failed to load participants', error);
      setMessage({ type: 'error', text: 'Gagal memuat data peserta' });
    } finally {
      setLoading(false);
    }
  };

  const handleShirtSizeChange = (participantId, size) => {
    setUpdates(prev => ({
      ...prev,
      [participantId]: size
    }));
  };

  const handleSingleUpdate = async (participant) => {
    const size = updates[participant.participant_id];
    if (!size) {
      alert('Pilih ukuran baju terlebih dahulu');
      return;
    }

    try {
      setLoading(true);
      await axios.put(
        `${API_URL}/participants/${participant.participant_id}/shirt-size`,
        { shirt_size: size }
      );
      
      setMessage({ type: 'success', text: `Ukuran baju ${participant.name} berhasil diupdate` });
      
      // Update local state
      setParticipants(prev => prev.map(p => 
        p.participant_id === participant.participant_id 
          ? { ...p, shirt_size: size }
          : p
      ));
      
      // Clear update for this participant
      setUpdates(prev => {
        const newUpdates = { ...prev };
        delete newUpdates[participant.participant_id];
        return newUpdates;
      });
    } catch (error) {
      console.error('Update failed', error);
      setMessage({ type: 'error', text: 'Gagal mengupdate ukuran baju' });
    } finally {
      setLoading(false);
    }
  };

  const handleBulkUpdate = async () => {
    const updateArray = Object.entries(updates)
      .filter(([_, size]) => size) // Only include non-empty sizes
      .map(([participant_id, shirt_size]) => ({ participant_id, shirt_size }));

    if (updateArray.length === 0) {
      alert('Tidak ada perubahan untuk disimpan');
      return;
    }

    if (!confirm(`Update ${updateArray.length} peserta?`)) {
      return;
    }

    try {
      setLoading(true);
      const { data } = await axios.post(
        `${API_URL}/participants/bulk-update-shirt-size`,
        { updates: updateArray }
      );

      setMessage({ 
        type: 'success', 
        text: `Berhasil update ${data.updated_count} peserta` 
      });

      // Reload participants
      await loadParticipants();
      setUpdates({});
    } catch (error) {
      console.error('Bulk update failed', error);
      setMessage({ type: 'error', text: 'Gagal melakukan bulk update' });
    } finally {
      setLoading(false);
    }
  };

  const filteredParticipants = participants.filter(p => {
    if (!search) return true;
    const searchLower = search.toLowerCase();
    return (
      p.name?.toLowerCase().includes(searchLower) ||
      p.participant_id?.toLowerCase().includes(searchLower) ||
      p.email?.toLowerCase().includes(searchLower)
    );
  });

  return (
    <div className="container mx-auto p-6">
      <h1 className="text-2xl font-semibold mb-6">Update Ukuran Baju Peserta</h1>

      {message && (
        <div className={`mb-4 p-4 rounded ${message.type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
          {message.text}
        </div>
      )}

      <div className="mb-4 flex gap-4 items-center">
        <input
          type="text"
          placeholder="Cari nama, email, atau ID peserta..."
          className="flex-1 px-4 py-2 border rounded"
          value={search}
          onChange={e => setSearch(e.target.value)}
        />
        <button
          onClick={handleBulkUpdate}
          disabled={loading || Object.keys(updates).length === 0}
          className="px-6 py-2 bg-blue-600 text-white rounded disabled:opacity-50 hover:bg-blue-700"
        >
          Simpan Semua ({Object.keys(updates).length})
        </button>
      </div>

      {loading && <p className="text-center py-4">Loading...</p>}

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Peserta</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ukuran Saat Ini</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ukuran Baru</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {filteredParticipants.map(participant => (
              <tr key={participant.participant_id}>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {participant.participant_id}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {participant.name}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {participant.email}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {participant.shirt_size || '-'}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm">
                  <select
                    className="px-3 py-1 border rounded"
                    value={updates[participant.participant_id] || participant.shirt_size || ''}
                    onChange={e => handleShirtSizeChange(participant.participant_id, e.target.value)}
                  >
                    <option value="">Pilih Ukuran</option>
                    {SHIRT_SIZES.map(size => (
                      <option key={size} value={size}>{size}</option>
                    ))}
                  </select>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm">
                  <button
                    onClick={() => handleSingleUpdate(participant)}
                    disabled={loading || !updates[participant.participant_id]}
                    className="px-3 py-1 bg-green-600 text-white rounded text-xs disabled:opacity-50 hover:bg-green-700"
                  >
                    Update
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {filteredParticipants.length === 0 && !loading && (
          <div className="text-center py-8 text-gray-500">
            Tidak ada data peserta
          </div>
        )}
      </div>

      <div className="mt-6 p-4 bg-blue-50 rounded">
        <h3 className="font-semibold mb-2">Petunjuk:</h3>
        <ul className="list-disc list-inside text-sm text-gray-700 space-y-1">
          <li>Pilih ukuran baju untuk setiap peserta dari dropdown</li>
          <li>Klik "Update" untuk menyimpan satu peserta, atau</li>
          <li>Klik "Simpan Semua" untuk menyimpan semua perubahan sekaligus</li>
          <li>Ukuran yang tersedia: XS, S, M, L, XL, XXL, XXXL</li>
        </ul>
      </div>
    </div>
  );
}
