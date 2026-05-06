package com.eventbooking.service;

import com.eventbooking.dto.BookingRequest;
import com.eventbooking.dto.BookingResponse;
import com.eventbooking.dto.PaymentResult;
import com.eventbooking.exception.InsufficientTicketsException;
import com.eventbooking.exception.ResourceNotFoundException;
import com.eventbooking.model.*;
import com.eventbooking.repository.BookingRepository;
import com.eventbooking.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.data.mongodb.core.FindAndModifyOptions;
import org.springframework.data.mongodb.core.MongoTemplate;
import org.springframework.data.mongodb.core.query.Criteria;
import org.springframework.data.mongodb.core.query.Query;
import org.springframework.data.mongodb.core.query.Update;
import org.springframework.stereotype.Service;

import java.math.BigDecimal;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Random;
import java.util.stream.Collectors;

@Slf4j
@Service
@RequiredArgsConstructor
public class BookingService {

    private final BookingRepository bookingRepository;
    private final UserRepository userRepository;
    private final MongoTemplate mongoTemplate;
    private final PaymentService paymentService;
    private final NotificationService notificationService;

    private static final String ALPHANUMERIC = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    private static final Random RANDOM = new Random();

    public BookingResponse createBooking(String userId, BookingRequest request) {
        // Fetch user
        User user = userRepository.findById(userId)
                .orElseThrow(() -> new ResourceNotFoundException("User not found with id: " + userId));

        // Atomically decrement remainingTickets — prevents overselling
        Query query = Query.query(
                Criteria.where("_id").is(request.getEventId())
                        .and("remainingTickets").gte(request.getQuantity())
        );
        Update update = new Update().inc("remainingTickets", -request.getQuantity());
        Event event = mongoTemplate.findAndModify(
                query, update,
                FindAndModifyOptions.options().returnNew(false),
                Event.class
        );

        if (event == null) {
            // Either event not found or insufficient tickets
            boolean eventExists = mongoTemplate.exists(
                    Query.query(Criteria.where("_id").is(request.getEventId())), Event.class);
            if (!eventExists) {
                throw new ResourceNotFoundException("Event not found with id: " + request.getEventId());
            }
            throw new InsufficientTicketsException(
                    "Insufficient tickets available for this event. Please try a smaller quantity.");
        }

        // Process simulated payment
        PaymentResult paymentResult = paymentService.processPayment(request.getPayment());

        // Generate unique reference number
        String referenceNumber = generateReferenceNumber();

        // Calculate total amount
        BigDecimal totalAmount = event.getTicketPrice().multiply(BigDecimal.valueOf(request.getQuantity()));

        // Build and save booking
        Booking booking = Booking.builder()
                .referenceNumber(referenceNumber)
                .userId(userId)
                .eventId(event.getId())
                .eventSnapshot(EventSnapshot.builder()
                        .name(event.getName())
                        .venue(event.getVenue())
                        .dateTime(event.getDateTime())
                        .ticketPrice(event.getTicketPrice())
                        .build())
                .quantity(request.getQuantity())
                .totalAmount(totalAmount)
                .status(BookingStatus.CONFIRMED)
                .paymentDetails(PaymentDetails.builder()
                        .cardholderName(request.getPayment().getCardholderName())
                        .maskedCardNumber(paymentResult.getMaskedCardNumber())
                        .simulatedSuccess(true)
                        .build())
                .createdAt(LocalDateTime.now())
                .build();

        Booking savedBooking = bookingRepository.save(booking);
        log.info("Booking created: {} for user: {}", referenceNumber, userId);

        // Send confirmation email (failure does not affect booking)
        try {
            notificationService.sendBookingConfirmation(savedBooking, user, event);
        } catch (Exception e) {
            log.error("Failed to send confirmation email for booking {}: {}", referenceNumber, e.getMessage());
        }

        return toResponse(savedBooking);
    }

    public List<BookingResponse> getBookingsByUser(String userId) {
        return bookingRepository.findByUserId(userId)
                .stream()
                .map(this::toResponse)
                .collect(Collectors.toList());
    }

    public BookingResponse getBookingById(String userId, String bookingId) {
        Booking booking = bookingRepository.findByIdAndUserId(bookingId, userId)
                .orElseThrow(() -> new ResourceNotFoundException("Booking not found with id: " + bookingId));
        return toResponse(booking);
    }

    private BookingResponse toResponse(Booking booking) {
        return BookingResponse.builder()
                .id(booking.getId())
                .referenceNumber(booking.getReferenceNumber())
                .eventId(booking.getEventId())
                .eventName(booking.getEventSnapshot().getName())
                .eventDateTime(booking.getEventSnapshot().getDateTime())
                .venue(booking.getEventSnapshot().getVenue())
                .quantity(booking.getQuantity())
                .totalAmount(booking.getTotalAmount())
                .status(booking.getStatus())
                .createdAt(booking.getCreatedAt())
                .build();
    }

    private String generateReferenceNumber() {
        String date = LocalDate.now().format(DateTimeFormatter.ofPattern("yyyyMMdd"));
        String suffix = RANDOM.ints(4, 0, ALPHANUMERIC.length())
                .mapToObj(i -> String.valueOf(ALPHANUMERIC.charAt(i)))
                .collect(Collectors.joining());
        return "BK-" + date + "-" + suffix;
    }
}
