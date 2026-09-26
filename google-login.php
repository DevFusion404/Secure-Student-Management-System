<?php

require_once 'security.php';

require_once 'config.php';

try {
    // OIDC callback validation: bind this authorization request to this browser session.
    $oidcState = bin2hex(random_bytes(32));
    $oidcNonce = bin2hex(random_bytes(32));
} catch (Throwable $exception) {
    error_log('OIDC security-token generation failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Google sign in is temporarily unavailable. Please try again.');
}

$_SESSION['oidc_state'] = $oidcState;
$_SESSION['oidc_nonce'] = $oidcNonce;
$_SESSION['oidc_started_at'] = time();

$client->setState($oidcState);

// Allow the session cookie on Google's top-level callback so these values can be checked.
if (!headers_sent()) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), session_id(), [
        'expires' => 0,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

$authUrl = $client->createAuthUrl(null, ['nonce' => $oidcNonce]);

header("Location: ".$authUrl);

exit();

?>
