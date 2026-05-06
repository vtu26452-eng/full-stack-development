package com.eventbooking.seed;

import com.eventbooking.model.Event;
import com.eventbooking.repository.EventRepository;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.boot.ApplicationArguments;
import org.springframework.boot.ApplicationRunner;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.time.LocalDateTime;
import java.util.List;

@Slf4j
@Component
@RequiredArgsConstructor
public class SeedDataRunner implements ApplicationRunner {

    private final EventRepository eventRepository;

    @Override
    public void run(ApplicationArguments args) {
        if (eventRepository.count() == 0) {
            eventRepository.saveAll(buildSeedEvents());
            log.info("Seed data inserted: 10 college events");
        } else {
            log.info("Events collection not empty — skipping seed data");
        }
    }

    private List<Event> buildSeedEvents() {
        LocalDateTime now = LocalDateTime.now();
        return List.of(
            // VelTech University Event 1
            Event.builder()
                .name("VelTech TechFest 2025")
                .description("Annual technical festival featuring hackathons, coding contests, robotics competitions, and tech talks by industry experts at VelTech University.")
                .category("TECHNICAL")
                .organizer("VelTech University")
                .venue("Main Auditorium, VelTech University, Avadi, Chennai")
                .dateTime(LocalDateTime.of(2025, 9, 15, 10, 0))
                .ticketPrice(new BigDecimal("150.00"))
                .totalTickets(500)
                .remainingTickets(500)
                .imageUrl("https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800")
                .createdAt(now)
                .build(),

            // VelTech University Event 2
            Event.builder()
                .name("VelTech Cultural Nite 2025")
                .description("A spectacular evening of music, dance, drama, and cultural performances by students of VelTech University. Featuring celebrity performances and inter-college competitions.")
                .category("CULTURAL")
                .organizer("VelTech University")
                .venue("Open Air Theatre, VelTech University, Avadi, Chennai")
                .dateTime(LocalDateTime.of(2025, 10, 5, 18, 0))
                .ticketPrice(new BigDecimal("100.00"))
                .totalTickets(800)
                .remainingTickets(800)
                .imageUrl("https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800")
                .createdAt(now)
                .build(),

            // SRM Institute
            Event.builder()
                .name("CodeSprint Hackathon 2025")
                .description("24-hour hackathon challenging students to build innovative solutions for real-world problems. Prizes worth ₹1,00,000 for top teams. Open to all engineering students.")
                .category("TECHNICAL")
                .organizer("SRM Institute of Science and Technology")
                .venue("SRM Tech Park, Kattankulathur, Chennai")
                .dateTime(LocalDateTime.of(2025, 8, 20, 9, 0))
                .ticketPrice(new BigDecimal("200.00"))
                .totalTickets(300)
                .remainingTickets(300)
                .imageUrl("https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=800")
                .createdAt(now)
                .build(),

            // Anna University
            Event.builder()
                .name("Rhythms Cultural Fest 2025")
                .description("Anna University's flagship cultural festival with classical dance, folk music, street plays, and art exhibitions. One of the largest college cultural events in Tamil Nadu.")
                .category("CULTURAL")
                .organizer("Anna University")
                .venue("Anna University Grounds, Guindy, Chennai")
                .dateTime(LocalDateTime.of(2025, 11, 1, 10, 0))
                .ticketPrice(new BigDecimal("120.00"))
                .totalTickets(600)
                .remainingTickets(600)
                .imageUrl("https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800")
                .createdAt(now)
                .build(),

            // Loyola College
            Event.builder()
                .name("Inter-College Cricket Cup 2025")
                .description("Annual inter-college cricket tournament featuring 16 teams from across Tamil Nadu. T20 format with knockout rounds. Exciting matches and prizes for winners.")
                .category("SPORTS")
                .organizer("Loyola College")
                .venue("Loyola Sports Ground, Nungambakkam, Chennai")
                .dateTime(LocalDateTime.of(2025, 9, 28, 8, 0))
                .ticketPrice(new BigDecimal("80.00"))
                .totalTickets(1000)
                .remainingTickets(1000)
                .imageUrl("https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=800")
                .createdAt(now)
                .build(),

            // IIT Madras
            Event.builder()
                .name("AI & ML Seminar 2025")
                .description("A one-day seminar on Artificial Intelligence and Machine Learning featuring keynote speakers from Google, Microsoft, and leading research institutions. Includes hands-on workshops.")
                .category("SEMINAR")
                .organizer("IIT Madras")
                .venue("Seminar Hall, IIT Madras, Adyar, Chennai")
                .dateTime(LocalDateTime.of(2025, 8, 10, 9, 30))
                .ticketPrice(new BigDecimal("250.00"))
                .totalTickets(200)
                .remainingTickets(200)
                .imageUrl("https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800")
                .createdAt(now)
                .build(),

            // PSG College of Technology
            Event.builder()
                .name("Robotics Workshop 2025")
                .description("Hands-on robotics workshop covering Arduino programming, sensor integration, and autonomous robot building. Participants will build and program their own robots.")
                .category("WORKSHOP")
                .organizer("PSG College of Technology")
                .venue("PSG Innovation Lab, Peelamedu, Coimbatore")
                .dateTime(LocalDateTime.of(2025, 10, 18, 9, 0))
                .ticketPrice(new BigDecimal("300.00"))
                .totalTickets(150)
                .remainingTickets(150)
                .imageUrl("https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800")
                .createdAt(now)
                .build(),

            // Amrita University
            Event.builder()
                .name("Entrepreneurship Summit 2025")
                .description("Annual entrepreneurship summit featuring startup pitches, investor panels, and workshops on building successful businesses. Network with 50+ investors and 100+ startups.")
                .category("SEMINAR")
                .organizer("Amrita University")
                .venue("Amrita Convention Centre, Ettimadai, Coimbatore")
                .dateTime(LocalDateTime.of(2025, 9, 5, 10, 0))
                .ticketPrice(new BigDecimal("180.00"))
                .totalTickets(400)
                .remainingTickets(400)
                .imageUrl("https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=800")
                .createdAt(now)
                .build(),

            // Madras Christian College
            Event.builder()
                .name("Inter-College Basketball Championship 2025")
                .description("State-level inter-college basketball championship with men's and women's categories. 20 teams competing over 3 days. Live commentary and refreshments available.")
                .category("SPORTS")
                .organizer("Madras Christian College")
                .venue("MCC Sports Complex, Tambaram, Chennai")
                .dateTime(LocalDateTime.of(2025, 11, 15, 8, 0))
                .ticketPrice(new BigDecimal("60.00"))
                .totalTickets(700)
                .remainingTickets(700)
                .imageUrl("https://images.unsplash.com/photo-1546519638405-a9f9e8d3e8e8?w=800")
                .createdAt(now)
                .build(),

            // Stella Maris College
            Event.builder()
                .name("Photography & Arts Expo 2025")
                .description("Annual photography and fine arts exhibition showcasing student works across painting, sculpture, digital art, and photography. Open to all college students across Tamil Nadu.")
                .category("CULTURAL")
                .organizer("Stella Maris College")
                .venue("Stella Maris Gallery, Cathedral Road, Chennai")
                .dateTime(LocalDateTime.of(2025, 10, 25, 10, 0))
                .ticketPrice(new BigDecimal("90.00"))
                .totalTickets(350)
                .remainingTickets(350)
                .imageUrl("https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=800")
                .createdAt(now)
                .build()
        );
    }
}
