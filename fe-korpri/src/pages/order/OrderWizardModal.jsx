import { useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { Modal, ModalContent, ModalHeader, ModalTitle } from '@/components';
import OrderWizard from '@/pages/order/OrderWizard';

export default function OrderWizardModal() {
  const navigate = useNavigate();
  const handleClose = useCallback(() => {
    // Close modal and go back to previous page
    navigate(-1);
  }, [navigate]);

  return (
    <Modal open={true} onClose={handleClose} className="flex items-start justify-center p-4 md:p-8">
      <ModalContent className="w-full max-w-3xl shadow-xl rounded-xl bg-white dark:bg-coal-100 outline-none">
        <ModalHeader className="flex items-center justify-between p-4 border-b">
          <ModalTitle className="text-lg font-semibold">Order Tiket</ModalTitle>
          <button aria-label="Close" className="btn btn-sm btn-light" onClick={handleClose}>&times;</button>
        </ModalHeader>
        <div className="p-4 md:p-6">
          <OrderWizard embedded />
        </div>
      </ModalContent>
    </Modal>
  );
}
