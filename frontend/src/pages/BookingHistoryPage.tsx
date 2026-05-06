import React, { useState, useEffect } from 'react';
import BookingCard from '../components/BookingCard';
import { Booking } from '../types';
import { getBookings } from '../api/bookings';
import { Link } from 'react-router-dom';

const BookingHistoryPage: React.FC = () => {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    getBookings()
      .then(setBookings)
      .catch(() => setError('Failed to load bookings. Please try again.'))
      .finally(() => setLoading(false));
  }, []);

  return (
    <main style={styles.main}>
      <div style={styles.container}>
        <h1 style={styles.title}>My Bookings</h1>

        {loading && (
          <div style={styles.center} role="status" aria-live="polite">
            <p>Loading your bookings...</p>
          </div>
        )}

        {error && (
          <div style={styles.error} role="alert">{error}</div>
        )}

        {!loading && !error && bookings.length === 0 && (
          <div style={styles.empty}>
            <p style={styles.emptyText}>You haven't booked any events yet.</p>
            <Link to="/" style={styles.browseBtn}>Browse Events</Link>
          </div>
        )}

        {!loading && !error && bookings.length > 0 && (
          <div role="list" aria-label="My bookings">
            {bookings.map((booking) => (
              <div key={booking.id} role="listitem">
                <BookingCard booking={booking} />
              </div>
            ))}
          </div>
        )}
      </div>
    </main>
  );
};

const styles: Record<string, React.CSSProperties> = {
  main: { minHeight: '100vh', background: '#f8fafc', padding: '32px 20px' },
  container: { maxWidth: '720px', margin: '0 auto' },
  title: { fontSize: '32px', fontWeight: 800, color: '#1a1a2e', marginBottom: '24px' },
  center: { textAlign: 'center', padding: '40px 20px', color: '#6b7280' },
  error: { background: '#fee2e2', color: '#dc2626', padding: '16px', borderRadius: '8px' },
  empty: { textAlign: 'center', padding: '60px 20px' },
  emptyText: { fontSize: '18px', color: '#6b7280', marginBottom: '20px' },
  browseBtn: { display: 'inline-block', background: '#4f46e5', color: '#fff', padding: '12px 24px', borderRadius: '10px', textDecoration: 'none', fontWeight: 600 },
};

export default BookingHistoryPage;
