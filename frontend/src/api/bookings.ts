import apiClient from './client';
import { Booking, BookingRequest } from '../types';

export const createBooking = async (request: BookingRequest): Promise<Booking> => {
  const response = await apiClient.post<Booking>('/bookings', request);
  return response.data;
};

export const getBookings = async (): Promise<Booking[]> => {
  const response = await apiClient.get<Booking[]>('/bookings');
  return response.data;
};

export const getBookingById = async (id: string): Promise<Booking> => {
  const response = await apiClient.get<Booking>(`/bookings/${id}`);
  return response.data;
};
