import { useEffect, useState } from 'react';
import axios from 'axios';
import { toast } from 'sonner';
import { Modal } from '@/components/modal/Modal';
import { ModalContent } from '@/components/modal/ModalContent';
import { ModalHeader } from '@/components/modal/ModalHeader';
import { ModalTitle } from '@/components/modal/ModalTitle';
import { ModalBody } from '@/components/modal/ModalBody';
import { Input } from '@/components/ui/input';

const baseUrl = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

export default function PermissionFormModal({ open, onClose, onSaved, editItem }) {
  const isEdit = !!editItem?.id;
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState({ title: '' });

  useEffect(() => {
    if (editItem) {
      setForm({ title: editItem.title || editItem.name || '' });
    } else {
      setForm({ title: '' });
    }
  }, [editItem]);

  const set = (k, v) => setForm(prev => ({ ...prev, [k]: v }));

  const submit = async (e) => {
    e?.preventDefault?.();
    try {
      setLoading(true);
      const payload = { title: form.title };
      if (isEdit) {
        await axios.put(`${baseUrl}/permissions/${editItem.id}`, payload);
        toast.success('Permission updated');
      } else {
        await axios.post(`${baseUrl}/permissions`, payload);
        toast.success('Permission created');
      }
      onSaved?.();
      onClose?.();
    } catch (e) {
      console.error('Save permission failed', e);
      toast.error('Gagal menyimpan permission');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal open={open} onClose={onClose}>
      <ModalContent className="max-w-md w-full">
        <ModalHeader>
          <ModalTitle>{isEdit ? 'Edit Permission' : 'Tambah Permission'}</ModalTitle>
        </ModalHeader>
        <ModalBody>
          <form className="flex flex-col gap-4" onSubmit={submit}>
            <div>
              <label className="form-label">Nama Permission</label>
              <Input value={form.title} onChange={e => set('title', e.target.value)} required />
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
