<?php

require_once 'security.php';
require_once 'input-validation.php';

// Supporting Input Validation: allow only this page's expected request fields and formats.
validateRequestFields(
  array('code' => 'code', 'scope' => 'text', 'authuser' => 'id', 'prompt' => 'text', 'state' => 'code', 'error' => 'text', 'error_description' => 'text', 'error_uri' => 'code', 'error_subtype' => 'text'),
  array()
);

require_once 'config.php';

$authenticated = false;
$pageMessage = 'We could not complete your Google sign in.';
$googleName = '';
$googleEmail = '';

if (!isset($_GET['code']) && !isset($_GET['error'])) {
    http_response_code(400);
    $pageMessage = 'The Google authorization code is missing. Please start again from the login page.';
} else {
    $expectedState = isset($_SESSION['oidc_state']) ? (string) $_SESSION['oidc_state'] : '';
    $expectedNonce = isset($_SESSION['oidc_nonce']) ? (string) $_SESSION['oidc_nonce'] : '';
    $oidcStartedAt = isset($_SESSION['oidc_started_at']) ? (int) $_SESSION['oidc_started_at'] : 0;

    // State and nonce are single-use, including failed and cancelled callback attempts.
    unset($_SESSION['oidc_state'], $_SESSION['oidc_nonce'], $_SESSION['oidc_started_at']);

    // Restore the application's stricter cookie policy after returning from Google.
    if (!headers_sent()) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), session_id(), [
            'expires' => 0,
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    $receivedState = isset($_GET['state']) ? (string) $_GET['state'] : '';
    $validStateFormat = preg_match('/^[a-f0-9]{64}$/D', $receivedState) === 1;
    $stateMatches = $validStateFormat
        && preg_match('/^[a-f0-9]{64}$/D', $expectedState) === 1
        && hash_equals($expectedState, $receivedState);
    $requestIsFresh = $oidcStartedAt > 0
        && $oidcStartedAt <= time()
        && (time() - $oidcStartedAt) <= 600;

    if (!$stateMatches || !$requestIsFresh || $expectedNonce === '') {
        http_response_code(400);
        $pageMessage = 'The Google sign-in response could not be validated. Please start again from the login page.';
    } elseif (isset($_GET['error'])) {
        http_response_code(400);
        $pageMessage = $_GET['error'] === 'access_denied'
            ? 'Google sign in was cancelled. No local session was created.'
            : 'Google authentication was not completed. Please try again.';
    } else {
        try {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

            if (!is_array($token) || isset($token['error']) || empty($token['id_token']) || !is_string($token['id_token'])) {
                throw new UnexpectedValueException('Google did not return a usable ID token.');
            }

            // OIDC callback validation: verify the signature and required identity claims.
            $claims = $client->verifyIdToken($token['id_token']);
            $clientId = $client->getClientId();
            $issuerIsValid = is_array($claims)
                && isset($claims['iss'])
                && in_array($claims['iss'], ['https://accounts.google.com', 'accounts.google.com'], true);
            $audienceIsValid = is_array($claims)
                && isset($claims['aud'])
                && ((is_string($claims['aud']) && hash_equals($clientId, $claims['aud']))
                    || (is_array($claims['aud']) && in_array($clientId, $claims['aud'], true)));
            $expiryIsValid = is_array($claims)
                && isset($claims['exp'])
                && is_numeric($claims['exp'])
                && (int) $claims['exp'] > time();
            $issuedAtIsValid = is_array($claims)
                && isset($claims['iat'])
                && is_numeric($claims['iat'])
                && (int) $claims['iat'] > 0
                && (int) $claims['iat'] <= (time() + 300);
            $nonceIsValid = is_array($claims)
                && isset($claims['nonce'])
                && is_string($claims['nonce'])
                && hash_equals($expectedNonce, $claims['nonce']);
            $subjectIsValid = is_array($claims)
                && isset($claims['sub'])
                && is_string($claims['sub'])
                && preg_match('/^[\x21-\x7E]{1,255}$/D', $claims['sub']) === 1;
            $emailIsValid = is_array($claims)
                && isset($claims['email'])
                && is_string($claims['email'])
                && filter_var($claims['email'], FILTER_VALIDATE_EMAIL) !== false
                && isset($claims['email_verified'])
                && ($claims['email_verified'] === true || $claims['email_verified'] === 'true');
            $presenterIsValid = !is_array($claims)
                || !isset($claims['azp'])
                || (is_string($claims['azp']) && hash_equals($clientId, $claims['azp']));

            if (!$issuerIsValid || !$audienceIsValid || !$expiryIsValid || !$issuedAtIsValid
                || !$nonceIsValid || !$subjectIsValid || !$emailIsValid || !$presenterIsValid) {
                throw new UnexpectedValueException('Google ID-token claims did not pass validation.');
            }

            $googleEmail = (string) $claims['email'];
            $googleName = isset($claims['name']) && is_string($claims['name']) && trim($claims['name']) !== ''
                ? trim($claims['name'])
                : $googleEmail;

            // Create the existing local session only after every callback check succeeds.
            session_regenerate_id(true);
            $_SESSION['google_email'] = $googleEmail;
            $_SESSION['google_name'] = $googleName;
            $_SESSION['user'] = $googleName;
            $_SESSION['google_logged_in'] = true;

            $authenticated = true;
            $pageMessage = 'Your account is ready. Welcome to the Student Management System.';
        } catch (Throwable $exception) {
            error_log('OIDC callback validation failed: ' . $exception->getMessage());
            http_response_code(400);
            $pageMessage = 'Google authentication could not be validated. Please try again.';
        }
    }
}

$safeName = htmlspecialchars($googleName, ENT_QUOTES, 'UTF-8');
$safeEmail = htmlspecialchars($googleEmail, ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $authenticated ? 'Welcome' : 'Google Sign In'; ?> | Student Management System</title>
  <link rel="icon" href="img/favicon2.png">
  <link href="assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <link href="assets/css/custom.css" rel="stylesheet">
  <link href="assets/css/google-callback.css" rel="stylesheet">
</head>
<body class="google-callback-page">
  <div class="google-page-shell">
    <header class="google-topbar">
      <a class="google-brand" href="login.php">
        <span class="google-brand-icon"><i class="fa fa-institution"></i></span>
        <span>
          <strong>Student Management System</strong>
          <small>Secure academic portal</small>
        </span>
      </a>
      <a class="google-logout" href="logout.php">
        <i class="fa fa-sign-out"></i> Log out
      </a>
    </header>

    <main class="google-main">
      <?php if ($authenticated) { ?>
        <section class="google-welcome-card">
          <div class="google-success-mark">
            <i class="fa fa-check"></i>
          </div>
          <p class="google-eyebrow"><i class="fa fa-shield"></i> Secure sign in complete</p>
          <h1>Welcome<span id="welcome-greeting"></span>, <?php echo $safeName; ?>!</h1>
          <p class="google-lead"><?php echo htmlspecialchars($pageMessage, ENT_QUOTES, 'UTF-8'); ?></p>

          <div class="google-account">
            <span class="google-avatar"><i class="fa fa-user"></i></span>
            <span>
              <strong><?php echo $safeName; ?></strong>
              <small><?php echo $safeEmail; ?></small>
            </span>
            <span class="google-account-status"><i class="fa fa-circle"></i> Active</span>
          </div>

          <div class="google-actions">
            <a href="index.php" class="btn google-primary-button">
              Go to dashboard <i class="fa fa-arrow-right"></i>
            </a>
            <a href="profile.php" class="btn google-secondary-button">
              <i class="fa fa-user"></i> View profile
            </a>
          </div>
        </section>

        <section class="google-feature-row">
          <article class="google-feature-card">
            <span class="google-feature-icon green"><i class="fa fa-graduation-cap"></i></span>
            <h3>Stay connected</h3>
            <p>Keep up with classes, schedules, notices, and academic progress.</p>
          </article>
          <article class="google-feature-card">
            <span class="google-feature-icon blue"><i class="fa fa-calendar"></i></span>
            <h3>Plan your day</h3>
            <p>Find the information you need to make every school day count.</p>
          </article>
          <article class="google-feature-card">
            <span class="google-feature-icon gold"><i class="fa fa-lock"></i></span>
            <h3>Your privacy matters</h3>
            <p>Your account is protected with Google's secure authentication.</p>
          </article>
        </section>
      <?php } else { ?>
        <section class="google-welcome-card google-error-card">
          <div class="google-success-mark error">
            <i class="fa fa-exclamation"></i>
          </div>
          <p class="google-eyebrow">Sign in unavailable</p>
          <h1>We could not sign you in</h1>
          <p class="google-lead"><?php echo htmlspecialchars($pageMessage, ENT_QUOTES, 'UTF-8'); ?></p>
          <a href="login.php" class="btn google-primary-button">
            <i class="fa fa-arrow-left"></i> Return to login
          </a>
        </section>
      <?php } ?>
    </main>

    <footer class="google-footer">
      <span><i class="fa fa-institution"></i> Student Management System</span>
      <span>&copy; <?php echo date('Y'); ?> All Rights Reserved</span>
    </footer>
  </div>

  <script src="assets/vendors/jquery/dist/jquery.min.js"></script>
  <script src="assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="assets/js/google-callback.js"></script>
</body>
</html>
