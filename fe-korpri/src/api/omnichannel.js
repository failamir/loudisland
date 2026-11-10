import * as authHelper from '@/auth/_helpers';

const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8088/api/v1';

const authHeaders = () => {
  const auth = authHelper.getAuth();
  return {
    'Content-Type': 'application/json',
    ...(auth?.access_token ? { Authorization: `Bearer ${auth.access_token}` } : {}),
  };
};

export async function getOmnichannelMetrics() {
  const res = await fetch(`${API_URL}/omnichannel/metrics`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function getOmnichannelLookups() {
  const res = await fetch(`${API_URL}/omnichannel/lookups`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function getOmnichannelAssignees() {
  const res = await fetch(`${API_URL}/omnichannel/assignees`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function getOmnichannelThreads({ scope = 'all', search = '', tag = '' } = {}) {
  const params = new URLSearchParams();
  if (scope) params.append('scope', scope);
  if (search) params.append('search', search);
  if (tag) params.append('tag', tag);

  const res = await fetch(`${API_URL}/omnichannel/threads?${params.toString()}`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function getOmnichannelThread(id) {
  const res = await fetch(`${API_URL}/omnichannel/threads/${id}`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function updateOmnichannelThreadStatus(id, { status, close_comment }) {
  const res = await fetch(`${API_URL}/omnichannel/threads/${id}/status`, {
    method: 'PUT',
    headers: authHeaders(),
    body: JSON.stringify({ status, close_comment }),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function assignOmnichannelThread(id, { agent_id, agent_name }) {
  const res = await fetch(`${API_URL}/omnichannel/threads/${id}/assign`, {
    method: 'PUT',
    headers: authHeaders(),
    body: JSON.stringify({ agent_id, agent_name }),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function toggleOmnichannelFavourite(id) {
  const res = await fetch(`${API_URL}/omnichannel/threads/${id}/favourite`, {
    method: 'PUT',
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function getOmnichannelMessages(threadId) {
  const res = await fetch(`${API_URL}/omnichannel/threads/${threadId}/messages`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function sendOmnichannelMessage(threadId, payload) {
  const res = await fetch(`${API_URL}/omnichannel/threads/${threadId}/messages`, {
    method: 'POST',
    headers: authHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function getOmnichannelQuickReplies() {
  const res = await fetch(`${API_URL}/omnichannel/quick-replies`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function createOmnichannelQuickReply(payload) {
  const res = await fetch(`${API_URL}/omnichannel/quick-replies`, {
    method: 'POST',
    headers: authHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function getOmnichannelTags() {
  const res = await fetch(`${API_URL}/omnichannel/tags`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function addOmnichannelThreadTag(threadId, tag) {
  const res = await fetch(`${API_URL}/omnichannel/threads/${threadId}/tags`, {
    method: 'POST',
    headers: authHeaders(),
    body: JSON.stringify({ tag }),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function removeOmnichannelThreadTag(threadId, tag) {
  const res = await fetch(`${API_URL}/omnichannel/threads/${threadId}/tags/${encodeURIComponent(tag)}`, {
    method: 'DELETE',
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function createOmnichannelBroadcast(payload) {
  const res = await fetch(`${API_URL}/omnichannel/broadcast`, {
    method: 'POST',
    headers: authHeaders(),
    body: JSON.stringify(payload),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

export async function getOmnichannelBroadcastHistory() {
  const res = await fetch(`${API_URL}/omnichannel/broadcast/history`, {
    headers: authHeaders(),
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

