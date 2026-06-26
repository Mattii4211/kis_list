<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Ensure test env is applied when PHPUnit boots the kernel.
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
putenv('APP_ENV=test');

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = 'sqlite:///:memory:';
putenv('DATABASE_URL=sqlite:///:memory:');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
