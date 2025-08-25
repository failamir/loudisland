import React, { useEffect, useState } from 'react';
import axios from 'axios';

const TransactionsListPage = () => {
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    axios.get('http://localhost:8000/api/v1/transaksis')
      .then(response => {
        const data = Array.isArray(response.data) ? response.data : response.data.data;
        setTransactions(data);
        setLoading(false);
      })
      .catch(err => {
        setError('Gagal mengambil data transaksi.');
        setLoading(false);
      });
  }, []);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div>
      <h1>Daftar Transaksi</h1>
      <table border="1" cellPadding="8" style={{ borderCollapse: 'collapse', width: '100%' }}>
        <thead>
          <tr>
            <th>ID</th>
            <th>Invoice</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Nama</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          {transactions.map((trx) => (
            <tr key={trx.id}>
              <td>{trx.id}</td>
              <td>{trx.invoice || '-'}</td>
              <td>{trx.amount || '-'}</td>
              <td>{trx.status || '-'}</td>
              <td>{trx.peserta?.name || trx.created_by?.name || '-'}</td>
              <td>{trx.created_at || '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default TransactionsListPage;
