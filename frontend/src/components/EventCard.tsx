import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Event } from '../types';

interface EventCardProps {
  event: Event;
}

const categoryConfig: Record<string, { color: string; bg: string; emoji: string }> = {
  TECHNICAL: { color: '#fff', bg: 'linear-gradient(135deg, #667eea, #764ba2)', emoji: '💻' },
  CULTURAL: { color: '#fff', bg: 'linear-gradient(135deg, #f093fb, #f5576c)', emoji: '🎭' },
  SPORTS: { color: '#fff', bg: 'linear-gradient(135deg, #11998e, #38ef7d)', emoji: '🏆' },
  SEMINAR: { color: '#fff', bg: 'linear-gradient(135deg, #f7971e, #ffd200)', emoji: '🎓' },
  WORKSHOP: { color: '#fff', bg: 'linear-gradient(135deg, #4facfe, #00f2fe)', emoji: '🔧' },
};

const EventCard: React.FC<EventCardProps> = ({ event }) => {
  const navigate = useNavigate();
  const [hovered, setHovered] = useState(false);
  const isSoldOut = event.remainingTickets === 0;
  const config = categoryConfig[event.category] || { color: '#fff', bg: 'linear-gradient(135deg, #6b7280, #9ca3af)', emoji: '🎫' };

  const formatDate = (dateStr: string) =>
    new Date(dateStr).toLocaleDateString('en-IN', {
      day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });

  return (
    <article
      style={{ ...styles.card, ...(hovered ? styles.cardHovered : {}) }}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
      aria-label={`Event: ${event.name}`}
    >
      {/* Category banner */}
      <div style={{ ...styles.banner, background: config.bg }}>
        <span style={styles.bannerEmoji}>{config.emoji}</span>
        <span style={styles.bannerCategory}>{event.category}</span>
        {isSoldOut && <span style={styles.soldOutBadge}>SOLD OUT</span>}
        {!isSoldOut && event.remainingTickets <= 20 && (
          <span style={styles.urgentBadge}>🔥 Only {event.remainingTickets} left!</span>
        )}
      </div>

      <div style={styles.body}>
        <h3 style={styles.title}>{event.name}</h3>
        <p style={styles.organizer}>🏛️ {event.organizer}</p>
        <p style={styles.detail}>📍 {event.venue}</p>
        <p style={styles.detail}>📅 {formatDate(event.dateTime)}</p>

        <div style={styles.footer}>
          <div style={styles.priceSection}>
            <span style={styles.price}>₹{event.ticketPrice}</span>
            <span style={styles.perTicket}>/ticket</span>
          </div>
          <button
            onClick={() => navigate(`/events/${event.id}`)}
            style={{ ...styles.button, ...(isSoldOut ? styles.buttonDisabled : styles.buttonActive) }}
            disabled={isSoldOut}
          >
            {isSoldOut ? '😔 Sold Out' : '🎫 Book Now'}
          </button>
        </div>
      </div>
    </article>
  );
};

const styles: Record<string, React.CSSProperties> = {
  card: { background: '#fff', borderRadius: '16px', overflow: 'hidden', boxShadow: '0 4px 16px rgba(0,0,0,0.08)', transition: 'all 0.3s ease', display: 'flex', flexDirection: 'column' as const },
  cardHovered: { transform: 'translateY(-6px)', boxShadow: '0 16px 40px rgba(0,0,0,0.15)' },
  banner: { padding: '20px 16px', display: 'flex', alignItems: 'center', gap: '8px', minHeight: '80px' },
  bannerEmoji: { fontSize: '28px' },
  bannerCategory: { color: '#fff', fontSize: '12px', fontWeight: 800, letterSpacing: '1px', textTransform: 'uppercase' as const, flex: 1 },
  soldOutBadge: { background: 'rgba(0,0,0,0.3)', color: '#fff', fontSize: '10px', fontWeight: 800, padding: '3px 8px', borderRadius: '20px' },
  urgentBadge: { background: 'rgba(255,255,255,0.25)', color: '#fff', fontSize: '11px', fontWeight: 700, padding: '3px 8px', borderRadius: '20px' },
  body: { padding: '16px', flex: 1, display: 'flex', flexDirection: 'column' as const, gap: '6px' },
  title: { fontSize: '16px', fontWeight: 700, color: '#1a1a2e', margin: '0 0 4px', lineHeight: 1.3 },
  organizer: { fontSize: '13px', color: '#6366f1', fontWeight: 600, margin: 0 },
  detail: { fontSize: '12px', color: '#6b7280', margin: 0 },
  footer: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 'auto', paddingTop: '12px', borderTop: '1px solid #f3f4f6' },
  priceSection: { display: 'flex', alignItems: 'baseline', gap: '2px' },
  price: { fontSize: '20px', fontWeight: 800, color: '#1a1a2e' },
  perTicket: { fontSize: '11px', color: '#9ca3af' },
  button: { padding: '8px 16px', borderRadius: '8px', border: 'none', cursor: 'pointer', fontSize: '13px', fontWeight: 700 },
  buttonActive: { background: 'linear-gradient(135deg, #667eea, #764ba2)', color: '#fff' },
  buttonDisabled: { background: '#e5e7eb', color: '#9ca3af', cursor: 'not-allowed' },
};

export default EventCard;
