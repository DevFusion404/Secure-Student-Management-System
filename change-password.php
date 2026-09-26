<?php
require_once 'security.php';
require_once 'input-validation.php';

// Supporting Input Validation: allow only this page's expected request fields and formats.
validateRequestFields(
  array(),
  array('csrf_token' => 'token', 'submit' => 'action', 'password' => 'password')
);
include_once 'database.php';
include_once 'csrf.php';
verifyCSRFOnPost();

$message = '';

if (empty($_SESSION['change_password_email'])) {
  header("Location:login.php");
  exit();
}

if (isset($_POST['submit'])) {
  $email = $_SESSION['change_password_email'];
  $password = $_POST['password'];

  if (strlen($password) < 8
      || !preg_match('/[A-Z]/', $password)
      || !preg_match('/[a-z]/', $password)
      || !preg_match('/[0-9]/', $password)) {
    $message = "<p style='width:100%;text-align:center'>Password must be at least 8 characters and include an uppercase letter, a lowercase letter, and a number.</p>";
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
      "UPDATE user SET password=?, must_change_password=0 WHERE email=?"
    );
    $stmt->bind_param("ss", $hash, $email);
    $stmt->execute();

    unset($_SESSION['change_password_email']);
    session_regenerate_id(true);
    header("Location:login.php");
    exit();
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Change Password</title>
  <link href="assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/custom.css" rel="stylesheet">
</head>
<body class="login">
  <div>
    <div class="login_wrapper">
      <div class="animate form login_form">
        <section class="login_content">
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <h1>Change Password</h1>
            <?php echo $message; ?>
            <div class="form-group">
              <input name="password" type="password" class="form-control" placeholder="New password"
                     minlength="8" required>
              <small>Password must be at least 8 characters and include uppercase, lowercase, and a number.</small>
            </div>
            <button name="submit" value="submit" type="submit" class="btn btn-success btn-block btn-flat">
              Change Password
            </button>
          </form>
        </section>
      </div>
    </div>
  </div>
</body>
</html>
