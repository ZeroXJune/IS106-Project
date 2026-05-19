<?php
// ============================================================
//  includes/config.php
//  Put this file in: htdocs/worklink/includes/config.php
//  Include at the top of every PHP page.
// ============================================================

session_start();

// ── DATABASE ─────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // blank for default XAMPP
define('DB_NAME', 'worklink');

function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div class="alert alert-danger m-4">Database error: ' . $conn->connect_error . '</div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// ── AUTH HELPERS ─────────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['user']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin(string $redirectTo = 'login.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirectTo");
        exit;
    }
}

function requireRole(string $role, string $redirectTo = 'login.php'): void {
    requireLogin($redirectTo);
    if (currentUser()['role'] !== $role) {
        header("Location: index.php");
        exit;
    }
}

// ── FLASH MESSAGES ───────────────────────────────────────────
function flash(string $key, string $msg, string $type = 'success'): void {
    $_SESSION['flash'][$key] = ['msg' => $msg, 'type' => $type];
}

function getFlash(string $key): ?array {
    if (isset($_SESSION['flash'][$key])) {
        $f = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $f;
    }
    return null;
}

// ── SANITIZE ─────────────────────────────────────────────────
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
