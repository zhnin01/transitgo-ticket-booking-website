<?php
require __DIR__ . '/../config.php';
method('GET'); $u = require_login();
$q = db()->prepare('SELECT b.booking_id, b.travel_date, b.seat_number, b.booking_status, b.payment_status, b.payment_method,
                           b.total_amount, b.luggage_count, r.start_city, r.end_city, r.departure_time
                    FROM bookings b JOIN routes r ON r.route_id = b.route_id
                    WHERE b.passenger_id = ? ORDER BY b.travel_date DESC, b.booking_id DESC');
$q->execute([$u['passenger_id']]);
out(['bookings' => $q->fetchAll()]);
