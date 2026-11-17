<?php
require __DIR__ . '/../config.php';
method('GET');
$from = $_GET['from'] ?? ''; $to = $_GET['to'] ?? ''; $date = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

$cities = db()->query('SELECT start_city AS c FROM routes UNION SELECT end_city FROM routes ORDER BY c')->fetchAll(PDO::FETCH_COLUMN);
$sql = "SELECT r.route_id, r.start_city, r.end_city, r.distance, r.time_taken, r.transport_type, r.price,
               r.departure_time, r.arrival_time,
               (SELECT COUNT(*) FROM bookings b WHERE b.route_id = r.route_id AND b.travel_date = :d AND b.booking_status <> 'Cancelled') AS taken
        FROM routes r WHERE r.route_status = 'Active'";
$args = [':d' => $date];
if ($from !== '') { $sql .= ' AND r.start_city = :f'; $args[':f'] = $from; }
if ($to !== '')   { $sql .= ' AND r.end_city = :t';   $args[':t'] = $to; }
$q = db()->prepare($sql . ' ORDER BY r.departure_time'); $q->execute($args);
out(['cities' => $cities, 'routes' => $q->fetchAll()]);
