<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

return [
    'Testing 1 - Valid User Login Testing' => static function (): void {
        $response = login([
            'email' => requireEnv('REGRESSION_EMAIL'),
            'password' => requireEnv('REGRESSION_PASSWORD'),
        ]);
        assertSameValue(302, $response['status'], 'Valid login should redirect.');
        assertTrue($response['location'] !== 'change-password.php', 'This account must not require a first-login reset.');
    },

    'Testing 2 - Invalid Login Testing' => static function (): void {
        $response = login([
            'email' => requireEnv('REGRESSION_EMAIL'),
            'password' => 'definitely-not-the-password',
        ]);
        assertTrue(strpos($response['body'], 'Incorrect username or password') !== false, 'Invalid login message was not shown.');
    },

    'Testing 3 - SQL Injection Attack Testing' => static function (): void {
        $response = login([
            'email' => "' OR 1=1 -- ",
            'password' => 'anything',
        ]);
        assertTrue(strpos($response['body'], 'Incorrect username or password') !== false, 'Injection input must not authenticate.');
        assertTrue($response['status'] !== 302, 'Injection input must not redirect as an authenticated user.');
    },

    'Testing 4 - Password Hash Storage Verification Testing' => static function (): void {
        $email = requireEnv('REGRESSION_EMAIL');
        $db = database();
        $stmt = $db->prepare('SELECT password FROM user WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        assertSameValue(1, $result->num_rows, 'Configured regression user was not found.');
        $hash = (string) $result->fetch_assoc()['password'];
        $info = password_get_info($hash);
        assertTrue($info['algo'] !== 0, 'Password is not stored using a password-hashing algorithm.');
        assertTrue(strtolower($hash) !== md5($email), 'Password storage must not be an MD5 digest.');
    },

    'Testing 5 - Password Verification Testing' => static function (): void {
        $db = database();
        $email = requireEnv('REGRESSION_EMAIL');
        $password = requireEnv('REGRESSION_PASSWORD');
        $stmt = $db->prepare('SELECT password FROM user WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        assertTrue($row !== null, 'Configured regression user was not found.');
        $hash = (string) $row['password'];
        assertTrue(password_verify($password, $hash), 'The configured password does not verify against its stored hash.');
    },

    'Testing 6 - Incorrect Password Verification Testing' => static function (): void {
        $db = database();
        $email = requireEnv('REGRESSION_EMAIL');
        $stmt = $db->prepare('SELECT password FROM user WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        assertTrue($row !== null, 'Configured regression user was not found.');
        $hash = (string) $row['password'];
        assertTrue(!password_verify('wrong-password-123', $hash), 'An incorrect password must not verify.');
    },

    'Testing 7 - First Login Password Change Testing' => static function (): void {
        $response = login([
            'email' => requireEnv('REGRESSION_DEFAULT_EMAIL'),
            'password' => requireEnv('REGRESSION_DEFAULT_PASSWORD'),
        ]);
        assertSameValue(302, $response['status'], 'Default login should redirect.');
        assertTrue(strpos($response['location'], 'change-password.php') !== false, 'First login did not require a password change.');

        $page = httpRequest('change-password.php', [], $response['cookies']);
        $token = csrfToken($page['body']);
        $changed = httpRequest('change-password.php', [
            'password' => requireEnv('REGRESSION_NEW_PASSWORD'),
            'csrf_token' => $token,
            'submit' => 'submit',
        ], $page['cookies']);
        assertSameValue(302, $changed['status'], 'Password change should redirect back to login.');
        assertTrue(strpos($changed['location'], 'login.php') !== false, 'Password change did not return to login.');
    },

    'Testing 8 - New Password Login Testing' => static function (): void {
        $response = login([
            'email' => requireEnv('REGRESSION_DEFAULT_EMAIL'),
            'password' => requireEnv('REGRESSION_NEW_PASSWORD'),
        ]);
        assertSameValue(302, $response['status'], 'New password login should redirect.');
        assertTrue(strpos($response['location'], 'change-password.php') === false, 'New password still requires a reset.');
    },

    'Testing 9 - Old Default Password Testing' => static function (): void {
        $response = login([
            'email' => requireEnv('REGRESSION_DEFAULT_EMAIL'),
            'password' => requireEnv('REGRESSION_DEFAULT_PASSWORD'),
        ]);
        assertTrue(strpos($response['body'], 'Incorrect username or password') !== false, 'Old default password is still accepted.');
    },

    'Testing 10 - Google Login Button Testing' => static function (): void {
        $page = httpRequest('login.php');
        assertTrue(strpos($page['body'], 'google-login.php') !== false, 'Google login button is missing.');
        assertTrue(stripos($page['body'], 'Sign in with Google') !== false, 'Google login button label is missing.');
    },

    'Testing 11 - Google Authentication Flow Testing' => static function (): void {
        requireEnv('GOOGLE_CLIENT_ID');
        $response = httpRequest('google-login.php');
        assertSameValue(302, $response['status'], 'Google login should redirect to the provider.');
        assertTrue(strpos($response['location'], 'accounts.google.com') !== false, 'Redirect is not a Google authorization URL.');
    },

    'Testing 12 - Google User Session Testing' => static function (): void {
        $response = httpRequest('google-callback.php');
        assertTrue(strpos($response['body'], 'authorization code is missing') !== false, 'Callback should reject a missing authorization code.');
        assertTrue(strpos($response['body'], 'google_logged_in') === false, 'A missing code must not create a Google session.');
    },

    'Testing 13 - Existing Login Functionality Regression Testing' => static function (): void {
        $response = login([
            'email' => requireEnv('REGRESSION_EMAIL'),
            'password' => requireEnv('REGRESSION_PASSWORD'),
        ]);
        assertSameValue(302, $response['status'], 'Existing login no longer redirects.');
        assertTrue(strpos($response['location'], 'login.php') === false, 'Existing valid login was redirected back to login.');
    },

    'Testing 14 - Role-Based Login Regression Testing' => static function (): void {
        $expectedRole = requireEnv('REGRESSION_ROLE');
        $db = database();
        $email = requireEnv('REGRESSION_EMAIL');
        $stmt = $db->prepare('SELECT role FROM user WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        assertTrue($row !== null, 'Configured role user was not found.');
        assertSameValue($expectedRole, $row['role'], 'Configured user role does not match the expected role.');
        $response = login(['email' => $email, 'password' => requireEnv('REGRESSION_PASSWORD')]);
        assertSameValue(302, $response['status'], 'Role-based login failed.');
    },

    'Testing 15 - Complete Authentication Flow Regression Testing' => static function (): void {
        $page = httpRequest('login.php');
        $token = csrfToken($page['body']);
        $response = httpRequest('login.php', [
            'email' => requireEnv('REGRESSION_EMAIL'),
            'password' => requireEnv('REGRESSION_PASSWORD'),
            'csrf_token' => $token,
            'submit' => 'submit',
        ], $page['cookies']);
        assertSameValue(302, $response['status'], 'Complete flow did not authenticate.');
        assertTrue($response['location'] !== '', 'Complete flow did not provide a redirect.');
        $dashboard = httpRequest('index.php', [], $response['cookies']);
        assertTrue($dashboard['status'] !== 302 || strpos($dashboard['location'], 'login.php') === false, 'Authenticated session cannot access the dashboard.');
    },
];
