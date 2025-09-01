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

export default function UsersFormModal({ open, onClose, onSaved, editUser }) {
  const isEdit = !!editUser?.id;
  const [loading, setLoading] = useState(false);
  const [roles, setRoles] = useState([]);
  const [form, setForm] = useState({ name: '', email: '', password: '', roles: [], approved: false });

  useEffect(() => {
    const loadRoles = async () => {
      try {
        const { data } = await axios.get(`${baseUrl}/roles`);
        setRoles(data || []);
      } catch (e) {
        console.error('Failed fetching roles', e);
      }
    };
    loadRoles();
  }, []);

  useEffect(() => {
    if (editUser) {
      setForm({
        name: editUser.name || '',
        email: editUser.email || '',
        password: '',
        approved: !!editUser.approved,
        roles: (editUser.roles || []).map(r => r.id),
      });
    } else {
      setForm({ name: '', email: '', password: '', roles: [], approved: false });
    }
  }, [editUser]);

  const set = (k, v) => setForm(prev => ({ ...prev, [k]: v }));

  const submit = async (e) => {
    e?.preventDefault?.();
    try {
      setLoading(true);
      const payload = {
        name: form.name,
        email: form.email,
        approved: form.approved,
        roles: form.roles,
      };
      if (!isEdit || form.password) payload.password = form.password;
      if (isEdit) {
        await axios.put(`${baseUrl}/users/${editUser.id}`, payload);
        toast.success('User updated');
      } else {
        await axios.post(`${baseUrl}/users`, payload);
        toast.success('User created');
      }
      onSaved?.();
      onClose?.();
    } catch (e) {
      console.error('Save user failed', e);
      toast.error('Gagal menyimpan user');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal open={open} onClose={onClose}>
      <ModalContent className="max-w-xl w-full">
        <ModalHeader>
          <ModalTitle>{isEdit ? 'Edit User' : 'Tambah User'}</ModalTitle>
        </ModalHeader>
        <ModalBody>
          <form className="flex flex-col gap-4" onSubmit={submit}>
            <div>
              <label className="form-label">Nama</label>
              <Input value={form.name} onChange={e => set('name', e.target.value)} required />
            </div>
            <div>
              <label className="form-label">Email</label>
              <Input type="email" value={form.email} onChange={e => set('email', e.target.value)} required />
            </div>
            <div>
              <label className="form-label">Password {isEdit && <span className="text-2sm text-gray-500">(kosongkan jika tidak ganti)</span>}</label>
              <Input type="password" value={form.password} onChange={e => set('password', e.target.value)} minLength={isEdit ? undefined : 6} />
            </div>
            <div>
              <label className="form-label">Roles</label>
              <div className="flex flex-wrap gap-2">
                <Select value={''} onValueChange={(val) => {
                  const id = Number(val);
                  if (!form.roles.includes(id)) set('roles', [...form.roles, id]);
                }}>
                  <SelectTrigger size="sm">
                    <SelectValue placeholder="Tambah role" />
                  </SelectTrigger>
                  <SelectContent>
                    {roles.map(r => (
                      <SelectItem key={r.id} value={String(r.id)}>{r.title}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <div className="flex gap-2 items-center flex-wrap">
                  {(roles.filter(r => form.roles.includes(r.id))).map(r => (
                    <span key={r.id} className="badge badge-light flex items-center gap-1">
                      {r.title}
                      <button type="button" className="text-danger" onClick={() => set('roles', form.roles.filter(id => id !== r.id))}>×</button>
                    </span>
                  ))}
                </div>
              </div>
            </div>
            <label className="checkbox-group">
              <input className="checkbox checkbox-sm" type="checkbox" checked={form.approved} onChange={e => set('approved', e.target.checked)} />
              <span className="checkbox-label">Approved</span>
            </label>

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
