package com.eventbooking.service;

import com.eventbooking.dto.PaymentRequest;
import com.eventbooking.dto.PaymentResult;
import com.eventbooking.exception.PaymentFailedException;
import com.eventbooking.exception.PaymentValidationException;
import org.springframework.stereotype.Service;

import java.time.LocalDate;

@Service
public class PaymentService {

    public PaymentResult processPayment(PaymentRequest request) {
        // Validate all fields
        validatePayment(request);

        // If simulateFailure flag is set, throw failure after validation
        if (request.isSimulateFailure()) {
            throw new PaymentFailedException("Payment simulation failed as requested.");
        }

        // Build masked card number: ****-****-****-XXXX
        String maskedCard = maskCardNumber(request.getCardNumber());

        return new PaymentResult(true, maskedCard);
    }

    private void validatePayment(PaymentRequest request) {
        // Validate cardholder name
        if (request.getCardholderName() == null || request.getCardholderName().isBlank()) {
            throw new PaymentValidationException("Cardholder name is required.");
        }

        // Validate card number: exactly 16 digits
        if (request.getCardNumber() == null || !request.getCardNumber().matches("\\d{16}")) {
            throw new PaymentValidationException("Card number must be exactly 16 digits.");
        }

        // Validate expiry month: 01-12
        if (request.getExpiryMonth() == null || !request.getExpiryMonth().matches("^(0[1-9]|1[0-2])$")) {
            throw new PaymentValidationException("Expiry month must be between 01 and 12.");
        }

        // Validate expiry year: 4-digit year >= current year
        if (request.getExpiryYear() == null || !request.getExpiryYear().matches("\\d{4}")) {
            throw new PaymentValidationException("Expiry year must be a 4-digit year.");
        }
        int year = Integer.parseInt(request.getExpiryYear());
        int currentYear = LocalDate.now().getYear();
        if (year < currentYear) {
            throw new PaymentValidationException("Card has expired. Expiry year must be " + currentYear + " or later.");
        }

        // Validate CVV: exactly 3 digits
        if (request.getCvv() == null || !request.getCvv().matches("\\d{3}")) {
            throw new PaymentValidationException("CVV must be exactly 3 digits.");
        }
    }

    private String maskCardNumber(String cardNumber) {
        String last4 = cardNumber.substring(cardNumber.length() - 4);
        return "****-****-****-" + last4;
    }
}
