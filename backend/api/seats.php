<?php
require __DIR__ . '/../config.php';
method('GET');
$rid = (int)($_GET['route_id'] ?? 0); $date = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) out(['error' => 'Invalid date.'], 422);
$q = db()->prepare("SELECT seat_number FROM bookings WHERE route_id = ? AND travel_date = ? AND booking_status <> 'Cancelled'");
$q->execute([$rid, $date]);
out(['rows' => str_split(SEAT_ROWS), 'cols' => SEAT_COLS, 'taken' => array_map('strtoupper', $q->fetchAll(PDO::FETCH_COLUMN))]);
