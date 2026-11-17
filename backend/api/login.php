<?php
require __DIR__ . '/../config.php';
method('POST'); $in = input();
$email = strtolower(trim($in['email'] ?? '')); $pw = (string)($in['password'] ?? '');
$q = db()->prepare('SELECT passenger_id, password_hash FROM passengers WHERE email = ?'); $q->execute([$email]);
$row = $q->fetch();
if (!$row || !$row['password_hash'] || !password_verify($pw, $row['password_hash'])) out(['error' => 'Wrong email or password.'], 401);
session_regenerate_id(true);
$_SESSION['pid'] = (int)$row['passenger_id'];
out(['user' => current_user()]);
