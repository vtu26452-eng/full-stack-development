import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const RegisterPage: React.FC = () => {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { register } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    if (password.length < 8) {
      setError('Password must be at least 8 characters.');
      return;
    }
    setLoading(true);
    try {
      await register(name, email, password);
      navigate('/');
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } };
      setError(axiosError.response?.data?.message || 'Registration failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={styles.page}>
      {/* Left panel */}
      <div style={styles.leftPanel}>
        <div style={styles.leftContent}>
          <div style={styles.logo}>🎉</div>
          <h1 style={styles.brandName}>Join EventBook</h1>
          <p style={styles.brandTagline}>Create your account and start booking amazing college events today!</p>
          <div style={styles.steps}>
            <div style={styles.step}><span style={styles.stepNum}>1</span> Create your account</div>
            <div style={styles.step}><span style={styles.stepNum}>2</span> Browse 10+ college events</div>
            <div style={styles.step}><span style={styles.stepNum}>3</span> Book & get email confirmation</div>
          </div>
        </div>
      </div>

      {/* Right form panel */}
      <div style={styles.rightPanel}>
        <div style={styles.card}>
          <div style={styles.cardHeader}>
            <h2 style={styles.title}>Create Account 🚀</h2>
            <p style={styles.subtitle}>Fill in your details to get started</p>
          </div>

          {error && (
            <div style={styles.errorBox}>
              <span>⚠️</span>
              <span>{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit}>
            <div style={styles.field}>
              <label style={styles.label}>👤 Full Name</label>
              <input
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                style={styles.input}
                placeholder="John Doe"
                required
              />
            </div>
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
                placeholder="Min. 8 characters"
                required
                minLength={8}
              />
              <p style={styles.hint}>Must be at least 8 characters</p>
            </div>
            <button type="submit" style={styles.submitBtn} disabled={loading}>
              {loading ? '⏳ Creating account...' : '✨ Create Account'}
            </button>
          </form>

          <div style={styles.divider}><span style={styles.dividerText}>Already have an account?</span></div>

          <Link to="/login" style={styles.loginBtn}>
            🔑 Sign In Instead
          </Link>
        </div>
      </div>
    </div>
  );
};

const styles: Record<string, React.CSSProperties> = {
  page: { display: 'flex', minHeight: '100vh' },
  leftPanel: {
    flex: 1, background: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
    display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '40px',
  },
  leftContent: { color: '#fff', maxWidth: '400px' },
  logo: { fontSize: '64px', marginBottom: '16px' },
  brandName: { fontSize: '42px', fontWeight: 800, marginBottom: '12px', letterSpacing: '-1px' },
  brandTagline: { fontSize: '18px', opacity: 0.9, marginBottom: '32px', lineHeight: 1.6 },
  steps: { display: 'flex', flexDirection: 'column' as const, gap: '12px' },
  step: { display: 'flex', alignItems: 'center', gap: '12px', fontSize: '16px', background: 'rgba(255,255,255,0.2)', padding: '12px 16px', borderRadius: '10px' },
  stepNum: { background: '#fff', color: '#11998e', width: '28px', height: '28px', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 800, fontSize: '14px', flexShrink: 0 },
  rightPanel: { flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '40px', background: '#f0f4ff' },
  card: { background: '#fff', borderRadius: '20px', padding: '40px', width: '100%', maxWidth: '440px', boxShadow: '0 20px 60px rgba(17,153,142,0.15)' },
  cardHeader: { marginBottom: '28px' },
  title: { fontSize: '30px', fontWeight: 800, color: '#1a1a2e', marginBottom: '6px' },
  subtitle: { color: '#6b7280', fontSize: '15px' },
  errorBox: { display: 'flex', gap: '10px', background: '#fff5f5', border: '1px solid #fed7d7', color: '#c53030', padding: '12px 16px', borderRadius: '10px', marginBottom: '20px', fontSize: '14px' },
  field: { marginBottom: '18px' },
  label: { display: 'block', marginBottom: '8px', fontWeight: 600, color: '#374151', fontSize: '14px' },
  input: { width: '100%', padding: '12px 16px', border: '2px solid #e5e7eb', borderRadius: '10px', fontSize: '15px', boxSizing: 'border-box' as const },
  hint: { fontSize: '12px', color: '#9ca3af', marginTop: '4px' },
  submitBtn: { width: '100%', padding: '14px', background: 'linear-gradient(135deg, #11998e, #38ef7d)', color: '#fff', border: 'none', borderRadius: '10px', fontSize: '16px', fontWeight: 700, cursor: 'pointer', marginTop: '8px' },
  divider: { textAlign: 'center' as const, margin: '24px 0 16px', borderTop: '1px solid #e5e7eb', paddingTop: '20px' },
  dividerText: { color: '#9ca3af', fontSize: '13px' },
  loginBtn: { display: 'block', textAlign: 'center' as const, background: 'linear-gradient(135deg, #667eea, #764ba2)', color: '#fff', padding: '13px', borderRadius: '10px', fontWeight: 700, fontSize: '15px' },
};

export default RegisterPage;
