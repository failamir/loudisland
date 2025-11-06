import * as authHelper from '@/auth/_helpers';

const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

const authHeaders = () => {
  const auth = authHelper.getAuth();
  return {
    'Content-Type': 'application/json',
    ...(auth?.access_token ? { Authorization: `Bearer ${auth.access_token}` } : {}),
  };
};

export async function getMine({ perPage = 50 } = {}) {
  const res = await fetch(`${API_URL}/referral-codes/mine?per_page=${encodeURIComponent(perPage)}`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function updateMine(payload = {}) {
  const res = await fetch(`${API_URL}/referral-codes/mine`, {
    method: 'PATCH',
    headers: authHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function registerReferral(payload = {}) {
  const res = await fetch(`${API_URL}/referral-codes`, {
    method: 'POST',
    headers: authHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function getTransactions({ perPage = 50 } = {}) {
  const res = await fetch(`${API_URL}/referral-codes/transactions?per_page=${encodeURIComponent(perPage)}`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}
