<?php
// DIC configuration

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($c) {
    $settings = $c->get('settings')['view'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        $settings['options']
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c['router'],
        $c['request']->getUri()
    ));

    return $view;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// CError
$container['util_error'] = function ($c) {
    $settings = $c->get('settings')['db'];

    // クラスを初期化
    $error = new Am1\Utils\CError($settings['config']);
    return $error;
};

// CObserveAccess
$container['util_observe_access'] = function ($c) {
    $obs = new Am1\Utils\CObserveAccess([
        "ADMIN_EMAIL" => ADMIN_EMAIL,    // 管理者メールアドレス
        "FROM_EMAIL" => SYS_EMAIL     // 送信元メールアドレス
    ]);
    return $obs;
};
