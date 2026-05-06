package com.eventbooking.service;

import com.eventbooking.model.Booking;
import com.eventbooking.model.Event;
import com.eventbooking.model.User;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.retry.annotation.Backoff;
import org.springframework.retry.annotation.Recover;
import org.springframework.retry.annotation.Retryable;
import org.springframework.stereotype.Service;
import org.springframework.web.client.RestClientException;
import org.springframework.web.client.RestTemplate;

import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

@Slf4j
@Service
public class NotificationService {

    private final RestTemplate resendRestTemplate;

    @Value("${resend.from-email}")
    private String fromEmail;

    private static final String RESEND_API_URL = "https://api.resend.com/emails";
    private static final DateTimeFormatter DATE_FORMATTER =
            DateTimeFormatter.ofPattern("dd MMMM yyyy, hh:mm a");

    public NotificationService(@Qualifier("resendRestTemplate") RestTemplate resendRestTemplate) {
        this.resendRestTemplate = resendRestTemplate;
    }

    @Retryable(
        retryFor = {RestClientException.class},
        maxAttempts = 3,
        backoff = @Backoff(delay = 2000)
    )
    public void sendBookingConfirmation(Booking booking, User user, Event event) {
        // Skip if Resend API key is not configured
        if (fromEmail == null || fromEmail.isBlank() ||
            fromEmail.equals("noreply@yourdomain.com")) {
            log.warn("Resend not configured — skipping email for booking {}", booking.getReferenceNumber());
            return;
        }

        String subject = "Booking Confirmed – " + event.getName() + " | Ref: " + booking.getReferenceNumber();
        String body = buildEmailBody(booking, user, event);

        Map<String, Object> emailPayload = new HashMap<>();
        emailPayload.put("from", fromEmail);
        emailPayload.put("to", List.of(user.getEmail()));
        emailPayload.put("subject", subject);
        emailPayload.put("text", body);

        log.info("Sending booking confirmation email to {} for booking {}", user.getEmail(), booking.getReferenceNumber());
        resendRestTemplate.postForObject(RESEND_API_URL, emailPayload, Map.class);
        log.info("Booking confirmation email sent successfully to {}", user.getEmail());
    }

    @Recover
    public void recoverSendBookingConfirmation(Exception ex, Booking booking, User user, Event event) {
        log.error("Failed to send booking confirmation email to {} for booking {} after 3 attempts. Error: {}",
                user.getEmail(), booking.getReferenceNumber(), ex.getMessage());
        // Booking remains CONFIRMED — email failure does not roll back the booking
    }

    private String buildEmailBody(Booking booking, User user, Event event) {
        String formattedDate = event.getDateTime().format(DATE_FORMATTER);
        return String.format("""
                Dear %s,
                
                Your booking has been confirmed!
                
                Booking Reference : %s
                Event             : %s
                Date & Time       : %s
                Venue             : %s
                Tickets           : %d
                Total Paid        : ₹%.2f
                
                Please keep this email as your booking confirmation.
                We look forward to seeing you at the event!
                
                Thank you for booking with us.
                
                Event Ticket Booking System
                """,
                user.getName(),
                booking.getReferenceNumber(),
                event.getName(),
                formattedDate,
                event.getVenue(),
                booking.getQuantity(),
                booking.getTotalAmount()
        );
    }
}
