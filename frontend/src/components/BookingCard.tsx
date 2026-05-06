import React from 'react';
import { Booking } from '../types';

interface BookingCardProps {
  booking: Booking;
}

const statusColors: Record<string, { bg: string; color: string }> = {
  CONFIRMED: { bg: '#d1fae5', color: '#065f46' },
  PENDING: { bg: '#fef3c7', color: '#92400e' },
  FAILED: { bg: '#fee2e2', color: '#dc2626' },
};

const BookingCard: React.FC<BookingCardProps> = ({ booking }) => {
  const statusStyle = statusColors[booking.status] || statusColors.PENDING;

  const formatDate = (dateStr: string) =>
    new Date(dateStr).toLocaleDateString('en-IN', {
      day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });

  return (
    <article style={styles.card} aria-label={`Booking ${booking.referenceNumber}`}>
      <div style={styles.header}>
        <div>
          <p style={styles.ref}>Ref: {booking.referenceNumber}</p>
          <h3 style={styles.eventName}>{booking.eventName}</h3>
        </div>
        <span style={{ ...styles.status, background: statusStyle.bg, color: statusStyle.color }}>
          {booking.status}
        </span>
      </div>
      <div style={styles.details}>
        <p style={styles.detail}>📅 {formatDate(booking.eventDateTime)}</p>
        <p style={styles.detail}>📍 {booking.venue}</p>
        <p style={styles.detail}>🎫 {booking.quantity} ticket{booking.quantity > 1 ? 's' : ''}</p>
      </div>
      <div style={styles.footer}>
        <span style={styles.total}>Total: ₹{booking.totalAmount.toFixed(2)}</span>
        <span style={styles.bookedOn}>Booked on {formatDate(booking.createdAt)}</span>
      </div>
    </article>
  );
};

const styles: Record<string, React.CSSProperties> = {
  card: { background: '#fff', borderRadius: '12px', padding: '20px', boxShadow: '0 2px 8px rgba(0,0,0,0.08)', marginBottom: '16px' },
  header: { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '12px' },
  ref: { fontSize: '12px', color: '#9ca3af', margin: '0 0 4px' },
  eventName: { fontSize: '18px', fontWeight: 700, color: '#1a1a2e', margin: 0 },
  status: { fontSize: '12px', fontWeight: 700, padding: '4px 10px', borderRadius: '20px', flexShrink: 0 },
  details: { display: 'flex', flexDirection: 'column', gap: '6px', marginBottom: '12px' },
  detail: { fontSize: '14px', color: '#6b7280', margin: 0 },
  footer: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: '1px solid #f3f4f6', paddingTop: '12px' },
  total: { fontSize: '18px', fontWeight: 700, color: '#4f46e5' },
  bookedOn: { fontSize: '12px', color: '#9ca3af' },
};

export default BookingCard;
