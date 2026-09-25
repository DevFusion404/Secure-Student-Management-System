<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$client = new Google\Client();


$client->setClientId(
    $_ENV['GOOGLE_CLIENT_ID']
);


$client->setClientSecret(
    $_ENV['GOOGLE_CLIENT_SECRET']
);


$client->setRedirectUri(
    $_ENV['GOOGLE_REDIRECT_URI']
);


$client->addScope([
    "openid",
    "email",
    "profile"
]);

?>