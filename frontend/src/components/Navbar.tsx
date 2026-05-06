import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const Navbar: React.FC = () => {
  const { user, isAuthenticated, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  return (
    <nav style={styles.nav} role="navigation" aria-label="Main navigation">
      <div style={styles.container}>
        <Link to="/" style={styles.brand}>
          🎟️ <span style={styles.brandText}>EventBook</span>
        </Link>
        <div style={styles.links}>
          <Link to="/" style={styles.link}>🏠 Events</Link>
          {isAuthenticated && (
            <Link to="/bookings" style={styles.link}>📋 My Bookings</Link>
          )}
          {isAuthenticated ? (
            <div style={styles.userSection}>
              <div style={styles.avatar}>{user?.name?.charAt(0).toUpperCase()}</div>
              <span style={styles.userName}>{user?.name}</span>
              <button onClick={handleLogout} style={styles.logoutBtn}>
                Logout
              </button>
            </div>
          ) : (
            <div style={styles.authLinks}>
              <Link to="/login" style={styles.signInBtn}>Sign In</Link>
              <Link to="/register" style={styles.registerBtn}>Register Free</Link>
            </div>
          )}
        </div>
      </div>
    </nav>
  );
};

const styles: Record<string, React.CSSProperties> = {
  nav: {
    background: 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)',
    padding: '0 24px',
    position: 'sticky' as const,
    top: 0,
    zIndex: 100,
    boxShadow: '0 4px 20px rgba(0,0,0,0.3)',
    borderBottom: '1px solid rgba(255,255,255,0.05)',
  },
  container: { maxWidth: '1200px', margin: '0 auto', display: 'flex', alignItems: 'center', justifyContent: 'space-between', height: '68px' },
  brand: { display: 'flex', alignItems: 'center', gap: '8px', textDecoration: 'none', fontSize: '22px' },
  brandText: { color: '#fff', fontWeight: 800, letterSpacing: '-0.5px' },
  links: { display: 'flex', alignItems: 'center', gap: '8px' },
  link: { color: '#a5b4fc', textDecoration: 'none', fontSize: '14px', fontWeight: 500, padding: '8px 12px', borderRadius: '8px', transition: 'background 0.2s' },
  userSection: { display: 'flex', alignItems: 'center', gap: '10px' },
  avatar: { width: '34px', height: '34px', borderRadius: '50%', background: 'linear-gradient(135deg, #f093fb, #f5576c)', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 800, fontSize: '14px' },
  userName: { color: '#e0e7ff', fontSize: '14px', fontWeight: 500 },
  logoutBtn: { background: 'rgba(255,255,255,0.1)', border: '1px solid rgba(255,255,255,0.2)', color: '#e0e7ff', padding: '7px 14px', borderRadius: '8px', cursor: 'pointer', fontSize: '13px', fontWeight: 500 },
  authLinks: { display: 'flex', alignItems: 'center', gap: '10px' },
  signInBtn: { color: '#a5b4fc', textDecoration: 'none', fontSize: '14px', fontWeight: 600, padding: '8px 16px', borderRadius: '8px', border: '1px solid rgba(165,180,252,0.3)' },
  registerBtn: { background: 'linear-gradient(135deg, #667eea, #764ba2)', color: '#fff', padding: '8px 18px', borderRadius: '8px', textDecoration: 'none', fontSize: '14px', fontWeight: 700 },
};

export default Navbar;
