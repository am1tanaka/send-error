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
                'cache' => __DIR__ . '/../cache/'
            ]
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],
    ],
];
