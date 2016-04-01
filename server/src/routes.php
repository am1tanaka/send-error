<?php
require_once("./src/am1/utils/cerror.php");
require_once("./src/am1/utils/cobserve-access.php");

// Routes

// 不正アクセスAPI
/** 一時停止を解除する*/
$app->get('/invalid-access/{key}/release', function($request, $response, $args) {
    // エラーを初期化
    $this->util_error;

    $res = $this->util_observe_access->releaseInvalidAccess($args['key'], $_SERVER['REMOTE_ADDR']);
    if ($res == 0) {
        // 失敗
        return $this->view->render($response, 'info.html', [
            'info' => 'ok'
        ]);
    }
    // 成功
    return $this->view->render($response, 'info.html', [
        'info' => '指定のホストの一時停止を解除しました。'
    ]);
});

/** NGリストに登録する*/
$app->get('/invalid-access/{key}/ng', function($request, $response, $args) {
    // エラーを初期化
    $this->util_error;

    // 削除処理
    $res = $this->util_observe_access->entryNGList($args['key'], $_SERVER['REMOTE_ADDR']);

    // 呼び出されたキーのホストを取り出す
    if ($res === false) {
        // 失敗
        return $this->view->render($response, 'info.html', [
            'info' => 'ok'
        ]);
    }
    // 成功
    return $this->view->render($response, 'info.html', [
        'info' => '指定のホスト['.$res.']をNGリストに登録しました。'
    ]);
});

/** テスト*/
$app->get('/test', function($request, $respone, $args) {
    $data = '{"clientWidth":1080,"clientHeight":25,"doNotTrack":"unspecified","oscpu":"Intel Mac OS X 10.11","productSub":"20100101","cookieEnabled":true,"buildID":"20160315153207","appCodeName":"Mozilla","appName":"Netscape","appVersion":"5.0 (Macintosh)","platform":"MacIntel","userAgent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0","product":"Gecko","language":"ja","onLine":true}';

    return $this->view->render($respone, 'view.html', [
        'datas' => $this->utils_error->convJSON2Array($data)
    ]);
});

/** キーを指定して該当するデータがあれば表示する*/
$app->get('/{key}', function($request, $response, $args) {
    return $this->view->render($response, 'view.html', [
        'key' => $args['key']
    ]);
})->setName('view');
