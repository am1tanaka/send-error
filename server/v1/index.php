<?php
require_once __DIR__.'/../src/config/config-common.php';

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__.$_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }

    require __DIR__.'/../src/config/config-mac.php';
    $settings = require __DIR__.'/../src/settings.php';
} else {
    require __DIR__.'/../src/config/config.php';
    $settings = require __DIR__.'/../src/settings-rel.php';
}

session_start();

require __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/am1/utils/am1util.php';
require_once __DIR__.'/../src/am1/utils/cerror.php';
require_once __DIR__.'/../src/am1/utils/cobserve-access.php';
require_once __DIR__.'/../src/am1/utils/ErrorTable.php';
require_once __DIR__.'/../src/am1/utils/InvalidAccessTable.php';
require_once __DIR__.'/../src/am1/utils/NGIPsTable.php';

// Instantiate the app
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__.'/../src/dependencies.php';

// Register middleware
require __DIR__.'/../src/middleware.php';

// Register routes
require __DIR__.'/../src/routes.php';

// Run app
$app->run();
