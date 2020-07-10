<?php

namespace Vgsite;

define('ROOT_DIR', realpath(__DIR__ . '/..'));
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('CACHE_DIR', ROOT_DIR . '/var/cache');
define('LOGS_DIR', ROOT_DIR . '/var/logs');

// Load environmental variables
$dotenv = \Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
$dotenv->required(['ENVIRONMENT', 'HOST_DOMAIN', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_NAME_MAIN']);
