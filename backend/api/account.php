<?php
require __DIR__ . '/../config.php';
$u = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $q = db()->prepare('SELECT first_name, last_name, email, phone_no, street_name, city, postcode FROM passengers WHERE passenger_id = ?');
    $q->execute([$u['passenger_id']]);
    out(['account' => $q->fetch()]);
}
method('POST'); $in = input(); $action = $in['action'] ?? '';

if ($action === 'update') {
    $f = trim($in['first_name'] ?? ''); $l = trim($in['last_name'] ?? ''); $phone = trim($in['phone_no'] ?? '');
    $street = trim($in['street_name'] ?? ''); $city = trim($in['city'] ?? ''); $post = trim($in['postcode'] ?? '');
    if (!$f || !$l || !$phone || !$street || !$city || !$post) out(['error' => 'Please fill in every field.'], 422);
    if (mb_strlen($f) > 50 || mb_strlen($l) > 50 || mb_strlen($phone) > 20 || mb_strlen($street) > 100 || mb_strlen($city) > 20 || mb_strlen($post) > 10)
        out(['error' => 'One of the fields is too long (city max 20 characters, postcode 10, phone 20).'], 422);
    db()->prepare('UPDATE passengers SET first_name=?, last_name=?, phone_no=?, street_name=?, city=?, postcode=? WHERE passenger_id=?')
        ->execute([$f, $l, $phone, $street, $city, $post, $u['passenger_id']]);
    out(['ok' => true]);
}

if ($action === 'password') {
    $cur = (string)($in['current_password'] ?? ''); $new = (string)($in['new_password'] ?? '');
    if (strlen($new) < 8) out(['error' => 'New password must be at least 8 characters.'], 422);
    $q = db()->prepare('SELECT password_hash FROM passengers WHERE passenger_id = ?'); $q->execute([$u['passenger_id']]);
    $hash = $q->fetchColumn();
    if (!$hash || !password_verify($cur, $hash)) out(['error' => 'Your current password is wrong.'], 403);
    db()->prepare('UPDATE passengers SET password_hash = ? WHERE passenger_id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $u['passenger_id']]);
    session_regenerate_id(true);
    out(['ok' => true]);
}
out(['error' => 'Unknown action.'], 400);
