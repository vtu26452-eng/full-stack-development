import React from 'react';
import { Link } from 'react-router-dom';

const Home = () => {
    return (
        <div style={{ textAlign: 'center', marginTop: '50px' }}>
            <h1>Welcome to the Technical Fest Ticket Booking System</h1>
            <p>Join the biggest tech events of the year!</p>
            <div style={{ marginTop: '30px' }}>
                <Link to="/events" className="btn" style={{ marginRight: '15px' }}>View Events</Link>
                <Link to="/register" className="btn" style={{ background: '#2ecc71' }}>Register Now</Link>
            </div>
        </div>
    );
};

export default Home;
