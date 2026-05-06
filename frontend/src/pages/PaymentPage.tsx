import React, { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import OrderSummary from '../components/OrderSummary';
import PaymentForm from '../components/PaymentForm';
import { Event, PaymentFormData, BookingRequest } from '../types';
import { createBooking } from '../api/bookings';

interface PaymentState {
  event: Event;
  quantity: number;
}

const PaymentPage: React.FC = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const state = location.state as PaymentState | null;
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  if (!state?.event) {
    return (
      <div style={styles.center}>
        <p>No event selected. <button onClick={() => navigate('/')} style={styles.link}>Browse events</button></p>
      </div>
    );
  }

  const { event, quantity } = state;

  const handlePayment = async (paymentData: PaymentFormData) => {
    setLoading(true);
    setError(null);
    try {
      const request: BookingRequest = {
        eventId: event.id,
        quantity,
        payment: paymentData,
      };
      const booking = await createBooking(request);
      navigate('/booking-success', { state: { booking } });
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } };
      setError(axiosError.response?.data?.message || 'Payment failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <main style={styles.main}>
      <div style={styles.container}>
        <button onClick={() => navigate(-1)} style={styles.back} aria-label="Go back">← Back to Checkout</button>
        <h1 style={styles.title}>Payment</h1>
        <OrderSummary event={event} quantity={quantity} />
        {error && <div style={styles.error} role="alert">{error}</div>}
        <div style={styles.card}>
          <PaymentForm onSubmit={handlePayment} loading={loading} />
        </div>
      </div>
    </main>
  );
};

const styles: Record<string, React.CSSProperties> = {
  main: { minHeight: '100vh', background: '#f8fafc', padding: '20px' },
  container: { maxWidth: '560px', margin: '0 auto' },
  center: { textAlign: 'center', padding: '60px 20px' },
  back: { background: 'none', border: 'none', color: '#4f46e5', cursor: 'pointer', fontSize: '15px', marginBottom: '20px', padding: 0 },
  title: { fontSize: '28px', fontWeight: 800, color: '#1a1a2e', marginBottom: '24px' },
  error: { background: '#fee2e2', color: '#dc2626', padding: '12px', borderRadius: '8px', marginBottom: '16px', fontSize: '14px' },
  card: { background: '#fff', borderRadius: '12px', padding: '24px', boxShadow: '0 2px 12px rgba(0,0,0,0.08)' },
  link: { background: 'none', border: 'none', color: '#4f46e5', cursor: 'pointer', fontSize: '16px', textDecoration: 'underline' },
};

export default PaymentPage;
