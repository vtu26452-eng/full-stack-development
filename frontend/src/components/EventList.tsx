import React from 'react';
import { Event } from '../types';
import EventCard from './EventCard';

interface EventListProps {
  events: Event[];
  loading: boolean;
  error: string | null;
}

const EventList: React.FC<EventListProps> = ({ events, loading, error }) => {
  if (loading) {
    return (
      <div style={styles.center} role="status" aria-live="polite">
        <div style={styles.spinner} aria-hidden="true" />
        <p>Loading events...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div style={styles.error} role="alert">
        <p>⚠️ {error}</p>
      </div>
    );
  }

  if (events.length === 0) {
    return (
      <div style={styles.center}>
        <p style={styles.empty}>No events found. Try adjusting your filters.</p>
      </div>
    );
  }

  return (
    <div style={styles.grid} role="list" aria-label="Event listings">
      {events.map((event) => (
        <div key={event.id} role="listitem">
          <EventCard event={event} />
        </div>
      ))}
    </div>
  );
};

const styles: Record<string, React.CSSProperties> = {
  grid: { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: '24px' },
  center: { textAlign: 'center', padding: '60px 20px', color: '#6b7280' },
  spinner: { width: '40px', height: '40px', border: '4px solid #e5e7eb', borderTop: '4px solid #4f46e5', borderRadius: '50%', animation: 'spin 1s linear infinite', margin: '0 auto 16px' },
  error: { background: '#fee2e2', color: '#dc2626', padding: '16px', borderRadius: '8px', textAlign: 'center' },
  empty: { fontSize: '16px' },
};

export default EventList;
