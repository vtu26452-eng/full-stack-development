import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import apiClient from '../services/api';

const Events = () => {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        apiClient.get('/events')
            .then(res => {
                setEvents(res.data);
                setLoading(false);
            })
            .catch(err => {
                setError('Failed to fetch events from API. Ensure backend is running.');
                setLoading(false);
            });
    }, []);

    if (loading) return <h3>Loading events...</h3>;

    return (
        <div>
            <h2>Upcoming Events</h2>
            {error && <div className="error-msg">{error}</div>}

            <div className="card-grid">
                {events.length === 0 && !error ? <p>No events available.</p> : null}
                {events.map(event => (
                    <div key={event.id} className="card">
                        <h3>{event.name}</h3>
                        <p><strong>Location:</strong> {event.location}</p>
                        <p><strong>Date:</strong> {event.date}</p>
                        <p><strong>Available Tickets:</strong> {event.availableTickets}</p>

                        {event.availableTickets > 0 ? (
                            <Link to={`/book/${event.id}`} className="btn">Book Now</Link>
                        ) : (
                            <button className="btn" style={{ background: '#95a5a6' }} disabled>Sold Out</button>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
};

export default Events;
