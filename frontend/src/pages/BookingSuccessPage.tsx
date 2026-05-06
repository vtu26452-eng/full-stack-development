import React from 'react';
import { useLocation, useNavigate, Link } from 'react-router-dom';
import { Booking } from '../types';

interface SuccessState {
  booking: Booking;
}

const BookingSuccessPage: React.FC = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const state = location.state as SuccessState | null;

  if (!state?.booking) {
    return (
      <div style={styles.center}>
        <p>No booking found. <button onClick={() => navigate('/')} style={styles.link}>Browse events</button></p>
      </div>
    );
  }

  const { booking } = state;

  const formatDate = (dateStr: string) =>
    new Date(dateStr).toLocaleDateString('en-IN', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
      hour: '2-digit', minute: '2-digit',
    });

  return (
    <main style={styles.main}>
      {/* Confetti-like header */}
      <div style={styles.successHeader}>
        <div style={styles.successIconWrapper}>
          <span style={styles.successIcon}>🎉</span>
        </div>
        <h1 style={styles.successTitle}>Booking Confirmed!</h1>
        <p style={styles.successSubtitle}>
          🎊 Woohoo! Your tickets are booked. Check your email for confirmation.
        </p>
      </div>

      <div style={styles.container}>
        {/* Reference number card */}
        <div style={styles.refCard}>
          <p style={styles.refLabel}>BOOKING REFERENCE</p>
          <p style={styles.refValue}>{booking.referenceNumber}</p>
          <p style={styles.refNote}>📧 Confirmation sent to your email</p>
        </div>

        {/* Booking details */}
        <div style={styles.detailsCard}>
          <h3 style={styles.detailsTitle}>📋 Booking Details</h3>
          <div style={styles.detailRow}>
            <span style={styles.detailLabel}>🎭 Event</span>
            <span style={styles.detailValue}>{booking.eventName}</span>
          </div>
          <div style={styles.detailRow}>
            <span style={styles.detailLabel}>📅 Date & Time</span>
            <span style={styles.detailValue}>{formatDate(booking.eventDateTime)}</span>
          </div>
          <div style={styles.detailRow}>
            <span style={styles.detailLabel}>📍 Venue</span>
            <span style={styles.detailValue}>{booking.venue}</span>
          </div>
          <div style={styles.detailRow}>
            <span style={styles.detailLabel}>🎫 Tickets</span>
            <span style={styles.detailValue}>{booking.quantity} ticket{booking.quantity > 1 ? 's' : ''}</span>
          </div>
          <div style={styles.divider} />
          <div style={styles.totalRow}>
            <span style={styles.totalLabel}>💰 Total Paid</span>
            <span style={styles.totalValue}>₹{booking.totalAmount.toFixed(2)}</span>
          </div>
        </div>

        {/* Action buttons */}
        <div style={styles.actions}>
          <Link to="/bookings" style={styles.primaryBtn}>📋 View My Bookings</Link>
          <Link to="/" style={styles.secondaryBtn}>🏠 Back to Events</Link>
        </div>
      </div>
    </main>
  );
};

const styles: Record<string, React.CSSProperties> = {
  main: { minHeight: '100vh', background: '#f0f4ff' },
  successHeader: {
    background: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
    padding: '60px 20px 40px',
    textAlign: 'center' as const,
  },
  successIconWrapper: { marginBottom: '16px' },
  successIcon: { fontSize: '72px' },
  successTitle: { fontSize: '40px', fontWeight: 900, color: '#fff', marginBottom: '12px' },
  successSubtitle: { fontSize: '18px', color: 'rgba(255,255,255,0.9)', maxWidth: '500px', margin: '0 auto' },
  container: { maxWidth: '560px', margin: '0 auto', padding: '32px 20px' },
  center: { textAlign: 'center', padding: '60px 20px' },
  refCard: {
    background: 'linear-gradient(135deg, #667eea, #764ba2)',
    borderRadius: '16px', padding: '28px', textAlign: 'center' as const,
    marginBottom: '20px', boxShadow: '0 8px 32px rgba(102,126,234,0.4)',
  },
  refLabel: { color: 'rgba(255,255,255,0.7)', fontSize: '12px', fontWeight: 700, letterSpacing: '2px', margin: '0 0 8px' },
  refValue: { color: '#fff', fontSize: '28px', fontWeight: 900, letterSpacing: '2px', margin: '0 0 8px' },
  refNote: { color: 'rgba(255,255,255,0.8)', fontSize: '13px', margin: 0 },
  detailsCard: { background: '#fff', borderRadius: '16px', padding: '24px', marginBottom: '20px', boxShadow: '0 4px 16px rgba(0,0,0,0.08)' },
  detailsTitle: { fontSize: '16px', fontWeight: 700, color: '#1a1a2e', marginBottom: '16px' },
  detailRow: { display: 'flex', justifyContent: 'space-between', marginBottom: '12px', gap: '12px' },
  detailLabel: { fontSize: '14px', color: '#6b7280', flexShrink: 0 },
  detailValue: { fontSize: '14px', color: '#374151', fontWeight: 500, textAlign: 'right' as const },
  divider: { borderTop: '1px solid #f3f4f6', margin: '16px 0' },
  totalRow: { display: 'flex', justifyContent: 'space-between', alignItems: 'center' },
  totalLabel: { fontSize: '16px', fontWeight: 700, color: '#1a1a2e' },
  totalValue: { fontSize: '24px', fontWeight: 900, color: '#11998e' },
  actions: { display: 'flex', flexDirection: 'column' as const, gap: '12px' },
  primaryBtn: { display: 'block', textAlign: 'center' as const, background: 'linear-gradient(135deg, #667eea, #764ba2)', color: '#fff', padding: '14px', borderRadius: '12px', textDecoration: 'none', fontWeight: 700, fontSize: '16px' },
  secondaryBtn: { display: 'block', textAlign: 'center' as const, background: '#fff', color: '#374151', padding: '14px', borderRadius: '12px', textDecoration: 'none', fontWeight: 600, fontSize: '16px', border: '2px solid #e5e7eb' },
  link: { background: 'none', border: 'none', color: '#667eea', cursor: 'pointer', fontSize: '16px', textDecoration: 'underline' },
};

export default BookingSuccessPage;
