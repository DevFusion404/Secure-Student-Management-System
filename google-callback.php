<?php

session_start();

require_once 'config.php';

$authenticated = false;
$pageMessage = 'We could not complete your Google sign in.';
$googleName = '';
$googleEmail = '';

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        $pageMessage = 'Google authentication was not completed. Please try again.';
    } else {
        $client->setAccessToken($token);

        $oauth = new Google\Service\Oauth2($client);
        $googleUser = $oauth->userinfo->get();

        $googleName = (string) $googleUser->name;
        $googleEmail = (string) $googleUser->email;

        session_regenerate_id(true);
        $_SESSION['google_email'] = $googleEmail;
        $_SESSION['google_name'] = $googleName;
        $_SESSION['user'] = $googleName;
        $_SESSION['google_logged_in'] = true;

        $authenticated = true;
        $pageMessage = 'Your account is ready. Welcome to the Student Management System.';
    }
} else {
    $pageMessage = 'The Google authorization code is missing. Please start again from the login page.';
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
