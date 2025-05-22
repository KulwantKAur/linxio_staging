<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;



require dirname(__DIR__).'/config/bootstrap.php';

$env = getenv('SYMFONY_ENV') ?: 'prod';
$debug = false;
if ($env == 'dev' || $env == 'test' || $env == 'stage') {
    Debug::enable();
    $debug = true;
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['SYMFONY_ENV'], (bool) $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->headers->set('Access-Control-Allow-Origin', '*');
$response->send();
$kernel->terminate($request, $response);
