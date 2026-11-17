<?php
require __DIR__ . '/../config.php';
method('POST');
$_SESSION = []; session_destroy();
out(['ok' => true]);
