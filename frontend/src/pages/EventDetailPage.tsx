import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Event } from '../types';
import { getEventById } from '../api/events';
import { useAuth } from '../context/AuthContext';

const categoryColors: Record<string, string> = {
  TECHNICAL: '#3b82f6', CULTURAL: '#ec4899', SPORTS: '#10b981',
  SEMINAR: '#f59e0b', WORKSHOP: '#8b5cf6',
};

const EventDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const [event, setEvent] = useState<Event | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    if (!id) return;
    getEventById(id)
      .then(setEvent)
      .catch(() => setError('Event not found.'))
      .finally(() => setLoading(false));
  }, [id]);

  const handleBookNow = () => {
    if (!isAuthenticated) {
      navigate('/login', { state: { from: { pathname: `/events/${id}` } } });
      return;
    }
    navigate('/checkout', { state: { event, quantity } });
  };

  if (loading) return <div style={styles.center}><p>Loading event details...</p></div>;
  if (error || !event) return <div style={styles.center}><p style={{ color: '#dc2626' }}>{error || 'Event not found.'}</p></div>;

  const isSoldOut = event.remainingTickets === 0;
  const maxQty = Math.min(5, event.remainingTickets);
  const categoryColor = categoryColors[event.category] || '#6b7280';

  const formatDate = (dateStr: string) =>
    new Date(dateStr).toLocaleDateString('en-IN', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });

  return (
    <main style={styles.main}>
      <div style={styles.container}>
        <button onClick={() => navigate(-1)} style={styles.back} aria-label="Go back">← Back to Events</button>
        <div style={styles.card}>
          {event.imageUrl && (
            <img src={event.imageUrl} alt={event.name} style={styles.image}
              onError={(e) => { (e.target as HTMLImageElement).style.display = 'none'; }} />
          )}
          <div style={styles.content}>
            <div style={styles.header}>
              <span style={{ ...styles.badge, background: categoryColor }}>{event.category}</span>
              {isSoldOut && <span style={styles.soldOut}>SOLD OUT</span>}
            </div>
            <h1 style={styles.title}>{event.name}</h1>
            <p style={styles.organizer}>🏛️ Organized by {event.organizer}</p>
            <p style={styles.description}>{event.description}</p>
            <div style={styles.details}>
              <div style={styles.detailItem}><span style={styles.detailIcon}>📍</span><span>{event.venue}</span></div>
              <div style={styles.detailItem}><span style={styles.detailIcon}>📅</span><span>{formatDate(event.dateTime)}</span></div>
              <div style={styles.detailItem}><span style={styles.detailIcon}>🎫</span><span>{isSoldOut ? 'No tickets available' : `${event.remainingTickets} of ${event.totalTickets} tickets remaining`}</span></div>
            </div>
            {!isSoldOut && (
              <div style={styles.booking}>
                <div style={styles.priceSection}>
                  <span style={styles.price}>₹{event.ticketPrice}</span>
                  <span style={styles.perTicket}> per ticket</span>
                </div>
                <div style={styles.quantitySection}>
                  <label htmlFor="quantity" style={styles.qtyLabel}>Quantity</label>
                  <select
                    id="quantity"
                    value={quantity}
                    onChange={(e) => setQuantity(Number(e.target.value))}
                    style={styles.qtySelect}
                    aria-label="Select ticket quantity"
                  >
                    {Array.from({ length: maxQty }, (_, i) => i + 1).map((n) => (
                      <option key={n} value={n}>{n}</option>
                    ))}
                  </select>
                </div>
                <div style={styles.total}>
                  Total: <strong>₹{(event.ticketPrice * quantity).toFixed(2)}</strong>
                </div>
                <button onClick={handleBookNow} style={styles.bookBtn} aria-label={`Book ${quantity} ticket(s) for ${event.name}`}>
                  {isAuthenticated ? `Proceed to Checkout` : 'Sign In to Book'}
                </button>
              </div>
            )}
            {isSoldOut && (
              <div style={styles.soldOutMsg}>
                <p>This event is sold out. Check back later for cancellations.</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </main>
  );
};

const styles: Record<string, React.CSSProperties> = {
  main: { minHeight: '100vh', background: '#f8fafc', padding: '20px' },
  container: { maxWidth: '900px', margin: '0 auto' },
  center: { textAlign: 'center', padding: '60px 20px' },
  back: { background: 'none', border: 'none', color: '#4f46e5', cursor: 'pointer', fontSize: '15px', marginBottom: '20px', padding: 0 },
  card: { background: '#fff', borderRadius: '16px', overflow: 'hidden', boxShadow: '0 4px 20px rgba(0,0,0,0.1)' },
  image: { width: '100%', height: '300px', objectFit: 'cover' },
  content: { padding: '32px' },
  header: { display: 'flex', gap: '12px', alignItems: 'center', marginBottom: '12px' },
  badge: { color: '#fff', fontSize: '12px', fontWeight: 700, padding: '4px 10px', borderRadius: '20px' },
  soldOut: { background: '#fee2e2', color: '#dc2626', fontSize: '12px', fontWeight: 700, padding: '4px 10px', borderRadius: '20px' },
  title: { fontSize: '32px', fontWeight: 800, color: '#1a1a2e', margin: '0 0 8px' },
  organizer: { color: '#6366f1', fontWeight: 600, marginBottom: '16px' },
  description: { color: '#4b5563', lineHeight: 1.7, marginBottom: '24px' },
  details: { display: 'flex', flexDirection: 'column', gap: '10px', marginBottom: '28px', padding: '20px', background: '#f8fafc', borderRadius: '12px' },
  detailItem: { display: 'flex', gap: '10px', alignItems: 'flex-start', fontSize: '15px', color: '#374151' },
  detailIcon: { fontSize: '18px', flexShrink: 0 },
  booking: { borderTop: '1px solid #e5e7eb', paddingTop: '24px' },
  priceSection: { marginBottom: '16px' },
  price: { fontSize: '32px', fontWeight: 800, color: '#1a1a2e' },
  perTicket: { color: '#9ca3af', fontSize: '16px' },
  quantitySection: { display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '16px' },
  qtyLabel: { fontWeight: 600, color: '#374151' },
  qtySelect: { padding: '8px 12px', border: '1px solid #d1d5db', borderRadius: '8px', fontSize: '16px' },
  total: { fontSize: '18px', color: '#374151', marginBottom: '20px' },
  bookBtn: { background: '#4f46e5', color: '#fff', border: 'none', padding: '14px 32px', borderRadius: '10px', fontSize: '16px', fontWeight: 700, cursor: 'pointer', width: '100%' },
  soldOutMsg: { background: '#fee2e2', color: '#dc2626', padding: '16px', borderRadius: '8px', textAlign: 'center' },
};

export default EventDetailPage;
