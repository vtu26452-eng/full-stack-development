import React from 'react';

interface EventFilterProps {
  category: string;
  date: string;
  onCategoryChange: (category: string) => void;
  onDateChange: (date: string) => void;
  onClear: () => void;
}

const CATEGORIES = ['ALL', 'TECHNICAL', 'CULTURAL', 'SPORTS', 'SEMINAR', 'WORKSHOP'];

const EventFilter: React.FC<EventFilterProps> = ({
  category, date, onCategoryChange, onDateChange, onClear,
}) => {
  return (
    <div style={styles.container} role="search" aria-label="Filter events">
      <div style={styles.filterGroup}>
        <label htmlFor="category-filter" style={styles.label}>Category</label>
        <select
          id="category-filter"
          value={category}
          onChange={(e) => onCategoryChange(e.target.value)}
          style={styles.select}
          aria-label="Filter by category"
        >
          {CATEGORIES.map((cat) => (
            <option key={cat} value={cat}>{cat === 'ALL' ? 'All Categories' : cat}</option>
          ))}
        </select>
      </div>
      <div style={styles.filterGroup}>
        <label htmlFor="date-filter" style={styles.label}>Date</label>
        <input
          id="date-filter"
          type="date"
          value={date}
          onChange={(e) => onDateChange(e.target.value)}
          style={styles.input}
          aria-label="Filter by date"
        />
      </div>
      {(category !== 'ALL' || date) && (
        <button onClick={onClear} style={styles.clearBtn} aria-label="Clear filters">
          Clear Filters
        </button>
      )}
    </div>
  );
};

const styles: Record<string, React.CSSProperties> = {
  container: { display: 'flex', gap: '16px', alignItems: 'flex-end', flexWrap: 'wrap' as const, background: '#fff', padding: '16px 20px', borderRadius: '12px', boxShadow: '0 1px 4px rgba(0,0,0,0.06)', marginBottom: '24px' },
  filterGroup: { display: 'flex', flexDirection: 'column' as const, gap: '4px' },
  label: { fontSize: '12px', fontWeight: 600, color: '#6b7280', textTransform: 'uppercase' as const },
  select: { padding: '8px 12px', border: '1px solid #d1d5db', borderRadius: '8px', fontSize: '14px', minWidth: '160px' },
  input: { padding: '8px 12px', border: '1px solid #d1d5db', borderRadius: '8px', fontSize: '14px' },
  clearBtn: { background: '#f3f4f6', border: 'none', padding: '8px 16px', borderRadius: '8px', cursor: 'pointer', fontSize: '14px', color: '#374151', alignSelf: 'flex-end' as const },
};

export default EventFilter;
