<?php
return [
    'settings' => [
        'app' => require __DIR__ . '/settings-app.php',

        'displayErrorDetails' => true, // set to false in production

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Twig settings
        'view' => [
            'template_path' => __DIR__ . '/../templates/',
            'options' => [
                'debug' => true,
            ]
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],

        // Illuminate Database settings
        'db' => [
            'config' => [
                'driver' => 'mysql',
                'host' => DB_HOST,
                'database' => DB_NAME,
                'username' => DB_USER,
                'password' => DB_PASS,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => ''
            ],
        ]
    ],
];
