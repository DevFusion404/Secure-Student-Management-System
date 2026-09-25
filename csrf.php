<?php
/*
 * CSRF protection helpers (synchronizer token pattern).
 *
 * One random token is stored per session and embedded as a hidden
 * "csrf_token" field in every state-changing form. Every POST request
 * must send it back, and it is compared with hash_equals() so the
 * check does not leak timing information.
 */

// Ensure a hardened session exists (no-op if the page already loaded it).
require_once __DIR__ . '/security.php';

/**
 * Return the current session's CSRF token, creating it on first use.
 */
function generateCSRFToken() {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

/**
 * Stop the request with HTTP 403 unless $token matches the session token.
 */
function verifyCSRFToken($token) {
  if (!is_string($token) || $token === ''
      || empty($_SESSION['csrf_token'])
      || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    exit('Invalid or missing CSRF token. Please go back, reload the page and try again.');
  }
  return true;
}

/**
 * Verify the CSRF token on every POST request. Call this at the top of a
 * page, before any output and before any database writes.
 */
function verifyCSRFOnPost() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '');
  }
}
