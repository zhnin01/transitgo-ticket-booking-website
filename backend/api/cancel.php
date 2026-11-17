<?php
require __DIR__ . '/../config.php';
method('POST'); $u = require_login(); $in = input();
$q = db()->prepare("UPDATE bookings SET booking_status='Cancelled', cancellation_date=CURDATE()
                    WHERE booking_id=? AND passenger_id=? AND booking_status<>'Cancelled' AND travel_date >= CURDATE()");
$q->execute([(int)($in['booking_id'] ?? 0), $u['passenger_id']]);
if ($q->rowCount() === 0) out(['error' => 'That booking can’t be cancelled (already cancelled, in the past, or not yours).'], 409);
out(['ok' => true]);
