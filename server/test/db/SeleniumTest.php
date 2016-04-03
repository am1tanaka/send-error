<?php

namespace SeleniumTest;

use Am1\Utils\CError;
use Am1\Utils\CObserveAccess;
use Am1\Utils\ErrorTable;
use Am1\Utils\InvalidAccessTable;
use Am1\Utils\NGIPsTable;

/** Seleniumの継承*/
class WebTest extends \PHPUnit_Extensions_Selenium2TestCase
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
        $this->settings = require __DIR__.'/../../src/settings.php';

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

    /** 一時停止の処理*/
    public function xtestReleaseInvalidList()
    {
        // 無効なアクセスを登録
        self::$cobserve->entryInvalidAccess('localhost', 'SeleniumTest', 'errormess');
        self::$cobserve->entryInvalidAccess('localhost', 'SeleniumTest', 'errormess');
        self::$cobserve->entryInvalidAccess('localhost', 'SeleniumTest', 'errormess');
        self::$cobserve->entryInvalidAccess('localhost', 'SeleniumTest', 'errormess');

        // キーコードを取得
        $res = InvalidAccessTable::where('remote_host', 'like', 'localhost')->limit(1)->get();
        $key = $res[0]->keycode;

        // 削除発行
        $this->url(self::DOMAIN.self::OBSERVE_URL."/$key/release");
        $this->assertStringStartsWith('指定のホストの', $this->byId('info')->text());
    }

    /** 一時停止の無効な解放による一時停止*/
    public function xtestInvalidReleaseInvalidList()
    {
        // 失敗
        $this->url(self::DOMAIN.self::OBSERVE_URL.'/xxxx/release');
        $this->url(self::DOMAIN.self::OBSERVE_URL.'/xxxx/release');
        $this->url(self::DOMAIN.self::OBSERVE_URL.'/xxxx/release');
        $this->url(self::DOMAIN.self::OBSERVE_URL.'/xxxx/release');
        $this->url(self::DOMAIN.self::OBSERVE_URL.'/xxxx/release');
        $this->assertEquals('ok', $this->byId('info')->text());
    }

    /**
     * @group cobserve
     * NGリストへの登録呼び出しのテスト
     */
    public function xtestEntryNG()
    {
        // 一時停止にまずは登録
        self::$cobserve->entryInvalidAccess('localhost', 'SeleniumTest', 'testEntryNG');
        // localhostのキーを取り出す
        $row = InvalidAccessTable::where('remote_host', 'like', 'localhost')->get()[0];
        $key = $row->keycode;

        // NGリストを全て削除
        if (NGIPsTable::all()->count() > 0) {
            NGIPsTable::all()->delete();
        }
        $this->url(self::DOMAIN.self::OBSERVE_URL."/$key/ng");

        // 登録されていることを確認
        $this->assertStringStartsWith('指定のホスト', $this->byId('info')->text());
    }

    /**
     * @group cobserve
     * 不正なNGリスト登録の呼び出しテスト
     */
    public function xtestInvalidEntryNG()
    {
        // 現在のアクセス失敗回数を数える
        $before = InvalidAccessTable::all()->count();

        $key = 'ngkey';
        $this->url(self::DOMAIN.self::OBSERVE_URL."/$key/ng");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/$key/ng");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/$key/ng");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/$key/ng");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/$key/ng");

        // 5つふえていることを確認
        $after = InvalidAccessTable::all()->count();
        $this->assertEquals($before + 5, $after);
    }

    /**
     * @group cobserve
     * NGを解除するテスト
     */
    public function xtestReleaseNG()
    {
        $key = '';

        // NGがあるか
        if (NGIPsTable::all()->count() == 0) {
            self::$cobserve->entryNGListWithHost('localhost');
        }

        // NGを登録
        $this->assertEquals(1, NGIPsTable::all()->count());
        $key = NGIPsTable::get()[0]->keycode;

        // NGを解除
        $this->url(self::DOMAIN.self::OBSERVE_URL."/ng/$key/release");

        // 成功チェック
        $this->assertStringStartsWith('指定のホスト', $this->byId('info')->text());
    }

    /**
     * @group cobserve
     * 無効なキーでNGを繰り返して一時停止させるテスト
     */
    public function xtestInvalidReleaseNG()
    {
        // 現在のアクセス失敗回数を数える
        $before = InvalidAccessTable::all()->count();

        $key = 'invalidkey';

        $this->url(self::DOMAIN.self::OBSERVE_URL."/ng/$key/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/ng/$key/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/ng/$key/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/ng/$key/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/ng/$key/release");

        // 5つふえていることを確認
        $after = InvalidAccessTable::all()->count();
        $this->assertEquals($before + 5, $after);
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

    /**
     * @group error_test
     * エラーを登録するテスト
     */
    public function testEntryError()
    {
        $data = '{"clientWidth":1080,"clientHeight":25,';
        $data .= '"navigator":{"doNotTrack":"unspecified",';
        $data .= '"oscpu":"Intel Mac OS X 10.11","productSub":"20100101",';
        $data .= '"cookieEnabled":true,"buildID":"20160315153207",';
        $data .= '"appCodeName":"Mozilla","appName":"Netscape",';
        $data .= '"appVersion":"5.0 (Macintosh)","platform":"MacIntel",';
        $data .= '"userAgent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0",';
        $data .= '"product":"Gecko","language":"ja","onLine":true}}';

        $hash = hash('crc32', $data);

        $res = $this->postUrl(array(
            'description' => $data,
            'hash' => $hash,
        ));

        $this->assertRegExp('/200/', $res['http_response_header'][0]);
    }

    /**
     * @group error_test
     * エラーの失敗チェック
     */
    public function xtestInvalidEntryError()
    {
        // データを含まない
        $res = $this->postUrl(array(
            'another1' => '',
        ));
        $this->assertRegExp('/200/', $res['http_response_header'][0]);

        // JSON以外を送信
        $data = 'abc';
        $res = $this->postUrl(array(
            'description' => $data,
            'hash' => hash('crc32', $data),
        ));
        $this->assertRegExp('/200/', $res['http_response_header'][0]);

        // 無効なHASHを送信
        $data = '{"data": 1080}';
        $res = $this->postUrl(array(
            'description' => $data,
            'hash' => hash('crc32', 'abc'),
        ));
        $this->assertRegExp('/200/', $res['http_response_header'][0]);
    }

    /**
     * 登録されているエラーデータの最初のキーを返す.
     *
     * @return データがあった� �合はstringでキーコード。ない場合はfalse
     */
    private function getErrorKey()
    {
        if (ErrorTable::count() > 0) {
            return ErrorTable::first()->keycode;
        }

        return false;
    }

    /**
     * @depends testEntryError
     * @group localtest
     * 参照テスト
     */
    public function testView()
    {
        // 登録されているキーを一つ取得
        $key = $this->getErrorKey();
        $this->assertNotFalse($key, 'error depend.');

        // 表示アクセス
        $this->url(ERROR_ROOT."/$key");

        // チェック
        $this->assertEquals('ErrorViewer', $this->title());
    }

    /**
     * エラーテスト.
     */
    public function testInvalidKeyError()
    {
        // 無効なキーで呼び出し
        $this->url(ERROR_ROOT.'/invalid_key');

        // 戻り値がOKなら成功
        $this->assertEquals('ok', $this->byId('info')->text());
    }
}
