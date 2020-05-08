<?php
/**
 * 本番サーバー用の設定.
 *
 * @copyright 2016 YuTanaka@AmuseOne
 */
return [
    'settings' => [
        'app' => require __DIR__.'/settings-app.php',

        'displayErrorDetails' => false, // set to false in production

        // Twig settings
        'view' => [
            'template_path' => __DIR__.'/../templates/',
            'options' => [
                'cache' => __DIR__.'/../cache/',
            ],
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__.'/../logs/app.log',
        ],

        // Illuminate Database settings
        'db' => [
            'driver' => 'mysql',
            'host' => DB_HOST,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
        ],
    ],
];
