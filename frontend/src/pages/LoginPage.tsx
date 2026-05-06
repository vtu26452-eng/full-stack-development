import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const LoginPage: React.FC = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [notRegistered, setNotRegistered] = useState(false);
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const from = (location.state as { from?: { pathname: string } })?.from?.pathname || '/';

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setNotRegistered(false);
    setLoading(true);
    try {
      await login(email, password);
      navigate(from, { replace: true });
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string; error?: string } } };
      const errorCode = axiosError.response?.data?.error;
      if (errorCode === 'INVALID_CREDENTIALS') {
        setNotRegistered(true);
        setError('No account found with this email. Please sign up first.');
      } else {
        setError(axiosError.response?.data?.message || 'Login failed. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={styles.page}>
      {/* Left decorative panel */}
      <div style={styles.leftPanel}>
        <div style={styles.leftContent}>
          <div style={styles.logo}>🎟️</div>
          <h1 style={styles.brandName}>EventBook</h1>
          <p style={styles.brandTagline}>Discover & book the best college events across Tamil Nadu</p>
          <div style={styles.features}>
            <div style={styles.feature}>✅ 10+ College Events</div>
            <div style={styles.feature}>🎫 Instant Booking</div>
            <div style={styles.feature}>📧 Email Confirmation</div>
            <div style={styles.feature}>🔒 Secure Payments</div>
          </div>
        </div>
      </div>

      {/* Right form panel */}
      <div style={styles.rightPanel}>
        <div style={styles.card}>
          <div style={styles.cardHeader}>
            <h2 style={styles.title}>Welcome Back! 👋</h2>
            <p style={styles.subtitle}>Sign in to continue booking events</p>
          </div>

          {error && (
            <div style={styles.errorBox}>
              <span style={styles.errorIcon}>⚠️</span>
              <div>
                <p style={{ margin: 0, fontWeight: 600 }}>{error}</p>
                {notRegistered && (
                  <p style={{ margin: '6px 0 0', fontSize: '13px' }}>
                    Don't have an account?{' '}
                    <Link to="/register" style={styles.errorLink}>Sign up here →</Link>
                  </p>
                )}
              </div>
            </div>
          )}

          <form onSubmit={handleSubmit}>
            <div style={styles.field}>
              <label style={styles.label}>📧 Email Address</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                style={styles.input}
                placeholder="you@example.com"
                required
              />
            </div>
            <div style={styles.field}>
              <label style={styles.label}>🔑 Password</label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                style={styles.input}
                placeholder="Enter your password"
                required
              />
            </div>
            <button type="submit" style={styles.submitBtn} disabled={loading}>
              {loading ? '⏳ Signing in...' : '🚀 Sign In'}
            </button>
          </form>

          <div style={styles.divider}><span style={styles.dividerText}>New to EventBook?</span></div>

          <Link to="/register" style={styles.registerBtn}>
            ✨ Create a Free Account
          </Link>
        </div>
      </div>
    </div>
  );
};

const styles: Record<string, React.CSSProperties> = {
  page: { display: 'flex', minHeight: '100vh' },
  leftPanel: {
    flex: 1, background: 'linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%)',
    display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '40px',
    '@media (max-width: 768px)': { display: 'none' }
  },
  leftContent: { color: '#fff', maxWidth: '400px' },
  logo: { fontSize: '64px', marginBottom: '16px' },
  brandName: { fontSize: '42px', fontWeight: 800, marginBottom: '12px', letterSpacing: '-1px' },
  brandTagline: { fontSize: '18px', opacity: 0.9, marginBottom: '32px', lineHeight: 1.6 },
  features: { display: 'flex', flexDirection: 'column' as const, gap: '12px' },
  feature: { fontSize: '16px', background: 'rgba(255,255,255,0.2)', padding: '10px 16px', borderRadius: '8px', backdropFilter: 'blur(10px)' },
  rightPanel: { flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '40px', background: '#f0f4ff' },
  card: { background: '#fff', borderRadius: '20px', padding: '40px', width: '100%', maxWidth: '440px', boxShadow: '0 20px 60px rgba(102,126,234,0.2)' },
  cardHeader: { marginBottom: '28px' },
  title: { fontSize: '30px', fontWeight: 800, color: '#1a1a2e', marginBottom: '6px' },
  subtitle: { color: '#6b7280', fontSize: '15px' },
  errorBox: { display: 'flex', gap: '12px', background: '#fff5f5', border: '1px solid #fed7d7', color: '#c53030', padding: '14px', borderRadius: '10px', marginBottom: '20px', fontSize: '14px' },
  errorIcon: { fontSize: '18px', flexShrink: 0 },
  errorLink: { color: '#c53030', fontWeight: 700, textDecoration: 'underline' },
  field: { marginBottom: '18px' },
  label: { display: 'block', marginBottom: '8px', fontWeight: 600, color: '#374151', fontSize: '14px' },
  input: { width: '100%', padding: '12px 16px', border: '2px solid #e5e7eb', borderRadius: '10px', fontSize: '15px', boxSizing: 'border-box' as const, transition: 'border-color 0.2s', outline: 'none' },
  submitBtn: { width: '100%', padding: '14px', background: 'linear-gradient(135deg, #667eea, #764ba2)', color: '#fff', border: 'none', borderRadius: '10px', fontSize: '16px', fontWeight: 700, cursor: 'pointer', marginTop: '8px', letterSpacing: '0.5px' },
  divider: { textAlign: 'center' as const, margin: '24px 0 16px', position: 'relative' as const, borderTop: '1px solid #e5e7eb', paddingTop: '20px' },
  dividerText: { color: '#9ca3af', fontSize: '13px', background: '#fff', padding: '0 12px' },
  registerBtn: { display: 'block', textAlign: 'center' as const, background: 'linear-gradient(135deg, #f093fb, #f5576c)', color: '#fff', padding: '13px', borderRadius: '10px', fontWeight: 700, fontSize: '15px' },
};

export default LoginPage;
