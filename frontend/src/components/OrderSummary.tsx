import React from 'react';
import { Event } from '../types';

interface OrderSummaryProps {
  event: Event;
  quantity: number;
}

const OrderSummary: React.FC<OrderSummaryProps> = ({ event, quantity }) => {
  const total = event.ticketPrice * quantity;

  const formatDate = (dateStr: string) =>
    new Date(dateStr).toLocaleDateString('en-IN', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
      hour: '2-digit', minute: '2-digit',
    });

  return (
    <div style={styles.container} aria-label="Order summary">
      <h3 style={styles.heading}>Order Summary</h3>
      <div style={styles.row}>
        <span style={styles.label}>Event</span>
        <span style={styles.value}>{event.name}</span>
      </div>
      <div style={styles.row}>
        <span style={styles.label}>Date & Time</span>
        <span style={styles.value}>{formatDate(event.dateTime)}</span>
      </div>
      <div style={styles.row}>
        <span style={styles.label}>Venue</span>
        <span style={styles.value}>{event.venue}</span>
      </div>
      <div style={styles.row}>
        <span style={styles.label}>Tickets</span>
        <span style={styles.value}>{quantity} × ₹{event.ticketPrice}</span>
      </div>
      <div style={styles.divider} />
      <div style={styles.totalRow}>
        <span style={styles.totalLabel}>Total</span>
        <span style={styles.totalValue}>₹{total.toFixed(2)}</span>
      </div>
    </div>
  );
};

const styles: Record<string, React.CSSProperties> = {
  container: { background: '#f8fafc', border: '1px solid #e5e7eb', borderRadius: '12px', padding: '20px', marginBottom: '24px' },
  heading: { fontSize: '16px', fontWeight: 700, color: '#1a1a2e', marginBottom: '16px' },
  row: { display: 'flex', justifyContent: 'space-between', marginBottom: '10px', gap: '12px' },
  label: { fontSize: '14px', color: '#6b7280', flexShrink: 0 },
  value: { fontSize: '14px', color: '#374151', textAlign: 'right' as const },
  divider: { borderTop: '1px solid #e5e7eb', margin: '12px 0' },
  totalRow: { display: 'flex', justifyContent: 'space-between', alignItems: 'center' },
  totalLabel: { fontSize: '16px', fontWeight: 700, color: '#1a1a2e' },
  totalValue: { fontSize: '22px', fontWeight: 800, color: '#4f46e5' },
};

export default OrderSummary;
