<?php
/** mac/開発用サーバー用の設定
 * @copyright 2016 YuTanaka@AmuseOne
 */
return [
    'settings' => [
        // アプリ用共通設定
        'app' => require __DIR__.'/settings-app.php',

        // エラー状況を表示
        'displayErrorDetails' => true, // set to false in production

        // Twig settings
        'view' => [
            'template_path' => __DIR__.'/../templates/',
            'options' => [
                'debug' => true,
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
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ],
    ],
];
