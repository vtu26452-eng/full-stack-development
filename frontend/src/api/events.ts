import apiClient from './client';
import { Event } from '../types';

interface EventFilters {
  category?: string;
  date?: string;
}

export const getEvents = async (filters?: EventFilters): Promise<Event[]> => {
  const params = new URLSearchParams();
  if (filters?.category && filters.category !== 'ALL') {
    params.append('category', filters.category);
  }
  if (filters?.date) {
    params.append('date', filters.date);
  }
  const response = await apiClient.get<Event[]>(`/events${params.toString() ? '?' + params.toString() : ''}`);
  return response.data;
};

export const getEventById = async (id: string): Promise<Event> => {
  const response = await apiClient.get<Event>(`/events/${id}`);
  return response.data;
};
