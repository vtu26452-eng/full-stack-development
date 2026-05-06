package com.eventbooking.dto;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class PaymentRequest {
    private String cardholderName;
    private String cardNumber;
    private String expiryMonth;
    private String expiryYear;
    private String cvv;
    private boolean simulateFailure;
}
