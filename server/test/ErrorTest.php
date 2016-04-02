<?php
/**
 * エラー for PHP クラスのテスト.
 */
namespace ErrorTest;

//require_once("./server/test/testConfig.php");
//require_once("./server/src/am1/utils/cerror.php");
//require_once("./server/vendor/autoload.php");
//require __DIR__ . '/../vendor/autoload.php';

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * 登録された
     * JSON文字列を表示するテスト.
     */
    public function testViewResult()
    {
        /*
        $app = new \Slim\App();

        $container = createContainer();

        $data = '{"clientWidth":1080,"clientHeight":25,';
        $data .= '"navigator":{"doNotTrack":"unspecified",';
        $data .= '"oscpu":"Intel Mac OS X 10.11","productSub":"20100101",';
        $data .= '"cookieEnabled":true,"buildID":"20160315153207",';
        $data .= '"appCodeName":"Mozilla","appName":"Netscape",';
        $data .= '"appVersion":"5.0 (Macintosh)","platform":"MacIntel",';
        $data .= '"userAgent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0",';
        $data .= '"product":"Gecko","language":"ja","onLine":true}}';
        $obj = json_decode($data);

        $str = print_r($obj, true);
        echo "data=".$str;
        */
    }
}
