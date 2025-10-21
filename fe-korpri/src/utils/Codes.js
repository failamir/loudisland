const CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
const pick = () => CHARS[Math.floor(Math.random() * CHARS.length)];
const randomCode = len => Array.from({ length: len }, pick).join('');
export const normalizeCode = v => (v || '').toString().trim().toUpperCase();
export const generateReferralCode = (length = 8, prefix = 'REF') => {
  const body = randomCode(length);
  return `${prefix}-${body}`;
};
export const generatePromoCode = (length = 8, prefix = 'PROMO') => {
  const body = randomCode(length);
  return `${prefix}-${body}`;
};
export const isValidCodeFormat = (code, { prefix, length = 8 } = {}) => {
  const c = normalizeCode(code);
  const p = prefix ? `${normalizeCode(prefix)}-` : '';
  const re = new RegExp(`^${p}[A-Z0-9]{${length}}$`);
  return re.test(c);
};
export const parseCodeType = code => {
  const c = normalizeCode(code);
  if (c.startsWith('REF-')) return 'referral';
  if (c.startsWith('PROMO-')) return 'promo';
  return null;
};
