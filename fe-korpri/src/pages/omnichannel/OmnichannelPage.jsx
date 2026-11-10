import React, { useState, useEffect, useRef } from 'react';
import { Container } from '@/components/container';
import { Toolbar, ToolbarHeading } from '@/layouts/demo1/toolbar';
import { KeenIcon } from '@/components';
import {
  getOmnichannelMetrics,
  getOmnichannelThreads,
  getOmnichannelThread,
  getOmnichannelMessages,
  sendOmnichannelMessage,
  updateOmnichannelThreadStatus,
  assignOmnichannelThread,
  toggleOmnichannelFavourite,
  getOmnichannelQuickReplies,
  getOmnichannelTags,
  addOmnichannelThreadTag,
  removeOmnichannelThreadTag,
  getOmnichannelAssignees,
} from '@/api/omnichannel';
import BroadcastModal from './BroadcastModal';

const OmnichannelPage = () => {
  // Tabs & Metrics
  const [activeTab, setActiveTab] = useState('all'); // all, mine, unassigned, resolved, favourites
  const [metrics, setMetrics] = useState({
    tabs: { all: 0, mine: 0, unassigned: 0, resolved: 0, favourites: 0 },
    total_open: 0,
    total_unread: 0,
  });

  // Threads list state
  const [threads, setThreads] = useState([]);
  const [loadingThreads, setLoadingThreads] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedTag, setSelectedTag] = useState('');

  // Active thread & messages
  const [activeThreadId, setActiveThreadId] = useState(null);
  const [activeThread, setActiveThread] = useState(null);
  const [messages, setMessages] = useState([]);
  const [loadingMessages, setLoadingMessages] = useState(false);

  // Composer state
  const [inputMessage, setInputMessage] = useState('');
  const [isPrivateNote, setIsPrivateNote] = useState(false);
  const [sending, setSending] = useState(false);
  const messagesEndRef = useRef(null);

  // Reference lookups
  const [quickReplies, setQuickReplies] = useState([]);
  const [tags, setTags] = useState([]);
  const [assignees, setAssignees] = useState([]);
  const [showQuickReplyMenu, setShowQuickReplyMenu] = useState(false);
  const [showBroadcastModal, setShowBroadcastModal] = useState(false);
  const [newTagInput, setNewTagInput] = useState('');

  // Initial fetch
  useEffect(() => {
    fetchMetrics();
    fetchLookups();
  }, []);

  // Fetch threads when tab, search, or tag changes
  useEffect(() => {
    fetchThreads();
  }, [activeTab, searchQuery, selectedTag]);

  // Fetch thread detail & messages when activeThreadId changes
  useEffect(() => {
    if (activeThreadId) {
      fetchThreadDetail(activeThreadId);
      fetchMessages(activeThreadId);
    } else {
      setActiveThread(null);
      setMessages([]);
    }
  }, [activeThreadId]);

  // Auto-scroll messages
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const fetchMetrics = async () => {
    const res = await getOmnichannelMetrics();
    if (res.ok && res.data?.data) {
      setMetrics(res.data.data);
    }
  };

  const fetchLookups = async () => {
    const [qrRes, tagRes, asgRes] = await Promise.all([
      getOmnichannelQuickReplies(),
      getOmnichannelTags(),
      getOmnichannelAssignees(),
    ]);
    if (qrRes.ok && qrRes.data?.data) setQuickReplies(qrRes.data.data);
    if (tagRes.ok && tagRes.data?.data) setTags(tagRes.data.data);
    if (asgRes.ok && asgRes.data?.data) setAssignees(asgRes.data.data);
  };

  const fetchThreads = async () => {
    setLoadingThreads(true);
    const res = await getOmnichannelThreads({
      scope: activeTab,
      search: searchQuery,
      tag: selectedTag,
    });
    setLoadingThreads(false);
    if (res.ok && res.data?.data) {
      setThreads(res.data.data);
      if (!activeThreadId && res.data.data.length > 0) {
        setActiveThreadId(res.data.data[0].id);
      }
    }
  };

  const fetchThreadDetail = async (id) => {
    const res = await getOmnichannelThread(id);
    if (res.ok && res.data?.data) {
      setActiveThread(res.data.data);
    }
  };

  const fetchMessages = async (id) => {
    setLoadingMessages(true);
    const res = await getOmnichannelMessages(id);
    setLoadingMessages(false);
    if (res.ok && res.data?.data) {
      setMessages(res.data.data);
      fetchMetrics();
    }
  };

  const handleSendMessage = async (e) => {
    e?.preventDefault();
    if (!inputMessage.trim() || !activeThreadId || sending) return;

    setSending(true);
    const payload = {
      content: inputMessage,
      message_type: 'text',
      is_private_note: isPrivateNote,
    };

    const res = await sendOmnichannelMessage(activeThreadId, payload);
    setSending(false);

    if (res.ok && res.data?.data) {
      setInputMessage('');
      setMessages((prev) => [...prev, res.data.data]);
      fetchThreads(); // update snippet
    }
  };

  const handleToggleFavourite = async () => {
    if (!activeThreadId) return;
    const res = await toggleOmnichannelFavourite(activeThreadId);
    if (res.ok) {
      setActiveThread((prev) => prev && { ...prev, is_favourite: !prev.is_favourite });
      setThreads((prev) =>
        prev.map((t) => (t.id === activeThreadId ? { ...t, is_favourite: !t.is_favourite } : t))
      );
      fetchMetrics();
    }
  };

  const handleStatusChange = async (newStatus) => {
    if (!activeThreadId) return;
    const res = await updateOmnichannelThreadStatus(activeThreadId, {
      status: newStatus,
      close_comment: newStatus === 'resolved' ? 'Percakapan diselesaikan oleh staf CS' : undefined,
    });
    if (res.ok && res.data?.data) {
      setActiveThread(res.data.data);
      fetchThreads();
      fetchMetrics();
    }
  };

  const handleAssignChange = async (agentId) => {
    if (!activeThreadId) return;
    const agent = assignees.find((a) => a.id === agentId);
    const res = await assignOmnichannelThread(activeThreadId, {
      agent_id: agent ? agent.id : null,
      agent_name: agent ? agent.name : null,
    });
    if (res.ok && res.data?.data) {
      setActiveThread(res.data.data);
      fetchThreads();
      fetchMetrics();
    }
  };

  const handleAddTag = async (e) => {
    e?.preventDefault();
    if (!newTagInput.trim() || !activeThreadId) return;
    const res = await addOmnichannelThreadTag(activeThreadId, newTagInput.trim());
    if (res.ok && res.data?.data) {
      setActiveThread((prev) => prev && { ...prev, tags: res.data.data });
      setNewTagInput('');
      fetchThreads();
    }
  };

  const handleRemoveTag = async (tagToRemove) => {
    if (!activeThreadId) return;
    const res = await removeOmnichannelThreadTag(activeThreadId, tagToRemove);
    if (res.ok && res.data?.data) {
      setActiveThread((prev) => prev && { ...prev, tags: res.data.data });
      fetchThreads();
    }
  };

  const selectQuickReply = (qr) => {
    setInputMessage((prev) => (prev ? `${prev} ${qr.content}` : qr.content));
    setShowQuickReplyMenu(false);
  };

  return (
    <div>
      <Container>
        <Toolbar>
          <ToolbarHeading
            title="Omnichannel Hub"
            description="Layanan Komunikasi Pelanggan & WhatsApp CS Terpadu"
          />
          <div className="flex items-center gap-2">
            <button
              onClick={() => setShowBroadcastModal(true)}
              className="btn btn-sm btn-primary"
            >
              <KeenIcon icon="send" className="mr-1" />
              Broadcast Massal
            </button>
            <span className="badge badge-sm badge-outline badge-primary">
              <KeenIcon icon="whatsapp" className="mr-1 text-green-500" />
              WAHA Connected (0812-XXXX)
            </span>
          </div>
        </Toolbar>
      </Container>

      <Container>
        {/* Main Split-Screen Workspace */}
        <div className="card shadow-sm border border-gray-200 dark:border-gray-800 flex flex-col md:flex-row h-[780px] overflow-hidden rounded-xl bg-white dark:bg-coal-500">
          
          {/* 1. Left Sidebar: Inbox & Filter */}
          <div className="w-full md:w-80 lg:w-96 border-r border-gray-200 dark:border-gray-800 flex flex-col h-full bg-gray-50/50 dark:bg-coal-600/30">
            {/* Search & Tabs */}
            <div className="p-3 border-b border-gray-200 dark:border-gray-800 space-y-2">
              <div className="relative">
                <input
                  type="text"
                  placeholder="Cari chat atau nomor peserta..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="input input-sm w-full pl-8 pr-3 text-xs bg-white dark:bg-coal-600 rounded-lg"
                />
                <KeenIcon
                  icon="magnifier"
                  className="absolute left-2.5 top-2.5 text-gray-400 text-xs"
                />
              </div>

              {/* Tabs: All, Mine, Unassigned, Resolved, Favs */}
              <div className="flex items-center gap-1 overflow-x-auto pb-1 text-xs">
                <button
                  onClick={() => setActiveTab('all')}
                  className={`px-2.5 py-1 rounded-md font-medium transition-colors ${
                    activeTab === 'all'
                      ? 'bg-primary text-white'
                      : 'bg-gray-200 dark:bg-coal-500 text-gray-600 dark:text-gray-300 hover:bg-gray-300'
                  }`}
                >
                  Semua ({metrics.tabs.all})
                </button>
                <button
                  onClick={() => setActiveTab('mine')}
                  className={`px-2.5 py-1 rounded-md font-medium transition-colors ${
                    activeTab === 'mine'
                      ? 'bg-primary text-white'
                      : 'bg-gray-200 dark:bg-coal-500 text-gray-600 dark:text-gray-300 hover:bg-gray-300'
                  }`}
                >
                  Saya ({metrics.tabs.mine})
                </button>
                <button
                  onClick={() => setActiveTab('unassigned')}
                  className={`px-2.5 py-1 rounded-md font-medium transition-colors ${
                    activeTab === 'unassigned'
                      ? 'bg-primary text-white'
                      : 'bg-gray-200 dark:bg-coal-500 text-gray-600 dark:text-gray-300 hover:bg-gray-300'
                  }`}
                >
                  Antrean ({metrics.tabs.unassigned})
                </button>
                <button
                  onClick={() => setActiveTab('resolved')}
                  className={`px-2.5 py-1 rounded-md font-medium transition-colors ${
                    activeTab === 'resolved'
                      ? 'bg-primary text-white'
                      : 'bg-gray-200 dark:bg-coal-500 text-gray-600 dark:text-gray-300 hover:bg-gray-300'
                  }`}
                >
                  Selesai ({metrics.tabs.resolved})
                </button>
                <button
                  onClick={() => setActiveTab('favourites')}
                  className={`px-2 py-1 rounded-md font-medium transition-colors ${
                    activeTab === 'favourites'
                      ? 'bg-primary text-white'
                      : 'bg-gray-200 dark:bg-coal-500 text-gray-600 dark:text-gray-300 hover:bg-gray-300'
                  }`}
                >
                  <KeenIcon icon="star" className="text-xs" />
                </button>
              </div>
            </div>

            {/* Threads List */}
            <div className="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
              {loadingThreads ? (
                <div className="p-4 text-center text-xs text-gray-400">Memuat percakapan...</div>
              ) : threads.length === 0 ? (
                <div className="p-6 text-center text-xs text-gray-400">
                  Tidak ada pesan di tab ini
                </div>
              ) : (
                threads.map((t) => {
                  const isSelected = t.id === activeThreadId;
                  return (
                    <div
                      key={t.id}
                      onClick={() => setActiveThreadId(t.id)}
                      className={`p-3 cursor-pointer transition-colors relative flex gap-3 ${
                        isSelected
                          ? 'bg-primary/10 border-l-4 border-primary'
                          : 'hover:bg-gray-100 dark:hover:bg-coal-600/50'
                      }`}
                    >
                      {/* Avatar initials */}
                      <div className="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 font-semibold flex items-center justify-center shrink-0 text-xs">
                        {t.customer.initials || 'CS'}
                      </div>

                      {/* Content preview */}
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between">
                          <span className="font-semibold text-xs text-gray-900 dark:text-white truncate">
                            {t.customer.name}
                          </span>
                          <span className="text-[10px] text-gray-400 shrink-0">
                            {t.last_message.time_display}
                          </span>
                        </div>

                        <div className="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                          {t.last_message.text}
                        </div>

                        <div className="flex items-center gap-1.5 mt-1.5">
                          {t.tags.map((tag) => (
                            <span
                              key={tag}
                              className="px-1.5 py-0.2 text-[9px] bg-gray-200 dark:bg-coal-500 text-gray-700 dark:text-gray-300 rounded font-medium"
                            >
                              {tag}
                            </span>
                          ))}
                          {t.unread_count > 0 && (
                            <span className="ml-auto badge badge-xs badge-danger">
                              {t.unread_count}
                            </span>
                          )}
                        </div>
                      </div>
                    </div>
                  );
                })
              )}
            </div>
          </div>

          {/* 2. Middle: Active Chat Room & Timeline */}
          {activeThread ? (
            <div className="flex-1 flex flex-col h-full bg-white dark:bg-coal-500 border-r border-gray-200 dark:border-gray-800">
              {/* Chat Room Header */}
              <div className="p-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-white dark:bg-coal-600">
                <div className="flex items-center gap-2">
                  <div className="w-8 h-8 rounded-full bg-primary/20 text-primary font-bold flex items-center justify-center text-xs">
                    {activeThread.customer.initials}
                  </div>
                  <div>
                    <div className="font-semibold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                      {activeThread.customer.name}
                      <span className="text-[10px] text-gray-400 font-normal">
                        ({activeThread.customer.phone})
                      </span>
                    </div>
                    <div className="text-[10px] text-gray-400 flex items-center gap-1">
                      <span className="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                      WhatsApp Official • Status: <strong className="uppercase">{activeThread.status}</strong>
                    </div>
                  </div>
                </div>

                {/* Header Action Buttons */}
                <div className="flex items-center gap-2">
                  <button
                    onClick={handleToggleFavourite}
                    className={`btn btn-xs btn-icon btn-light ${
                      activeThread.is_favourite ? 'text-amber-500' : 'text-gray-400'
                    }`}
                    title="Favoritkan"
                  >
                    <KeenIcon icon="star" />
                  </button>

                  {/* Assignee select */}
                  <select
                    value={activeThread.assignment.assigned_agent_id || ''}
                    onChange={(e) => handleAssignChange(e.target.value)}
                    className="select select-xs text-[11px] bg-gray-100 dark:bg-coal-600 rounded-lg"
                  >
                    <option value="">Belum Ditugaskan</option>
                    {assignees.map((a) => (
                      <option key={a.id} value={a.id}>
                        {a.name} ({a.role_badge})
                      </option>
                    ))}
                  </select>

                  {/* Status Toggle */}
                  {activeThread.status === 'open' ? (
                    <button
                      onClick={() => handleStatusChange('resolved')}
                      className="btn btn-xs btn-success"
                    >
                      <KeenIcon icon="check-circle" className="mr-1" /> Selesaikan
                    </button>
                  ) : (
                    <button
                      onClick={() => handleStatusChange('open')}
                      className="btn btn-xs btn-light"
                    >
                      Buka Kembali
                    </button>
                  )}
                </div>
              </div>

              {/* Chat Message Timeline */}
              <div className="flex-1 overflow-y-auto p-4 space-y-3 bg-[#f0f2f5]/40 dark:bg-coal-700/20">
                {loadingMessages ? (
                  <div className="text-center py-10 text-xs text-gray-400">
                    Memuat riwayat chat...
                  </div>
                ) : (
                  messages.map((m) => {
                    const isInbound = m.direction === 'inbound';
                    const isInternal = m.is_private_note;

                    if (isInternal) {
                      return (
                        <div
                          key={m.id}
                          className="mx-auto max-w-md p-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs shadow-sm"
                        >
                          <div className="flex items-center justify-between font-semibold text-[10px] text-amber-700 dark:text-amber-400 mb-1">
                            <span>Catatan Internal ({m.sender_name})</span>
                            <span>{new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                          </div>
                          <p>{m.content}</p>
                        </div>
                      );
                    }

                    return (
                      <div
                        key={m.id}
                        className={`flex flex-col ${isInbound ? 'items-start' : 'items-end'}`}
                      >
                        <div
                          className={`max-w-[75%] rounded-xl px-3.5 py-2 text-xs shadow-sm relative ${
                            isInbound
                              ? 'bg-white dark:bg-coal-600 text-gray-800 dark:text-gray-100 rounded-tl-none border border-gray-100 dark:border-gray-700'
                              : 'bg-primary text-white rounded-tr-none'
                          }`}
                        >
                          <div className="text-[10px] font-semibold opacity-75 mb-0.5">
                            {isInbound ? m.sender_name : 'Anda'}
                          </div>
                          <div className="leading-relaxed whitespace-pre-wrap">{m.content}</div>
                          <div
                            className={`text-[9px] mt-1 text-right flex items-center justify-end gap-1 ${
                              isInbound ? 'text-gray-400' : 'text-primary-light/80'
                            }`}
                          >
                            <span>
                              {new Date(m.created_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                              })}
                            </span>
                            {!isInbound && <KeenIcon icon="double-check" className="text-[10px]" />}
                          </div>
                        </div>
                      </div>
                    );
                  })
                )}
                <div ref={messagesEndRef} />
              </div>

              {/* Chat Composer */}
              <div className="p-2.5 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-coal-600 relative">
                {/* Quick Reply Drawer Menu */}
                {showQuickReplyMenu && (
                  <div className="absolute bottom-full left-2 right-2 mb-2 p-3 bg-white dark:bg-coal-500 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-48 overflow-y-auto space-y-1.5 z-10">
                    <div className="text-[10px] font-bold uppercase text-gray-400 mb-1">
                      Template Balasan Cepat (Quick Reply)
                    </div>
                    {quickReplies.map((qr) => (
                      <div
                        key={qr.id}
                        onClick={() => selectQuickReply(qr)}
                        className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-coal-600 cursor-pointer text-xs flex items-center justify-between"
                      >
                        <div>
                          <strong className="text-primary mr-1">{qr.shortcut}</strong> {qr.title}
                          <div className="text-[10px] text-gray-500 truncate max-w-sm">
                            {qr.content}
                          </div>
                        </div>
                        <span className="badge badge-xs badge-outline">{qr.category}</span>
                      </div>
                    ))}
                  </div>
                )}

                <div className="flex items-center justify-between mb-1.5 text-xs px-1">
                  <div className="flex items-center gap-2">
                    <button
                      type="button"
                      onClick={() => setShowQuickReplyMenu(!showQuickReplyMenu)}
                      className="text-gray-500 hover:text-primary flex items-center gap-1 text-[11px]"
                    >
                      <KeenIcon icon="flash" /> Quick Reply
                    </button>
                    <label className="flex items-center gap-1 text-[11px] cursor-pointer text-gray-500">
                      <input
                        type="checkbox"
                        checked={isPrivateNote}
                        onChange={(e) => setIsPrivateNote(e.target.checked)}
                        className="checkbox checkbox-xs"
                      />
                      <span>Catatan Internal</span>
                    </label>
                  </div>
                </div>

                <form onSubmit={handleSendMessage} className="flex gap-2">
                  <textarea
                    rows={2}
                    value={inputMessage}
                    onChange={(e) => setInputMessage(e.target.value)}
                    placeholder={
                      isPrivateNote
                        ? 'Tulis catatan internal (hanya staf yang bisa membaca)...'
                        : 'Ketik pesan balasan... Tekan Enter untuk mengirim'
                    }
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        handleSendMessage();
                      }
                    }}
                    className={`textarea textarea-sm w-full text-xs rounded-xl ${
                      isPrivateNote ? 'bg-amber-50/50 border-amber-300' : ''
                    }`}
                  />
                  <button
                    type="submit"
                    disabled={sending || !inputMessage.trim()}
                    className={`btn btn-sm ${isPrivateNote ? 'btn-warning' : 'btn-primary'} self-end px-4`}
                  >
                    {sending ? '...' : <KeenIcon icon="send" />}
                  </button>
                </form>
              </div>
            </div>
          ) : (
            <div className="flex-1 flex items-center justify-center text-xs text-gray-400 p-8">
              Pilih salah satu percakapan di sebelah kiri untuk melihat pesan
            </div>
          )}

          {/* 3. Right Sidebar: Customer CRM & Order Details */}
          {activeThread && (
            <div className="hidden lg:flex w-72 border-l border-gray-200 dark:border-gray-800 flex-col h-full bg-gray-50/40 dark:bg-coal-600/20 overflow-y-auto p-4 space-y-4">
              {/* Profile Card */}
              <div className="text-center pb-3 border-b border-gray-200 dark:border-gray-700">
                <div className="w-14 h-14 rounded-full bg-primary/20 text-primary font-bold text-lg mx-auto flex items-center justify-center mb-2">
                  {activeThread.customer.initials}
                </div>
                <h4 className="font-bold text-xs text-gray-900 dark:text-white">
                  {activeThread.customer.name}
                </h4>
                <p className="text-[11px] text-gray-500">{activeThread.customer.phone}</p>
                {activeThread.customer.email && (
                  <p className="text-[11px] text-gray-400">{activeThread.customer.email}</p>
                )}
              </div>

              {/* Race Registration / Ticket Metadata */}
              <div className="space-y-2">
                <h5 className="font-semibold text-[11px] uppercase tracking-wider text-gray-400">
                  Data Registrasi Korpri Run
                </h5>

                <div className="p-2.5 rounded-lg bg-white dark:bg-coal-600 border border-gray-200 dark:border-gray-700 text-xs space-y-1.5">
                  <div className="flex justify-between">
                    <span className="text-gray-500">Nomor BIB</span>
                    <strong className="text-primary font-mono">
                      {activeThread.customer.bib_number || 'Belum di-pair'}
                    </strong>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-500">Kategori Lari</span>
                    <span className="font-medium">
                      {activeThread.customer.event_category || '5K Fun Run'}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-500">Ukuran Jersey</span>
                    <span className="badge badge-xs badge-outline">
                      {activeThread.customer.shirt_size || 'L'}
                    </span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-gray-500">Pembayaran</span>
                    <span
                      className={`badge badge-xs ${
                        activeThread.customer.payment_status === 'PAID'
                          ? 'badge-success'
                          : 'badge-warning'
                      }`}
                    >
                      {activeThread.customer.payment_status || 'PAID'}
                    </span>
                  </div>
                </div>
              </div>

              {/* Conversation Tags Management */}
              <div className="space-y-2">
                <h5 className="font-semibold text-[11px] uppercase tracking-wider text-gray-400">
                  Tag Percakapan
                </h5>
                <div className="flex flex-wrap gap-1">
                  {activeThread.tags.map((tag) => (
                    <span
                      key={tag}
                      className="badge badge-sm badge-outline flex items-center gap-1 text-[10px]"
                    >
                      {tag}
                      <button
                        onClick={() => handleRemoveTag(tag)}
                        className="hover:text-danger ml-0.5"
                      >
                        ×
                      </button>
                    </span>
                  ))}
                </div>

                <form onSubmit={handleAddTag} className="flex gap-1 mt-1">
                  <input
                    type="text"
                    placeholder="Tambah tag..."
                    value={newTagInput}
                    onChange={(e) => setNewTagInput(e.target.value)}
                    className="input input-xs w-full text-[10px] rounded"
                  />
                  <button type="submit" className="btn btn-xs btn-light">
                    +
                  </button>
                </form>
              </div>

              {/* Session / Remarks */}
              <div className="space-y-1 pt-2 border-t border-gray-200 dark:border-gray-700 text-xs">
                <div className="text-[10px] text-gray-400">Catatan Sesi:</div>
                <div className="p-2 rounded bg-gray-100 dark:bg-coal-700 text-[11px] text-gray-600 dark:text-gray-300 italic">
                  {activeThread.remarks || 'Tidak ada catatan tambahan untuk percakapan ini.'}
                </div>
              </div>
            </div>
          )}
        </div>
      </Container>

      {/* Broadcast Modal */}
      <BroadcastModal
        isOpen={showBroadcastModal}
        onClose={() => setShowBroadcastModal(false)}
      />
    </div>
  );
};

export default OmnichannelPage;

