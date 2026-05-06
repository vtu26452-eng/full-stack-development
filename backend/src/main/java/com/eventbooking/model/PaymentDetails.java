package com.eventbooking.model;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class PaymentDetails {
    private String cardholderName;
    private String maskedCardNumber;   // "****-****-****-1234"
    private boolean simulatedSuccess;
}
