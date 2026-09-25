<?php

declare(strict_types=1);

const REGRESSION_ROOT = __DIR__ . '/../..';

final class RegressionFailure extends RuntimeException
{
}

final class RegressionSkip extends RuntimeException
{
}

function envValue(string $name, string $default = ''): string
{
    $value = getenv($name);
    return $value === false ? $default : $value;
}

function requireEnv(string $name): string
{
    $value = envValue($name);
    if ($value === '') {
        throw new RegressionSkip("Set {$name} to run this test.");
    }
    return $value;
}

function assertTrue($condition, string $message): void
{
    if (!$condition) {
        throw new RegressionFailure($message);
    }
}

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RegressionFailure(
            $message . ' Expected: ' . var_export($expected, true)
            . '; actual: ' . var_export($actual, true)
        );
    }
}

function appUrl(string $path = ''): string
{
    return rtrim(envValue('REGRESSION_BASE_URL', 'http://localhost/SSD/Secure-Student-Management-System'), '/')
        . '/' . ltrim($path, '/');
}

function httpRequest(string $path, array $post = [], array $cookies = []): array
{
    $headers = ['User-Agent: Secure-Student-Management-System regression tests'];
    if ($cookies) {
        $headers[] = 'Cookie: ' . implode('; ', array_map(
            static function (string $key, string $value): string {
                return $key . '=' . $value;
            },
            array_keys($cookies),
            $cookies
        ));
    }

    $options = [
        'http' => [
            'method' => $post ? 'POST' : 'GET',
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
            'follow_location' => 0,
            'content' => $post ? http_build_query($post) : '',
        ],
    ];
    if ($post) {
        $options['http']['header'] .= "\r\nContent-Type: application/x-www-form-urlencoded";
    }

    $context = stream_context_create($options);
    $body = @file_get_contents(appUrl($path), false, $context);
    $responseHeaders = $http_response_header ?? [];
    $responseCookies = $cookies;
    foreach ($responseHeaders as $header) {
        if (stripos($header, 'Set-Cookie:') === 0
            && preg_match('/Set-Cookie:\s*([^=]+)=([^;]*)/i', $header, $matches)) {
            $responseCookies[$matches[1]] = $matches[2];
        }
    }

    preg_match('/^HTTP\/\S+\s+(\d+)/', $responseHeaders[0] ?? '', $status);
    preg_match('/^Location:\s*(.+)$/im', implode("\n", $responseHeaders), $location);

    return [
        'status' => (int) ($status[1] ?? 0),
        'body' => $body === false ? '' : $body,
        'headers' => $responseHeaders,
        'cookies' => $responseCookies,
        'location' => trim($location[1] ?? ''),
    ];
}

function csrfToken(string $html): string
{
    assertTrue(
        preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $matches) === 1,
        'The page did not contain a CSRF token.'
    );
    return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
}

function login(array $credentials): array
{
    $page = httpRequest('login.php');
    $credentials['csrf_token'] = csrfToken($page['body']);
    $credentials['submit'] = 'submit';
    return httpRequest('login.php', $credentials, $page['cookies']);
}

function database(): mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF);
    $db = new mysqli(
        envValue('REGRESSION_DB_HOST', '127.0.0.1'),
        envValue('REGRESSION_DB_USER', 'root'),
        envValue('REGRESSION_DB_PASSWORD'),
        envValue('REGRESSION_DB_NAME', 'student-management-system'),
        (int) envValue('REGRESSION_DB_PORT', '3306')
    );
    assertTrue(!$db->connect_errno, 'Database connection failed: ' . $db->connect_error);
    return $db;
}

function source(string $file): string
{
    $path = REGRESSION_ROOT . '/' . ltrim($file, '/');
    assertTrue(is_file($path), "Source file not found: {$file}");
    return (string) file_get_contents($path);
}

