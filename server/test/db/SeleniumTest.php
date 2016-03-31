<?php

// TODO: CError/CObserveAccessを組み込み
require_once(__DIR__."/../../src/am1/utils/am1util.php");
require_once(__DIR__."/../../src/am1/utils/cerror.php");
require_once(__DIR__."/../../src/am1/utils/cobserve-access.php");

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Am1\Utils\Am1Util;
use Am1\Utils\CError;
use Am1\Utils\CObserveAccess;
use Am1\Utils\InvalidAccessTable;
use Am1\Utils\NGIPsTable;

/** Seleniumの継承*/
class WebTest extends PHPUnit_Extensions_Selenium2TestCase {
    const DOMAIN = "http://0.0.0.0:8080";
    const OBSERVE_URL = "/invalid-access";
    static $cerror = null;
    static $cobserve = null;
    var $settings = "";

    protected function setUp() {
        $this->setBrowser('firefox');
        $this->setBrowserUrl(self::DOMAIN);

        // 初期化
        $this->settings = require __DIR__ . '/../../src/settings.php';

        // クラスを初期化
        if (self::$cerror == null) {
            self::$cerror = new CError($this->settings['settings']['db']['config']);
        }
        if (self::$cobserve == null) {
            self::$cobserve = new CObserveAccess(
                [   "ADMIN_EMAIL"=> ADMIN_ADDR,
                    "FROM_EMAIL"=> SYS_ADDR
                ]
            );
        }
    }

    /** 一時停止の処理*/
    public function testReleaseInvalidList() {
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
    public function _testInvalidReleaseInvalidList() {
        // 失敗
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->assertEquals('ok', $this->byId('info')->text());
    }

}

?>
