/* eslint-disable react-hooks/exhaustive-deps */
import { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { useAuthContext } from '@/auth';

const API_URL = import.meta.env.VITE_APP_API_URL;
const EVENTS_URL = `${API_URL}/events`;
const BELI_URL = `${API_URL}/buy`;
const GET_USER_URL = `${API_URL}/me`;
const PROVINCES_URL = `${API_URL}/wilayah/provinces`;
const REGENCIES_URL = (provinceCode) => `${API_URL}/wilayah/regencies/${provinceCode}`;
const DISTRICTS_URL = (regencyCode) => `${API_URL}/wilayah/districts/${regencyCode}`;
const VILLAGES_URL = (districtCode) => `${API_URL}/wilayah/villages/${districtCode}`;

function currency(n){
  try {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(n||0));
  } catch {
    return n;
  }
}

export default function OrderWizard({ embedded = false }) {
  const { currentUser } = useAuthContext();

  // steps: 0=select events, 1=review, 2=pay
  const [step, setStep] = useState(0);
  const [loading, setLoading] = useState(false);

  // data
  const [events, setEvents] = useState([]);
  const [selectedIds, setSelectedIds] = useState([]);
  const [userUid, setUserUid] = useState('');
  // data diri (address)
  const [fullName, setFullName] = useState('');
  const [phone, setPhone] = useState('');
  const [address, setAddress] = useState('');
  const [nik, setNik] = useState('');
  const [provinces, setProvinces] = useState([]);
  const [regencies, setRegencies] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [villages, setVillages] = useState([]);
  const [provinceCode, setProvinceCode] = useState('');
  const [regencyCode, setRegencyCode] = useState('');
  const [districtCode, setDistrictCode] = useState('');
  const [villageCode, setVillageCode] = useState('');

  // fetch events on mount
  useEffect(() => {
    let mounted = true;
    (async () => {
      try {
        setLoading(true);
        const { data } = await axios.get(EVENTS_URL, { params: { per_page: 100 } });
        // try to normalize for both resource or plain array responses
        const items = Array.isArray(data) ? data : (data?.data || []);
        if (mounted) setEvents(items);
      } catch (e) {
        console.error('Failed to load events', e);
      } finally {
        setLoading(false);
      }
    })();
    return () => { mounted = false; };
  }, []);

  // ensure we have uid
  useEffect(() => {
    let mounted = true;
    (async () => {
      try {
        // prefer currentUser if it contains uid
        if (currentUser?.uid) {
          setUserUid(currentUser.uid);
          return;
        }
        // fallback load from /me
        const { data } = await axios.get(GET_USER_URL);
        if (mounted && data?.uid) setUserUid(data.uid);
      } catch (e) {
        console.warn('Cannot fetch user uid from /me. Ensure backend returns uid.', e);
      }
    })();
    return () => { mounted = false; };
  }, [currentUser?.uid]);

  // Load provinces once (when component mounts)
  useEffect(() => {
    (async () => {
      try {
        const { data } = await axios.get(PROVINCES_URL);
        setProvinces(data?.data || []);
      } catch (e) {
        console.error('Failed to load provinces', e);
      }
    })();
  }, []);

  // Load regencies when province changes
  useEffect(() => {
    if (!provinceCode) {
      setRegencies([]); setRegencyCode('');
      setDistricts([]); setDistrictCode('');
      setVillages([]); setVillageCode('');
      return;
    }
    (async () => {
      try {
        const { data } = await axios.get(REGENCIES_URL(provinceCode));
        setRegencies(data?.data || []);
        setRegencyCode('');
        setDistricts([]); setDistrictCode('');
        setVillages([]); setVillageCode('');
      } catch (e) {
        console.error('Failed to load regencies', e);
      }
    })();
  }, [provinceCode]);

  // Load districts when regency changes
  useEffect(() => {
    if (!regencyCode) {
      setDistricts([]); setDistrictCode('');
      setVillages([]); setVillageCode('');
      return;
    }
    (async () => {
      try {
        const { data } = await axios.get(DISTRICTS_URL(regencyCode));
        setDistricts(data?.data || []);
        setDistrictCode('');
        setVillages([]); setVillageCode('');
      } catch (e) {
        console.error('Failed to load districts', e);
      }
    })();
  }, [regencyCode]);

  // Load villages when district changes
  useEffect(() => {
    if (!districtCode) {
      setVillages([]); setVillageCode('');
      return;
    }
    (async () => {
      try {
        const { data } = await axios.get(VILLAGES_URL(districtCode));
        setVillages(data?.data || []);
        setVillageCode('');
      } catch (e) {
        console.error('Failed to load villages', e);
      }
    })();
  }, [districtCode]);

  const total = useMemo(() => {
    const map = new Map(events.map(e => [e.id, e]));
    return selectedIds.reduce((sum, id) => sum + Number(map.get(id)?.harga || 0), 0);
  }, [selectedIds, events]);

  const toggleId = (id) => {
    setSelectedIds(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
  };

  const next = () => setStep(s => Math.min(2, s + 1));
  const prev = () => setStep(s => Math.max(0, s - 1));

  const submitOrder = async () => {
    if (!userUid) {
      alert('UID pengguna tidak ditemukan. Pastikan endpoint /me mengembalikan uid.');
      return;
    }
    if (selectedIds.length === 0) {
      alert('Pilih minimal satu tiket.');
      return;
    }
    try {
      setLoading(true);
      const payload = { uid: userUid, id: selectedIds };
      const { data } = await axios.post(BELI_URL, payload);
      const paymentUrl = data?.data || data?.paymentUrl || data?.url;
      if (paymentUrl) {
        window.location.href = paymentUrl; // redirect to Midtrans payment URL
      } else {
        alert('Gagal mendapatkan payment URL.');
        console.error('Unexpected beli response:', data);
      }
    } catch (e) {
      console.error('Beli API error', e);
      alert('Terjadi kesalahan saat memproses pesanan.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className={embedded ? 'py-4' : 'container py-8'}>
      <h1 className="text-2xl font-semibold mb-6">Order Tiket</h1>

      {/* Steps indicator */}
      <div className="flex items-center gap-3 mb-8">
        {[0,1,2].map(i => (
          <div key={i} className={`h-2 flex-1 rounded ${i <= step ? 'bg-blue-600' : 'bg-gray-200'}`} />
        ))}
      </div>

      {step === 0 && (
        <section>
          <h2 className="text-xl font-medium mb-4">Pilih Event</h2>
          {loading && <p>Loading...</p>}
          {!loading && events.length === 0 && <p>Tidak ada event tersedia.</p>}
          <div className="space-y-3">
            {events.map(ev => (
              <label key={ev.id} className="flex items-center justify-between p-4 border rounded cursor-pointer">
                <div className="flex items-center gap-3">
                  <input type="checkbox" checked={selectedIds.includes(ev.id)} onChange={() => toggleId(ev.id)} />
                  <div>
                    <div className="font-medium">{ev.nama_event || ev.name || `Event #${ev.id}`}</div>
                    <div className="text-sm text-gray-500">{ev.deskripsi || ev.description || ''}</div>
                  </div>
                </div>
                <div className="ml-4 font-semibold">{currency(ev.harga)}</div>
              </label>
            ))}
          </div>
          <div className="flex items-center justify-between mt-6">
            <div className="text-lg">Total: <span className="font-semibold">{currency(total)}</span></div>
            <button className="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50" disabled={selectedIds.length===0} onClick={next}>Lanjut</button>
          </div>
        </section>
      )}

      {step === 1 && (
        <section>
          <h2 className="text-xl font-medium mb-4">Isi Data Diri</h2>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm mb-1">Nama Lengkap</label>
              <input type="text" className="form-input w-full" value={fullName} onChange={e=>setFullName(e.target.value)} placeholder="Nama sesuai KTP" />
            </div>
            <div>
              <label className="block text-sm mb-1">Nomor HP</label>
              <input type="tel" className="form-input w-full" value={phone} onChange={e=>setPhone(e.target.value)} placeholder="08xxxxxxxxxx" />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
              <label className="block text-sm mb-1">NIK</label>
              <input type="text" inputMode="numeric" pattern="[0-9]*" className="form-input w-full" value={nik} onChange={e=>setNik(e.target.value.replace(/\D/g,''))} placeholder="16 digit NIK" />
            </div>
            <div className="md:col-span-1">
              <label className="block text-sm mb-1">Alamat</label>
              <textarea className="form-textarea w-full" rows={2} value={address} onChange={e=>setAddress(e.target.value)} placeholder="Nama jalan, no rumah, RT/RW, dsb" />
            </div>
          </div>

          <h3 className="text-lg font-medium mt-6 mb-3">Alamat (Wilayah)</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm mb-1">Provinsi</label>
              <select className="form-select w-full" value={provinceCode} onChange={e=>setProvinceCode(e.target.value)}>
                <option value="">Pilih Provinsi</option>
                {provinces.map(p => (
                  <option key={p.code} value={p.code}>{p.name}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm mb-1">Kabupaten / Kota</label>
              <select className="form-select w-full" value={regencyCode} onChange={e=>setRegencyCode(e.target.value)} disabled={!provinceCode}>
                <option value="">Pilih Kabupaten/Kota</option>
                {regencies.map(r => (
                  <option key={r.code} value={r.code}>{r.name}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm mb-1">Kecamatan</label>
              <select className="form-select w-full" value={districtCode} onChange={e=>setDistrictCode(e.target.value)} disabled={!regencyCode}>
                <option value="">Pilih Kecamatan</option>
                {districts.map(d => (
                  <option key={d.code} value={d.code}>{d.name}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm mb-1">Kelurahan / Desa</label>
              <select className="form-select w-full" value={villageCode} onChange={e=>setVillageCode(e.target.value)} disabled={!districtCode}>
                <option value="">Pilih Kelurahan/Desa</option>
                {villages.map(v => (
                  <option key={v.code} value={v.code}>{v.name}</option>
                ))}
              </select>
            </div>
          </div>

          <div className="flex items-center justify-between mt-6">
            <button className="px-4 py-2 border rounded" onClick={prev}>Kembali</button>
            <div className="text-sm text-gray-500">
              Pastikan alamat sudah benar.
            </div>
            <button
              className="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
              onClick={next}
              disabled={!fullName || !phone || !nik || !provinceCode || !regencyCode || !districtCode || !villageCode}
            >Lanjut</button>
          </div>
        </section>
      )}

      {step === 2 && (
        <section>
          <h2 className="text-xl font-medium mb-4">Pembayaran</h2>
          <p className="mb-4">Klik tombol bayar untuk lanjut ke halaman pembayaran.</p>
          <div className="flex items-center gap-3">
            <button className="px-4 py-2 border rounded" onClick={prev} disabled={loading}>Kembali</button>
            <button className="px-4 py-2 bg-green-600 text-white rounded disabled:opacity-50" disabled={loading} onClick={submitOrder}>
              {loading ? 'Memproses...' : 'Bayar Sekarang'}
            </button>
          </div>
        </section>
      )}
    </div>
  );
}
