import React, { useState } from 'react';
import { PaymentFormData } from '../types';

interface PaymentFormProps {
  onSubmit: (data: PaymentFormData) => void;
  loading: boolean;
}

const currentYear = new Date().getFullYear();

const PaymentForm: React.FC<PaymentFormProps> = ({ onSubmit, loading }) => {
  const [form, setForm] = useState<PaymentFormData>({
    cardholderName: '',
    cardNumber: '',
    expiryMonth: '',
    expiryYear: '',
    cvv: '',
    simulateFailure: false,
  });
  const [errors, setErrors] = useState<Partial<Record<keyof PaymentFormData, string>>>({});

  const validate = (): boolean => {
    const newErrors: Partial<Record<keyof PaymentFormData, string>> = {};
    if (!form.cardholderName.trim()) newErrors.cardholderName = 'Cardholder name is required.';
    if (!/^\d{16}$/.test(form.cardNumber)) newErrors.cardNumber = 'Card number must be exactly 16 digits.';
    if (!/^(0[1-9]|1[0-2])$/.test(form.expiryMonth)) newErrors.expiryMonth = 'Enter a valid month (01–12).';
    if (!/^\d{4}$/.test(form.expiryYear) || parseInt(form.expiryYear) < currentYear)
      newErrors.expiryYear = `Expiry year must be ${currentYear} or later.`;
    if (!/^\d{3}$/.test(form.cvv)) newErrors.cvv = 'CVV must be exactly 3 digits.';
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleChange = (field: keyof PaymentFormData, value: string | boolean) => {
    setForm((prev) => ({ ...prev, [field]: value }));
    if (errors[field]) setErrors((prev) => ({ ...prev, [field]: undefined }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (validate()) onSubmit(form);
  };

  return (
    <form onSubmit={handleSubmit} aria-label="Payment form" noValidate>
      <h3 style={styles.heading}>Payment Details</h3>

      <div style={styles.field}>
        <label htmlFor="cardholderName" style={styles.label}>Cardholder Name</label>
        <input
          id="cardholderName"
          type="text"
          value={form.cardholderName}
          onChange={(e) => handleChange('cardholderName', e.target.value)}
          style={{ ...styles.input, ...(errors.cardholderName ? styles.inputError : {}) }}
          placeholder="John Doe"
          aria-describedby={errors.cardholderName ? 'cardholderName-error' : undefined}
          aria-invalid={!!errors.cardholderName}
        />
        {errors.cardholderName && <span id="cardholderName-error" style={styles.error} role="alert">{errors.cardholderName}</span>}
      </div>

      <div style={styles.field}>
        <label htmlFor="cardNumber" style={styles.label}>Card Number</label>
        <input
          id="cardNumber"
          type="text"
          value={form.cardNumber}
          onChange={(e) => handleChange('cardNumber', e.target.value.replace(/\D/g, '').slice(0, 16))}
          style={{ ...styles.input, ...(errors.cardNumber ? styles.inputError : {}) }}
          placeholder="1234567890123456"
          maxLength={16}
          aria-describedby={errors.cardNumber ? 'cardNumber-error' : undefined}
          aria-invalid={!!errors.cardNumber}
        />
        {errors.cardNumber && <span id="cardNumber-error" style={styles.error} role="alert">{errors.cardNumber}</span>}
      </div>

      <div style={styles.row}>
        <div style={{ ...styles.field, flex: 1 }}>
          <label htmlFor="expiryMonth" style={styles.label}>Expiry Month</label>
          <input
            id="expiryMonth"
            type="text"
            value={form.expiryMonth}
            onChange={(e) => handleChange('expiryMonth', e.target.value.replace(/\D/g, '').slice(0, 2))}
            style={{ ...styles.input, ...(errors.expiryMonth ? styles.inputError : {}) }}
            placeholder="MM"
            maxLength={2}
            aria-describedby={errors.expiryMonth ? 'expiryMonth-error' : undefined}
            aria-invalid={!!errors.expiryMonth}
          />
          {errors.expiryMonth && <span id="expiryMonth-error" style={styles.error} role="alert">{errors.expiryMonth}</span>}
        </div>
        <div style={{ ...styles.field, flex: 1 }}>
          <label htmlFor="expiryYear" style={styles.label}>Expiry Year</label>
          <input
            id="expiryYear"
            type="text"
            value={form.expiryYear}
            onChange={(e) => handleChange('expiryYear', e.target.value.replace(/\D/g, '').slice(0, 4))}
            style={{ ...styles.input, ...(errors.expiryYear ? styles.inputError : {}) }}
            placeholder="YYYY"
            maxLength={4}
            aria-describedby={errors.expiryYear ? 'expiryYear-error' : undefined}
            aria-invalid={!!errors.expiryYear}
          />
          {errors.expiryYear && <span id="expiryYear-error" style={styles.error} role="alert">{errors.expiryYear}</span>}
        </div>
        <div style={{ ...styles.field, flex: 1 }}>
          <label htmlFor="cvv" style={styles.label}>CVV</label>
          <input
            id="cvv"
            type="text"
            value={form.cvv}
            onChange={(e) => handleChange('cvv', e.target.value.replace(/\D/g, '').slice(0, 3))}
            style={{ ...styles.input, ...(errors.cvv ? styles.inputError : {}) }}
            placeholder="123"
            maxLength={3}
            aria-describedby={errors.cvv ? 'cvv-error' : undefined}
            aria-invalid={!!errors.cvv}
          />
          {errors.cvv && <span id="cvv-error" style={styles.error} role="alert">{errors.cvv}</span>}
        </div>
      </div>

      <div style={styles.checkboxField}>
        <input
          id="simulateFailure"
          type="checkbox"
          checked={form.simulateFailure}
          onChange={(e) => handleChange('simulateFailure', e.target.checked)}
          style={styles.checkbox}
          aria-label="Simulate payment failure for testing"
        />
        <label htmlFor="simulateFailure" style={styles.checkboxLabel}>
          Simulate Payment Failure (for testing)
        </label>
      </div>

      <button type="submit" style={styles.button} disabled={loading} aria-label="Pay now">
        {loading ? 'Processing...' : 'Pay Now'}
      </button>
    </form>
  );
};

const styles: Record<string, React.CSSProperties> = {
  heading: { fontSize: '18px', fontWeight: 700, color: '#1a1a2e', marginBottom: '20px' },
  field: { marginBottom: '16px' },
  row: { display: 'flex', gap: '12px' },
  label: { display: 'block', marginBottom: '6px', fontWeight: 500, color: '#374151', fontSize: '14px' },
  input: { width: '100%', padding: '10px 14px', border: '1px solid #d1d5db', borderRadius: '8px', fontSize: '16px', boxSizing: 'border-box' as const },
  inputError: { borderColor: '#dc2626' },
  error: { color: '#dc2626', fontSize: '12px', marginTop: '4px', display: 'block' },
  checkboxField: { display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px', padding: '12px', background: '#fef3c7', borderRadius: '8px' },
  checkbox: { width: '16px', height: '16px', cursor: 'pointer' },
  checkboxLabel: { fontSize: '14px', color: '#92400e', cursor: 'pointer' },
  button: { width: '100%', padding: '14px', background: '#4f46e5', color: '#fff', border: 'none', borderRadius: '10px', fontSize: '16px', fontWeight: 700, cursor: 'pointer' },
};

export default PaymentForm;
