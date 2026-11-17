<?php
require __DIR__ . '/../config.php';
method('GET'); require_admin();
$stats = db()->query("SELECT
    COALESCE(SUM(booking_status<>'Cancelled'),0) AS active,
    COALESCE(SUM(booking_status='Cancelled'),0) AS cancelled,
    COALESCE(SUM(CASE WHEN booking_status<>'Cancelled' AND payment_status='Paid' THEN total_amount END),0) AS paid,
    COALESCE(SUM(CASE WHEN booking_status<>'Cancelled' AND payment_status='Pending' THEN total_amount END),0) AS pending
    FROM bookings")->fetch();
$top = db()->query("SELECT CONCAT(r.start_city,' → ',r.end_city) AS name, COUNT(*) AS n FROM bookings b
    JOIN routes r ON r.route_id=b.route_id WHERE b.booking_status<>'Cancelled' GROUP BY b.route_id ORDER BY n DESC LIMIT 1")->fetch();
$all = db()->query("SELECT b.booking_id, b.travel_date, b.seat_number, b.booking_status, b.payment_status, b.total_amount,
    CONCAT(p.first_name,' ',p.last_name) AS passenger, CONCAT(r.start_city,' → ',r.end_city) AS route
    FROM bookings b JOIN passengers p ON p.passenger_id=b.passenger_id JOIN routes r ON r.route_id=b.route_id
    ORDER BY b.travel_date DESC, b.booking_id DESC")->fetchAll();
out(['stats' => $stats, 'top_route' => $top ?: null, 'bookings' => $all]);
