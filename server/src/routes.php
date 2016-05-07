<?php

/*
require_once './src/am1/utils/cerror.php';
require_once './src/am1/utils/cobserve-access.php';
require_once './src/am1/error-middleware.php';
*/

// Routes

// エラー処理
/* エラーの登録*/
$app->post('/error', function ($request, $response, $args) {
    // エラーを初期化
    $this->utils_error;

    // リモートホストがNGリストの場合は却下
    if ($this->util_observe_access->isNG($_SERVER['REMOTE_ADDR']) > 0) {
        return $this->view->render($response, 'info.html', [
            'info' => 'ok(1)',
        ]);
    }

    // パラメーター不足
    if (!array_key_exists('description', $_POST)) {
        // パラメーター不足
        $this->util_observe_access->entryInvalidAccess(
            $_SERVER['REMOTE_ADDR'],
            $this->settings['app']['SERVICE_NAME'],
            'Invalid Parameter.'
        );

        return $this->view->render($response, 'info.html', [
            'info' => 'ok',
        ]);
    }

    // jsonかチェック
    if (is_null(json_decode($_POST['description']))) {
        // データが不正
        $this->util_observe_access->entryInvalidAccess(
            $_SERVER['REMOTE_ADDR'],
            $this->settings['app']['SERVICE_NAME'],
            'Invalid JSON.'
        );

        return $this->view->render($response, 'info.html', [
            'info' => 'ok',
        ]);
    }

    /*
    // ハッシュの不一致
    $hash = hash('crc32', $_POST['description']);
    if ($hash !== $_POST['hash']) {
        // ハッシュが不一致
        $this->util_observe_access->entryInvalidAccess(
            $_SERVER['REMOTE_ADDR'],
            $this->settings['app']['SERVICE_NAME'],
            'Not Match Hash. Expected='.$hash.'/Sended='.$_POST['hash']
        );

        return $this->view->render($response, 'info.html', [
            'info' => 'ok'
        ]);
    }
    */

    // 登録
    if ($this->utils_error->entryErrorData($_POST['description'])) {
        $this->util_observe_access->releaseInvalidHost($_SERVER['REMOTE_ADDR'], $this->settings['app']['SERVICE_NAME']);
    }

    return $this->view->render(
        $response,
        'info.html',
        [
            'info' => 'entry ok.',
        ]
    );
});

/* エラーの参照*/
$app->get('/error/{key}', function ($request, $response, $args) {
    // キーのデータを取得
    $desc = $this->utils_error->getDescriptionArrayFromDB($args['key']);
    if ($desc === false) {
        // キーが見当たらないのでアクセス失敗に登録
        $this->util_observe_access->entryInvalidAccess(
            $_SERVER['REMOTE_ADDR'],
            $this->settings['app']['SERVICE_NAME'],
            'Invalid Key: '.$args['key']
        );
        //
        return $this->view->render($response, 'info.html', [
            'info' => 'ok',
        ]);
    }

    // 詳細を画面に出力
    return $this->view->render($response, 'view.html', [
        'datas' => $desc,
    ]);
});

/* エラーの削除*/
$app->get('/error/{key}/delete', function ($request, $response, $args) {
    $count = $this->utils_error->deleteDataFromDB($args['key']);
    if ($count == 0) {
        // キーが不正
        $this->util_observe_access->entryInvalidAccess(
            $_SERVER['REMOTE_ADDR'],
            $this->settings['app']['SERVICE_NAME'],
            'Invalid Key: '.$args['key']
        );

        return $this->view->render($response, 'info.html', [
            'info' => 'ok',
        ]);
    }

    // 成功
    return $this->view->render($response, 'info.html', [
        'info' => '指定のエラーを削除しました。',
    ]);
});

// 不正アクセスAPI
/* 一時停止を解除する*/
$app->get('/invalid-access/{key}/release', function ($request, $response, $args) {
    // エラーを初期化
    $this->utils_error;

    $res = $this->util_observe_access->releaseInvalidAccess($args['key'], $_SERVER['REMOTE_ADDR']);
    if ($res == 0) {
        // 失敗
        return $this->view->render($response, 'info.html', [
            'info' => 'ok',
        ]);
    }
    // 成功
    return $this->view->render($response, 'info.html', [
        'info' => '指定のホストの一時停止を解除しました。',
    ]);
});

/* NGリストに登録する*/
$app->get('/invalid-access/{key}/ng', function ($request, $response, $args) {
    // エラーを初期化
    $this->utils_error;

    // NGリストへの追加処理
    $res = $this->util_observe_access->entryNGList($args['key'], $_SERVER['REMOTE_ADDR']);

    // 呼び出されたキーのホストを取り出す
    if ($res === false) {
        // 失敗
        return $this->view->render($response, 'info.html', [
            'info' => 'ok',
        ]);
    }
    // 成功
    return $this->view->render($response, 'info.html', [
        'info' => '指定のホスト['.$res.']をNGリストに登録しました。',
    ]);
});

/* NGリストから指定のキーを削除*/
$app->get('/invalid-access/ng/{key}/release', function ($request, $response, $args) {
    // エラーを初期化
    $this->utils_error;

    // NG解除処理
    $res = $this->util_observe_access->releaseNGList($args['key'], $_SERVER['REMOTE_ADDR']);

    // 呼び出されたキーのホストを取り出す
    if ($res === false) {
        // 失敗
        return $this->view->render($response, 'info.html', [
            'info' => 'ok',
        ]);
    }
    // 成功
    return $this->view->render($response, 'info.html', [
        'info' => '指定のホスト['.$res.']のNGを解除しました。',
    ]);
});

/* テスト*/
$app->get('/test', function ($request, $respone, $args) {
    $data = '{"clientWidth":1080,"clientHeight":25,"doNotTrack":"unspecified",';
    $data .= '"oscpu":"Intel Mac OS X 10.11","productSub":"20100101",';
    $data .= '"cookieEnabled":true,"buildID":"20160315153207",';
    $data .= '"appCodeName":"Mozilla","appName":"Netscape",';
    $data .= '"appVersion":"5.0 (Macintosh)","platform":"MacIntel",';
    $data .= '"userAgent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0",';
    $data .= '"product":"Gecko","language":"ja","onLine":true}';

    return $this->view->render($respone, 'view.html', [
        'datas' => $this->utils_error->convJSON2Array($data),
    ]);
});

/* アプリ側*/
$app->get('/app', function ($request, $response, $args) {
    return $this->view->render($response, 'testapp.html', [
        'error_url' => ERROR_ROOT
    ]);
});
