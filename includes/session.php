<?php
$sessionLifetime = 60 * 60 * 24 * 7; // 7 days

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
    ini_set('session.cookie_lifetime', (string) $sessionLifetime);

    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}
