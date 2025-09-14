import { useEffect, useMemo, useState } from 'react';
import ApexChart from 'react-apexcharts';

const Card = ({ title, children, right }) => (
  <div className="card h-full">
    <div className="card-header">
      <h3 className="card-title">{title}</h3>
      {right}
    </div>
    <div className="card-body">
      {children}
    </div>
  </div>
);

const EventAnalytics = () => {
  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [transactions, setTransactions] = useState([]);
  const [participants, setParticipants] = useState([]);

  useEffect(() => {
    const controller = new AbortController();
    let ignore = false;
    setLoading(true);
    setError(null);
    fetch(`${API_URL}/transactions`, { signal: controller.signal, headers: { 'Accept': 'application/json' } })
      .then(async (res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (ignore) return;
        const rows = Array.isArray(json?.data) ? json.data : [];
        setTransactions(rows);
      })
      .catch((e) => {
        if (ignore) return;
        if (e.name === 'AbortError') return;
        setError(e?.message || 'Failed to load');
      })
      .finally(() => { if (!ignore) setLoading(false); });
    return () => { ignore = true; controller.abort(); };
  }, [API_URL]);

  // Fetch participants for per-participant event composition
  useEffect(() => {
    const controller = new AbortController();
    let ignore = false;
    fetch(`${API_URL}/participants`, { signal: controller.signal, headers: { 'Accept': 'application/json' } })
      .then(async (res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (ignore) return;
        const rows = Array.isArray(json?.data) ? json.data : (Array.isArray(json) ? json : []);
        setParticipants(rows);
      })
      .catch(() => {})
      .finally(() => {});
    return () => { ignore = true; controller.abort(); };
  }, [API_URL]);

  // Helpers
  const safeParseAmount = (val) => {
    const n = Number(val);
    return Number.isFinite(n) ? n : 0;
  };
  const dateKey = (createdAt) => {
    if (!createdAt) return '';
    // created_at like "YYYY-MM-DD HH:mm:ss" -> take date part
    return String(createdAt).slice(0, 10);
  };

  // Build daily sales (sum amount per day)
  const { days, salesSeries } = useMemo(() => {
    const byDate = new Map();
    for (const t of transactions) {
      // Only count successful transactions for daily sales
      const status = (t?.status || '').toLowerCase();
      if (status !== 'success') continue;
      const key = dateKey(t.created_at);
      if (!key) continue;
      const prev = byDate.get(key) || 0;
      byDate.set(key, prev + safeParseAmount(t.amount));
    }
    // Sort by date asc
    const labels = Array.from(byDate.keys()).sort();
    const data = labels.map((k) => byDate.get(k));
    return {
      days: labels,
      salesSeries: [{ name: 'Total Penjualan (Rp)', data }],
    };
  }, [transactions]);

  // Build ticket/event composition PER PARTICIPANT
  const { ticketLabels, ticketTypeSeries } = useMemo(() => {
    // Build a quick map eventId -> event name based on transactions' loaded events (best-effort)
    const eventNameMap = new Map();
    for (const t of transactions) {
      const ev = t?.event;
      if (ev?.id) {
        const name = ev?.nama_event || ev?.name || ev?.judul || `Event #${ev.id}`;
        if (!eventNameMap.has(ev.id)) eventNameMap.set(ev.id, name);
      }
    }

    const counts = new Map();
    for (const p of participants) {
      // Only count active/paid participants when status === '1' if provided
      if (String(p?.status ?? '') !== '1') continue;
      const eid = p?.ticket_id ?? null;
      let label = 'Tidak diketahui';
      if (eid != null) {
        label = eventNameMap.get(eid) || `Event #${eid}`;
      }
      counts.set(label, (counts.get(label) || 0) + 1);
    }
    const labels = Array.from(counts.keys());
    const series = labels.map((l) => counts.get(l));
    return { ticketLabels: labels, ticketTypeSeries: series };
  }, [participants, transactions]);

  // Build status distribution
  const statusSeries = useMemo(() => {
    const order = ['success', 'pending', 'failed', 'cancel', 'expire'];
    const counts = new Map();
    for (const t of transactions) {
      const s = (t?.status || 'unknown').toLowerCase();
      counts.set(s, (counts.get(s) || 0) + 1);
    }
    const labels = Array.from(new Set([...order, ...counts.keys()]));
    const series = [{ name: 'Transaksi', data: labels.map((l) => counts.get(l) || 0) }];
    return { labels, series };
  }, [transactions]);

  const lineOptions = useMemo(() => ({
    chart: { type: 'area', toolbar: { show: false }, height: 250 },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3, colors: ['var(--tw-primary)'] },
    xaxis: {
      categories: days,
      labels: { style: { colors: 'var(--tw-gray-500)', fontSize: '12px' } },
      axisBorder: { show: false }, axisTicks: { show: false }
    },
    yaxis: {
      labels: {
        style: { colors: 'var(--tw-gray-500)', fontSize: '12px' },
        formatter: (val) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(val)
      }
    },
    fill: { gradient: { opacityFrom: 0.25, opacityTo: 0 } },
    grid: { borderColor: 'var(--tw-gray-200)', strokeDashArray: 5 }
  }), [days]);

  const donutOptions = useMemo(() => ({
    chart: { type: 'donut' },
    labels: ticketLabels,
    legend: { position: 'bottom' },
    colors: ['#3b82f6', '#f59e0b', '#ef4444']
  }), [ticketLabels]);

  const barOptions = useMemo(() => ({
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '40%' } },
    xaxis: { categories: statusSeries.labels, labels: { style: { colors: 'var(--tw-gray-500)', fontSize: '12px' } } },
    yaxis: { labels: { style: { colors: 'var(--tw-gray-500)', fontSize: '12px' } } },
    grid: { borderColor: 'var(--tw-gray-200)', strokeDashArray: 5 },
    colors: ['var(--tw-primary)']
  }), [statusSeries.labels]);

  return (
    <div className="grid lg:grid-cols-3 gap-5 lg:gap-7.5 items-stretch">
      <div className="lg:col-span-2">
        <Card title="Penjualan Tiket - Per Hari">
          {loading ? (
            <div className="text-2sm text-gray-600">Memuat…</div>
          ) : error ? (
            <div className="text-2sm text-danger">{String(error)}</div>
          ) : (
            <ApexChart type="area" options={lineOptions} series={salesSeries} height={250} />
          )}
        </Card>
      </div>
      <div className="lg:col-span-1">
        <Card title="Komposisi Event">
          {loading ? (
            <div className="text-2sm text-gray-600">Memuat…</div>
          ) : (
            <ApexChart type="donut" options={donutOptions} series={ticketTypeSeries} height={250} />
          )}
        </Card>
      </div>

      <div className="lg:col-span-3">
        <Card title="Distribusi Status Transaksi">
          {loading ? (
            <div className="text-2sm text-gray-600">Memuat…</div>
          ) : (
            <ApexChart type="bar" options={barOptions} series={statusSeries.series} height={260} />
          )}
        </Card>
      </div>
    </div>
  );
};

export { EventAnalytics };
