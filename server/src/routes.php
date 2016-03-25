<?php
require_once("./src/am1/utils/cerror.php");

// Routes

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
