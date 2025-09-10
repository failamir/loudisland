import { useEffect, useState } from 'react';
import axios from 'axios';
import { toast } from 'sonner';
import { Modal } from '@/components/modal/Modal';
import { ModalContent } from '@/components/modal/ModalContent';
import { ModalHeader } from '@/components/modal/ModalHeader';
import { ModalTitle } from '@/components/modal/ModalTitle';
import { ModalBody } from '@/components/modal/ModalBody';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const baseUrl = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

export default function RoleFormModal({ open, onClose, onSaved, editItem }) {
  const isEdit = !!editItem?.id;
  const [loading, setLoading] = useState(false);
  const [permissions, setPermissions] = useState([]);
  const [form, setForm] = useState({ title: '', permissions: [] });

  useEffect(() => {
    const loadPerms = async () => {
      try {
        const { data } = await axios.get(`${baseUrl}/permissions`);
        setPermissions(data || []);
      } catch (e) {
        console.warn('Permissions list API not available yet');
        setPermissions([]);
      }
    };
    loadPerms();
  }, []);

  useEffect(() => {
    if (editItem) {
      setForm({
        title: editItem.title || editItem.name || '',
        permissions: (editItem.permissions || []).map(p => p.id),
      });
    } else {
      setForm({ title: '', permissions: [] });
    }
  }, [editItem]);

  const set = (k, v) => setForm(prev => ({ ...prev, [k]: v }));

  const submit = async (e) => {
    e?.preventDefault?.();
    try {
      setLoading(true);
      const payload = { title: form.title, permissions: form.permissions };
      if (isEdit) {
        await axios.put(`${baseUrl}/roles/${editItem.id}`, payload);
        toast.success('Role updated');
      } else {
        await axios.post(`${baseUrl}/roles`, payload);
        toast.success('Role created');
      }
      onSaved?.();
      onClose?.();
    } catch (e) {
      console.error('Save role failed', e);
      toast.error('Gagal menyimpan role');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal open={open} onClose={onClose}>
      <ModalContent className="max-w-xl w-full">
        <ModalHeader>
          <ModalTitle>{isEdit ? 'Edit Role' : 'Tambah Role'}</ModalTitle>
        </ModalHeader>
        <ModalBody>
          <form className="flex flex-col gap-4" onSubmit={submit}>
            <div>
              <label className="form-label">Nama Role</label>
              <Input value={form.title} onChange={e => set('title', e.target.value)} required />
            </div>
            <div>
              <label className="form-label">Permissions</label>
              <div className="flex flex-wrap gap-2">
                <Select value={''} onValueChange={(val) => {
                  const id = Number(val);
                  if (!form.permissions.includes(id)) set('permissions', [...form.permissions, id]);
                }}>
                  <SelectTrigger size="sm">
                    <SelectValue placeholder="Tambah permission" />
                  </SelectTrigger>
                  <SelectContent>
                    {permissions.map(p => (
                      <SelectItem key={p.id} value={String(p.id)}>{p.title || p.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <div className="flex gap-2 items-center flex-wrap">
                  {(permissions.filter(p => form.permissions.includes(p.id))).map(p => (
                    <span key={p.id} className="badge badge-light flex items-center gap-1">
                      {p.title || p.name}
                      <button type="button" className="text-danger" onClick={() => set('permissions', form.permissions.filter(id => id !== p.id))}>×</button>
                    </span>
                  ))}
                </div>
              </div>
            </div>
            <div className="flex gap-2 justify-end mt-2">
              <button type="button" className="btn btn-light" onClick={onClose} disabled={loading}>Batal</button>
              <button type="submit" className="btn btn-primary" disabled={loading}>{loading ? 'Menyimpan...' : 'Simpan'}</button>
            </div>
          </form>
        </ModalBody>
      </ModalContent>
    </Modal>
  );
}
