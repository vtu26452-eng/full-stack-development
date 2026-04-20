import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import apiClient from '../services/api';

const Dashboard = () => {
    const navigate = useNavigate();
    const userStr = localStorage.getItem('user');
    const user = userStr ? JSON.parse(userStr) : null;

    const [bookings, setBookings] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!user) {
            navigate('/login');
            return;
        }

        apiClient.get(`/bookings/user/${user.id}`)
            .then(res => {
                setBookings(res.data);
                setLoading(false);
            })
            .catch(err => {
                console.error("Could not fetch bookings", err);
                setLoading(false);
            });
    }, [user, navigate]);

    if (!user) return null;

    return (
        <div>
            <h2>Dashboard</h2>
            <p>Welcome back, <strong>{user.name}</strong>!</p>

            <h3>Your Bookings</h3>
            {loading ? <p>Loading your bookings...</p> : (
                bookings.length === 0 ? (
                    <p>You haven't booked any tickets yet.</p>
                ) : (
                    <div className="card-grid">
                        {bookings.map(b => (
                            <div key={b.id} className="card">
                                <h3>Booking #{b.id}</h3>
                                <p><strong>Event ID:</strong> {b.eventId}</p>
                                <p><strong>Tickets:</strong> {b.numberOfTickets}</p>
                                <p><strong>Status:</strong> {b.status}</p>
                                <p><strong>Time:</strong> {new Date(b.bookingTime).toLocaleString()}</p>
                            </div>
                        ))}
                    </div>
                )
            )}
        </div>
    );
};

export default Dashboard;
