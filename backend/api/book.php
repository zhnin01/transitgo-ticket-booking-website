<?php
require __DIR__ . '/../config.php';
method('POST'); $u = require_login(); $in = input();
$rid = (int)($in['route_id'] ?? 0); $date = (string)($in['date'] ?? '');
$seat = strtoupper(trim((string)($in['seat'] ?? '')));
$lug = max(0, min(5, (int)($in['luggage'] ?? 0)));
$method = (($in['method'] ?? '') === 'Cash') ? 'Cash' : 'Card';

$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date || $date < date('Y-m-d')) out(['error' => 'Choose a valid travel date (today or later).'], 422);
if (!preg_match('/^([A-Z])([0-9]{1,2})$/', $seat, $m) || strpos(SEAT_ROWS, $m[1]) === false || (int)$m[2] < 1 || (int)$m[2] > SEAT_COLS)
    out(['error' => 'That seat does not exist. Pick one from the seat map.'], 422);

$q = db()->prepare("SELECT price FROM routes WHERE route_id = ? AND route_status = 'Active'"); $q->execute([$rid]);
$price = $q->fetchColumn();
if ($price === false) out(['error' => 'Route not found.'], 404);

// The database enforces "one active booking per seat" with a unique key (see database/upgrade_innodb.sql),
// so two people clicking the same seat at once cannot both succeed. We just catch the duplicate error.
$pdo = db();
try {
    $pdo->prepare("INSERT INTO bookings (passenger_id,route_id,travel_date,seat_number,booking_status,booking_date,payment_status,payment_method,total_amount,luggage_count)
                   VALUES (?,?,?,?,'Confirmed',CURDATE(),?,?,?,?)")
        ->execute([$u['passenger_id'], $rid, $date, $seat, $method === 'Card' ? 'Paid' : 'Pending', $method, $price, $lug]);
} catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1062) out(['error' => "Seat $seat is already taken on that date."], 409); // duplicate key
    throw $e;
}
out(['booking_id' => (int)$pdo->lastInsertId()], 201);
