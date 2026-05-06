export interface User {
  id: string;
  name: string;
  email: string;
}

export interface AuthContextValue {
  user: User | null;
  token: string | null;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  register: (name: string, email: string, password: string) => Promise<void>;
  isAuthenticated: boolean;
}

export interface Event {
  id: string;
  name: string;
  description: string;
  category: string;
  organizer: string;
  venue: string;
  dateTime: string; // ISO-8601
  ticketPrice: number;
  totalTickets: number;
  remainingTickets: number;
  imageUrl?: string;
}

export interface Booking {
  id: string;
  referenceNumber: string;
  eventId: string;
  eventName: string;
  eventDateTime: string;
  venue: string;
  quantity: number;
  totalAmount: number;
  status: 'CONFIRMED' | 'FAILED' | 'PENDING';
  createdAt: string;
}

export interface PaymentFormData {
  cardholderName: string;
  cardNumber: string;
  expiryMonth: string;
  expiryYear: string;
  cvv: string;
  simulateFailure: boolean;
}

export interface BookingRequest {
  eventId: string;
  quantity: number;
  payment: PaymentFormData;
}

export interface ApiError {
  error: string;
  message: string;
  timestamp: string;
}
