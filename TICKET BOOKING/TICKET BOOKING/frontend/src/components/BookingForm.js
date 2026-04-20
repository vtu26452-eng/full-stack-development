import React, { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import apiClient from '../services/api';

const BookingForm = () => {
    const { eventId } = useParams();
    const navigate = useNavigate();

    const [numberOfTickets, setNumberOfTickets] = useState(1);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const userStr = localStorage.getItem('user');
    const user = userStr ? JSON.parse(userStr) : null;

    const handleBook = async (e) => {
        e.preventDefault();
        setError('');

        if (!user) {
            setError('You must be logged in to book a ticket');
            setTimeout(() => navigate('/login'), 2000);
            return;
        }

        if (numberOfTickets < 1) {
            setError('Please select at least 1 ticket.');
            return;
        }

        try {
            await apiClient.post('/bookings', {
                userId: user.id,
                eventId: eventId,
                numberOfTickets: numberOfTickets
            });
            setSuccess('Booking Successful! Redirecting to Dashboard...');
            setTimeout(() => navigate('/dashboard'), 2000);
        } catch (err) {
            setError(err.response?.data?.error || 'Booking Failed. Not enough tickets?');
        }
    };

    return (
        <div className="form-container">
            <h2>Book Tickets (Event #{eventId})</h2>
            {error && <div className="error-msg">{error}</div>}
            {success && <div className="success-msg">{success}</div>}
            <form onSubmit={handleBook}>
                <div className="form-group">
                    <label>Number of Tickets</label>
                    <input
                        type="number"
                        min="1"
                        value={numberOfTickets}
                        onChange={e => setNumberOfTickets(e.target.value)}
                    />
                </div>
                <button type="submit" className="btn" style={{ width: '100%' }}>Confirm Booking</button>
            </form>
        </div>
    );
};

export default BookingForm;
