import axios from 'axios';

// Base API gateway URL
const API_URL = 'http://localhost:8080';

// Axios instance to centralize requests
const apiClient = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json'
    }
});

// Implement Dummy Mode Fallback using Axios Interceptors
apiClient.interceptors.response.use(
    response => response,
    error => {
        console.error("API Call Failed. Entering Dummy Mode...", error);

        const path = error.config.url;

        // Return dummy data if backend is offline
        if (path.includes('/events')) {
            return Promise.resolve({
                data: [
                    { id: 101, name: "AI Summit 2026", location: "Main Hall", date: "2026-05-10", availableTickets: 50 },
                    { id: 102, name: "Web Dev Bootcamp", location: "Lab 4", date: "2026-05-12", availableTickets: 120 }
                ]
            });
        }

        if (path.includes('/bookings')) {
            return Promise.resolve({
                data: { id: 999, status: "DUMMY_CONFIRMED" }
            });
        }

        if (path.includes('/users/login')) {
            return Promise.resolve({
                data: { id: 1, name: "Test User", email: "test@example.com" }
            });
        }

        return Promise.reject(error);
    }
);

export default apiClient;
