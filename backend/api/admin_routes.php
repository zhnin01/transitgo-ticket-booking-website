<?php
require __DIR__ . '/../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    out(['routes' => db()->query('SELECT route_id, start_city, end_city, distance, time_taken, transport_type, price,
        departure_time, arrival_time, route_status FROM routes ORDER BY route_id')->fetchAll()]);
}
method('POST'); $in = input(); $action = $in['action'] ?? '';

if ($action === 'toggle') {
    $q = db()->prepare("UPDATE routes SET route_status = IF(route_status = 'Active', 'Inactive', 'Active') WHERE route_id = ?");
    $q->execute([(int)($in['route_id'] ?? 0)]);
    if ($q->rowCount() === 0) out(['error' => 'Route not found.'], 404);
    out(['ok' => true]);
}

if ($action === 'save') {
    $r = $in['route'] ?? [];
    $id = (int)($r['route_id'] ?? 0);
    $start = trim($r['start_city'] ?? ''); $end = trim($r['end_city'] ?? '');
    $dist = $r['distance'] ?? ''; $price = $r['price'] ?? ''; $type = $r['transport_type'] ?? '';
    $time = function ($v) { return preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', (string)$v) ? substr($v, 0, 5) . ':00' : null; };
    $dep = $time($r['departure_time'] ?? ''); $arr = $time($r['arrival_time'] ?? '');

    if ($start === '' || $end === '' || mb_strlen($start) > 50 || mb_strlen($end) > 50) out(['error' => 'Enter both cities (50 characters max).'], 422);
    if (strcasecmp($start, $end) === 0) out(['error' => 'Start and end city must be different.'], 422);
    if (!is_numeric($dist) || $dist < 0.01 || $dist > 9999.99) out(['error' => 'Distance must be between 0.01 and 9999.99 km.'], 422);
    if (!is_numeric($price) || $price < 0 || $price > 999999.99) out(['error' => 'Enter a valid price.'], 422);
    if (!in_array($type, ['Train', 'Bus'], true)) out(['error' => 'Transport type must be Train or Bus.'], 422);
    if (!$dep || !$arr) out(['error' => 'Enter valid departure and arrival times.'], 422);

    $mins = (int)round((strtotime("1970-01-01 $arr") - strtotime("1970-01-01 $dep")) / 60);
    if ($mins <= 0) $mins += 1440; // arrives after midnight
    $dur = sprintf('%02d:%02d:00', intdiv($mins, 60), $mins % 60);

    if ($id > 0) {
        db()->prepare('UPDATE routes SET start_city=?, end_city=?, distance=?, time_taken=?, transport_type=?, price=?, departure_time=?, arrival_time=? WHERE route_id=?')
            ->execute([$start, $end, $dist, $dur, $type, $price, $dep, $arr, $id]);
    } else {
        db()->prepare("INSERT INTO routes (start_city,end_city,distance,time_taken,transport_type,price,departure_time,arrival_time,route_status)
                       VALUES (?,?,?,?,?,?,?,?,'Active')")
            ->execute([$start, $end, $dist, $dur, $type, $price, $dep, $arr]);
    }
    out(['ok' => true]);
}
out(['error' => 'Unknown action.'], 400);
