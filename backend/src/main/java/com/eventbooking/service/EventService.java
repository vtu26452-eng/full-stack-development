package com.eventbooking.service;

import com.eventbooking.exception.ResourceNotFoundException;
import com.eventbooking.model.Event;
import com.eventbooking.repository.EventRepository;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.stereotype.Service;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.List;

@Slf4j
@Service
@RequiredArgsConstructor
public class EventService {

    private final EventRepository eventRepository;

    public List<Event> getAllEvents() {
        return eventRepository.findAll();
    }

    public Event getEventById(String id) {
        return eventRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Event not found with id: " + id));
    }

    public List<Event> getEventsByCategory(String category) {
        return eventRepository.findByCategory(category.toUpperCase());
    }

    public List<Event> getEventsByDate(LocalDate date) {
        LocalDateTime start = date.atStartOfDay();
        LocalDateTime end = date.atTime(23, 59, 59);
        return eventRepository.findByDateTimeBetween(start, end);
    }

    public List<Event> getFilteredEvents(String category, LocalDate date) {
        if (category != null && date != null) {
            LocalDateTime start = date.atStartOfDay();
            LocalDateTime end = date.atTime(23, 59, 59);
            return eventRepository.findByCategoryAndDateTimeBetween(category.toUpperCase(), start, end);
        } else if (category != null) {
            return getEventsByCategory(category);
        } else if (date != null) {
            return getEventsByDate(date);
        }
        return getAllEvents();
    }
}
