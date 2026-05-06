package com.eventbooking.model;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class EventSnapshot {
    private String name;
    private String venue;
    private LocalDateTime dateTime;
    private BigDecimal ticketPrice;
}
