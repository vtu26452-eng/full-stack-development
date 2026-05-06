import React from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import OrderSummary from '../components/OrderSummary';
import { Event } from '../types';

interface CheckoutState {
  event: Event;
  quantity: number;
}

const CheckoutPage: React.FC = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const state = location.state as CheckoutState | null;

  if (!state?.event) {
    return (
      <div style={styles.center}>
        <p>No event selected. <button onClick={() => navigate('/')} style={styles.link}>Browse events</button></p>
      </div>
    );
  }

  const { event, quantity } = state;

  return (
    <main style={styles.main}>
      <div style={styles.container}>
        <button onClick={() => navigate(-1)} style={styles.back} aria-label="Go back">← Back</button>
        <h1 style={styles.title}>Checkout</h1>
        <OrderSummary event={event} quantity={quantity} />
        <button
          onClick={() => navigate('/payment', { state: { event, quantity } })}
          style={styles.button}
          aria-label="Proceed to payment"
        >
          Proceed to Payment →
        </button>
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
  button: { width: '100%', padding: '14px', background: '#4f46e5', color: '#fff', border: 'none', borderRadius: '10px', fontSize: '16px', fontWeight: 700, cursor: 'pointer' },
  link: { background: 'none', border: 'none', color: '#4f46e5', cursor: 'pointer', fontSize: '16px', textDecoration: 'underline' },
};

export default CheckoutPage;
