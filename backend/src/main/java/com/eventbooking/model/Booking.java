package com.eventbooking.model;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.index.Indexed;
import org.springframework.data.mongodb.core.mapping.Document;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
@Document(collection = "bookings")
public class Booking {

    @Id
    private String id;

    @Indexed(unique = true)
    private String referenceNumber;

    private String userId;
    private String eventId;
    private EventSnapshot eventSnapshot;
    private int quantity;
    private BigDecimal totalAmount;
    private BookingStatus status;
    private PaymentDetails paymentDetails;
    private LocalDateTime createdAt;
}
