package com.eventbooking.dto;

import jakarta.validation.Valid;
import jakarta.validation.constraints.Max;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Data;

@Data
public class BookingRequest {

    @NotBlank(message = "Event ID is required")
    private String eventId;

    @Min(value = 1, message = "Quantity must be at least 1")
    @Max(value = 5, message = "Maximum 5 tickets per booking")
    private int quantity;

    @NotNull(message = "Payment details are required")
    @Valid
    private PaymentRequest payment;
}
