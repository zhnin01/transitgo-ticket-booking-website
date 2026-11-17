<?php
require __DIR__ . '/../config.php';
method('POST'); $in = input();
$f = trim($in['first_name'] ?? ''); $l = trim($in['last_name'] ?? '');
$email = strtolower(trim($in['email'] ?? '')); $pw = (string)($in['password'] ?? '');
$phone = trim($in['phone_no'] ?? ''); $street = trim($in['street_name'] ?? '');
$city = trim($in['city'] ?? ''); $post = trim($in['postcode'] ?? '');

if (!$f || !$l || !$phone || !$street || !$city || !$post) out(['error' => 'Please fill in every field.'], 422);
if (mb_strlen($f) > 50 || mb_strlen($l) > 50 || mb_strlen($phone) > 20 || mb_strlen($street) > 100 || mb_strlen($city) > 20 || mb_strlen($post) > 10 || mb_strlen($email) > 100)
    out(['error' => 'One of the fields is too long (city max 20 characters, postcode 10, phone 20).'], 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) out(['error' => 'Please enter a valid email address.'], 422);
if (strlen($pw) < 8) out(['error' => 'Password must be at least 8 characters.'], 422);

$q = db()->prepare('SELECT 1 FROM passengers WHERE email = ?'); $q->execute([$email]);
if ($q->fetch()) out(['error' => 'An account with that email already exists.'], 409);

db()->prepare("INSERT INTO passengers (first_name,last_name,email,phone_no,street_name,city,postcode,country,date_registered,password_hash)
               VALUES (?,?,?,?,?,?,?,'UK',CURDATE(),?)")
    ->execute([$f, $l, $email, $phone, $street, $city, $post, password_hash($pw, PASSWORD_DEFAULT)]);
session_regenerate_id(true);
$_SESSION['pid'] = (int)db()->lastInsertId();
out(['user' => current_user()], 201);
