<?php
/** Issue 18用テスト*/

namespace SeleniumTest;

use Am1\Utils\CError;
use Am1\Utils\CObserveAccess;
use Am1\Utils\ErrorTable;
use Am1\Utils\InvalidAccessTable;
use Am1\Utils\NGIPsTable;

/** Seleniumの継承*/
class Selenium18Test extends \PHPUnit_Extensions_Selenium2TestCase
{
    const DOMAIN = 'http://0.0.0.0:8080';
    const OBSERVE_URL = '/invalid-access';
    private static $cerror = null;
    private static $cobserve = null;
    private $settings = '';

    protected function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl(self::DOMAIN);

        // 初期化
        $this->settings = require __DIR__.'/../src/settings.php';

        // クラスを初期化
        if (self::$cerror == null) {
            self::$cerror = new CError($this->settings['settings']);
        }
        if (self::$cobserve == null) {
            self::$cobserve = new CObserveAccess(
                ['ADMIN_EMAIL' => ADMIN_EMAIL,
                    'FROM_EMAIL' => SYS_EMAIL,
                ]
            );
        }
    }

    /**
     * エラーを登録
     * 戻り値 $res を以下で成功を試せる
     *   $this->assertRegExp('/200/', $res['http_response_header'][0]);
     * @param string $mes 登録するメッセージ。エラーを変更したい時に指定。省略可能
     * @return 戻り値
     */
    public function entryError($mes) {
        // エラー登録
        $data = '{"clientWidth":1080,"clientHeight":25,';
        $data .= '"mes":"'.$mes.'",';
        $data .= '"navigator":{"doNotTrack":"unspecified",';
        $data .= '"oscpu":"Intel Mac OS X 10.11","productSub":"20100101",';
        $data .= '"cookieEnabled":true,"buildID":"20160315153207",';
        $data .= '"appCodeName":"Mozilla","appName":"Netscape",';
        $data .= '"appVersion":"5.0 (Macintosh)","platform":"MacIntel",';
        $data .= '"userAgent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0",';
        $data .= '"product":"Gecko","language":"ja","onLine":true}}';

        return $this->postUrl(array(
            'description' => $data
        ));
    }

    /**
     * POST送信
     *
     * @param array $sendarray 送信する連想配列
     *
     * @return array response=戻ってきたページの情報 / http_response_header=レスポンスヘッダ
     */
    private function postUrl($sendarray)
    {
        $data_url = http_build_query($sendarray);
        $data_len = strlen($data_url);

        $res = file_get_contents(
            ERROR_ROOT,
            false,
            stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: $data_len\r\n",
                    'content' => $data_url,
                ),
            ))
        );

        return array(
            'response' => $res,
            'http_response_header' => $http_response_header,
        );
    }

    /** 普通にエラー登録をして成功することと、NGリストに登録後に登録できなくなることを確認*/
    public function testEntryErrorNG()
    {
        // エラー数を確認
        $errnum = ErrorTable::all()->count();

        // エラーをSeleniumで登録
        $res = $this->entryError('0');

        // エラーが増えていることを確認
        $this->assertEquals($errnum+1, ErrorTable::all()->count(), 'check increment error.');

        // NG登録
        self::$cobserve->entryNGListWithHost('127.0.0.1');

        // 再度エラー登録
        $this->entryError('1');
        // エラー数が変化しないことを確認
        $this->assertEquals($errnum+1, ErrorTable::all()->count(), 'check equal error.');
    }


}
