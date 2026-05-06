import React, { useState, useEffect } from 'react';
import EventList from '../components/EventList';
import EventFilter from '../components/EventFilter';
import { Event } from '../types';
import { getEvents } from '../api/events';

const HomePage: React.FC = () => {
  const [events, setEvents] = useState<Event[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [category, setCategory] = useState('ALL');
  const [date, setDate] = useState('');

  const fetchEvents = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await getEvents({ category, date });
      setEvents(data);
    } catch {
      setError('Failed to load events. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEvents();
  }, [category, date]);

  const handleClear = () => {
    setCategory('ALL');
    setDate('');
  };

  return (
    <main style={styles.main}>
      {/* Hero Section */}
      <div style={styles.hero}>
        <div style={styles.heroContent}>
          <div style={styles.heroBadge}>🎓 Tamil Nadu's #1 College Event Platform</div>
          <h1 style={styles.heroTitle}>Discover Amazing<br /><span style={styles.heroHighlight}>College Events</span></h1>
          <p style={styles.heroSubtitle}>Book tickets for hackathons, cultural fests, sports meets, seminars & more!</p>
          <div style={styles.heroStats}>
            <div style={styles.stat}><span style={styles.statNum}>10+</span><span style={styles.statLabel}>Events</span></div>
            <div style={styles.statDivider} />
            <div style={styles.stat}><span style={styles.statNum}>5</span><span style={styles.statLabel}>Categories</span></div>
            <div style={styles.statDivider} />
            <div style={styles.stat}><span style={styles.statNum}>8+</span><span style={styles.statLabel}>Colleges</span></div>
          </div>
        </div>
        <div style={styles.heroEmojis}>
          <span style={styles.floatingEmoji1}>🎭</span>
          <span style={styles.floatingEmoji2}>🏆</span>
          <span style={styles.floatingEmoji3}>💻</span>
          <span style={styles.floatingEmoji4}>🎵</span>
        </div>
      </div>

      {/* Events Section */}
      <div style={styles.container}>
        <div style={styles.sectionHeader}>
          <h2 style={styles.sectionTitle}>🔥 Upcoming Events</h2>
          <p style={styles.sectionSubtitle}>Filter by category or date to find your perfect event</p>
        </div>
        <EventFilter
          category={category}
          date={date}
          onCategoryChange={setCategory}
          onDateChange={setDate}
          onClear={handleClear}
        />
        <EventList events={events} loading={loading} error={error} />
      </div>
    </main>
  );
};

const styles: Record<string, React.CSSProperties> = {
  main: { minHeight: '100vh', background: '#f0f4ff' },
  hero: {
    background: 'linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 70%, #533483 100%)',
    padding: '80px 40px',
    position: 'relative' as const,
    overflow: 'hidden',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
  },
  heroContent: { textAlign: 'center' as const, position: 'relative' as const, zIndex: 2 },
  heroBadge: { display: 'inline-block', background: 'rgba(255,255,255,0.15)', color: '#a5b4fc', padding: '8px 20px', borderRadius: '20px', fontSize: '14px', fontWeight: 600, marginBottom: '20px', backdropFilter: 'blur(10px)', border: '1px solid rgba(255,255,255,0.1)' },
  heroTitle: { fontSize: '56px', fontWeight: 900, color: '#fff', marginBottom: '16px', lineHeight: 1.1, letterSpacing: '-2px' },
  heroHighlight: { background: 'linear-gradient(135deg, #f093fb, #f5576c, #fda085)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' },
  heroSubtitle: { fontSize: '18px', color: '#a5b4fc', marginBottom: '36px', maxWidth: '500px', margin: '0 auto 36px' },
  heroStats: { display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '24px', background: 'rgba(255,255,255,0.1)', backdropFilter: 'blur(10px)', padding: '16px 32px', borderRadius: '16px', border: '1px solid rgba(255,255,255,0.1)', display: 'inline-flex' },
  stat: { display: 'flex', flexDirection: 'column' as const, alignItems: 'center' },
  statNum: { fontSize: '28px', fontWeight: 800, color: '#fff' },
  statLabel: { fontSize: '12px', color: '#a5b4fc', fontWeight: 500 },
  statDivider: { width: '1px', height: '40px', background: 'rgba(255,255,255,0.2)' },
  heroEmojis: { position: 'absolute' as const, inset: 0, pointerEvents: 'none' as const },
  floatingEmoji1: { position: 'absolute' as const, top: '15%', left: '8%', fontSize: '48px', opacity: 0.3 },
  floatingEmoji2: { position: 'absolute' as const, top: '20%', right: '10%', fontSize: '40px', opacity: 0.3 },
  floatingEmoji3: { position: 'absolute' as const, bottom: '20%', left: '12%', fontSize: '36px', opacity: 0.3 },
  floatingEmoji4: { position: 'absolute' as const, bottom: '15%', right: '8%', fontSize: '44px', opacity: 0.3 },
  container: { maxWidth: '1200px', margin: '0 auto', padding: '40px 20px' },
  sectionHeader: { marginBottom: '24px' },
  sectionTitle: { fontSize: '28px', fontWeight: 800, color: '#1a1a2e', marginBottom: '6px' },
  sectionSubtitle: { color: '#6b7280', fontSize: '15px' },
};

export default HomePage;
