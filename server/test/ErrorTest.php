<?php
require_once("./server/test/testConfig.php");
require_once("./server/src/CError.php");

class ErrorTest extends PHPUnit_Framework_TestCase
{
    /**
     * 登録された
     * JSON文字列を表示するテスト
     */
    public function testViewResult() {
        $data = '{"clientWidth":1080,"clientHeight":25,"navigator":{"doNotTrack":"unspecified","oscpu":"Intel Mac OS X 10.11","productSub":"20100101","cookieEnabled":true,"buildID":"20160315153207","appCodeName":"Mozilla","appName":"Netscape","appVersion":"5.0 (Macintosh)","platform":"MacIntel","userAgent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0","product":"Gecko","language":"ja","onLine":true}}';
        $obj = json_decode($data);

        $str = print_r($obj, true);
        echo "data=".$str;
    }
}
 ?>
