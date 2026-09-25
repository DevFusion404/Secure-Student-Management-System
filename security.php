<?php
/*
 * Secure session bootstrap and HTTP security headers.
 *
 * Every page must require_once this file as its very first statement,
 * before any output, instead of calling session_start() directly.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
  // Reject session IDs the server did not issue, and never accept an ID from the URL.
  ini_set('session.use_strict_mode', '1');
  ini_set('session.use_only_cookies', '1');

  session_set_cookie_params([
    'lifetime' => 0,         // session cookie: gone when the browser closes
    'path'     => '/',
    'secure'   => false,     // app runs locally over HTTP; set true once HTTPS is configured
    'httponly' => true,      // JavaScript cannot read the session cookie
    'samesite' => 'Strict'   // cookie is not sent on cross-site requests (CSRF)
  ]);

  session_start();
}

// Security headers. require_once guarantees they are sent once per request.
if (!headers_sent()) {
  header("X-Frame-Options: DENY");
  header("X-Content-Type-Options: nosniff");
  header("Referrer-Policy: no-referrer");

  // Scripts only from this site: all page JS lives in assets/js/app.js, so
  // injected inline <script> (XSS) will not run. Inline styles stay allowed
  // because the templates use style="" attributes heavily.
  header("Content-Security-Policy: "
    . "default-src 'self'; "
    . "script-src 'self'; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
    . "font-src 'self' data: https://fonts.gstatic.com; "
    . "img-src 'self' data: https://st3.depositphotos.com; "
    . "object-src 'none'; "
    . "base-uri 'self'; "
    . "form-action 'self'; "
    . "frame-ancestors 'none'");
}
