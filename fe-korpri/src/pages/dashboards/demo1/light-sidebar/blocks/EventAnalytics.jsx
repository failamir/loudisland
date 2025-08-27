import { useMemo } from 'react';
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
  // Mock data
  const days = useMemo(() => {
    const today = new Date();
    const arr = [];
    for (let i = 13; i >= 0; i--) {
      const d = new Date(today);
      d.setDate(today.getDate() - i);
      arr.push(`${d.getDate()}/${d.getMonth() + 1}`);
    }
    return arr;
  }, []);

  const salesSeries = useMemo(() => [{
    name: 'Tiket Terjual',
    data: [12, 18, 14, 25, 30, 28, 22, 35, 40, 38, 42, 46, 44, 50]
  }], []);

  const ticketTypeSeries = useMemo(() => [44, 35, 21], []); // Regular, VIP, VVIP
  const channelSeries = useMemo(() => [{
    name: 'Penjualan',
    data: [120, 80, 60, 40]
  }], []); // Web, Mobile, Booth, Partner

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
      labels: { style: { colors: 'var(--tw-gray-500)', fontSize: '12px' } }
    },
    fill: { gradient: { opacityFrom: 0.25, opacityTo: 0 } },
    grid: { borderColor: 'var(--tw-gray-200)', strokeDashArray: 5 }
  }), [days]);

  const donutOptions = useMemo(() => ({
    chart: { type: 'donut' },
    labels: ['Regular', 'VIP', 'VVIP'],
    legend: { position: 'bottom' },
    colors: ['#3b82f6', '#f59e0b', '#ef4444']
  }), []);

  const barOptions = useMemo(() => ({
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '40%' } },
    xaxis: { categories: ['Web', 'Mobile', 'Booth', 'Partner'], labels: { style: { colors: 'var(--tw-gray-500)', fontSize: '12px' } } },
    yaxis: { labels: { style: { colors: 'var(--tw-gray-500)', fontSize: '12px' } } },
    grid: { borderColor: 'var(--tw-gray-200)', strokeDashArray: 5 },
    colors: ['var(--tw-primary)']
  }), []);

  return (
    <div className="grid lg:grid-cols-3 gap-5 lg:gap-7.5 items-stretch">
      <div className="lg:col-span-2">
        <Card title="Penjualan Tiket - 14 Hari Terakhir">
          <ApexChart type="area" options={lineOptions} series={salesSeries} height={250} />
        </Card>
      </div>
      <div className="lg:col-span-1">
        <Card title="Komposisi Jenis Tiket">
          <ApexChart type="donut" options={donutOptions} series={ticketTypeSeries} height={250} />
        </Card>
      </div>

      <div className="lg:col-span-3">
        <Card title="Sumber Penjualan">
          <ApexChart type="bar" options={barOptions} series={channelSeries} height={260} />
        </Card>
      </div>
    </div>
  );
};

export { EventAnalytics };
