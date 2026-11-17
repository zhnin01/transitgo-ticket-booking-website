<?php
require __DIR__ . '/../config.php';
method('POST'); require_admin(); $in = input();
$id = (int)($in['booking_id'] ?? 0); $action = $in['action'] ?? '';

if ($action === 'mark_paid') {
    $q = db()->prepare("UPDATE bookings SET payment_status='Paid' WHERE booking_id=? AND booking_status<>'Cancelled' AND payment_status='Pending'");
} elseif ($action === 'cancel') {
    $q = db()->prepare("UPDATE bookings SET booking_status='Cancelled', cancellation_date=CURDATE() WHERE booking_id=? AND booking_status<>'Cancelled'");
} else out(['error' => 'Unknown action.'], 400);

$q->execute([$id]);
if ($q->rowCount() === 0) out(['error' => 'Nothing to change: the booking is missing, already cancelled, or already paid.'], 409);
out(['ok' => true]);
