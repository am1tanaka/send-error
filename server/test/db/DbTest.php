<?php
/**
 * DBのテスト.
 */
namespace DbTest;

use Am1\Utils\Am1Util;
use Am1\Utils\CError;
use Am1\Utils\CObserveAccess;
use Am1\Utils\InvalidAccessTable;
use Am1\Utils\NGIPsTable;

class DbTest extends \PHPUnit_Extensions_Database_TestCase
{
    public static $pdo = null;
    private $pdo_conn = null;
    private $settings;
    private static $cerror = null;
    private static $cobserve = null;

    /**
     * TestCaseからデータベースへの接続.
     */
    public function getConnection()
    {
        if ($this->pdo_conn == null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO(
                    'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8',
                    TEST_DB_USER,
                    TEST_DB_PASS
                );
            }

            $this->pdo_conn = $this->createDefaultDBConnection(self::$pdo);
        }

        return $this->pdo_conn;
    }

    /**
     * テスト用のデータセットを作成する。テストごとに実行.
     */
    public function getDataSet()
    {
        // 初期化
        $this->settings = require __DIR__.'/../../src/settings.php';

        // クラスを初期化
        if (self::$cerror == null) {
            self::$cerror = new CError($this->settings['settings']);
        }
        if (self::$cobserve == null) {
            self::$cobserve = new CObserveAccess(
                [
                    'ADMIN_EMAIL' => ADMIN_EMAIL,
                    'FROM_EMAIL' => SYS_EMAIL,
                ]
            );
        }

        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            __DIR__.'/init-error-data.yml'
        );
    }

    public function testOne() {
        
    }

    /**
     * @group cerror
     * データ登録
     * 指定の文字列をデータベースに登録する
     */
    public function xtestDataEntry()
    {
        // テストデータを読み込む
        $data = file_get_contents(__DIR__.'/entry-test-data.json');

        // データ登録
        self::$cerror->entryErrorData($data, '0123456789112345');

        // 登録成功チェック
        $this->assertEquals(2, $this->getConnection()->getRowCount('error_data'));

        // 成功チェック
        $succ = self::$cerror->getDescriptionArrayFromDB('0123456789112345');
        $this->assertEquals(1080, $succ['clientWidth']);
    }

    /**
     * @group cerror
     * データの取得
     * メールにCSVを添付して送信
     */
    public function xtestGetKey()
    {
        // エラーチェック
        $fail = self::$cerror->getDescriptionArrayFromDB('0');
        $this->assertFalse($fail);

        // 成功チェック
        $succ = self::$cerror->getDescriptionArrayFromDB('0123456789abcdef');
        $this->assertNotFalse($succ);
        $this->assertEquals(1080, $succ['clientWidth']);
    }

    /**
     * @group cerror
     * データの削除
     */
    public function xtestDelete()
    {
        $conn = $this->getConnection();

        // 最初のデータ個数をチェック
        $this->assertEquals(1, $conn->getRowCount('error_data'));

        // エラーチェック
        $fail = self::$cerror->deleteDataFromDB('0');
        $this->assertEquals(0, $fail);

        // データを削除
        $succ = self::$cerror->deleteDataFromDB('0123456789abcdef');
        $this->assertEquals(1, $succ);

        // 残り個数をチェック
        $this->assertEquals(0, $conn->getRowCount('error_data'));
    }

    /**
     * @group cobserve
     * 失敗の報告
     */
    public function xtestEntryInvalidAccess()
    {
        // エラー許容
        for ($i = 0; $i < 4; ++$i) {
            $res = self::$cobserve->entryInvalidAccess(
                'localhost',
                'DbTest',
                'error'
            );
            $this->assertTrue($res, 'loop:'.$i);
        }

        // 一時停止&報告
        $res = self::$cobserve->entryInvalidAccess(
            'localhost',
            'DbTest',
            'error'
        );
        $this->assertFalse($res, 'pause and sendmail');

        // 一時停止&報告なし
        $res = self::$cobserve->entryInvalidAccess(
            'localhost',
            'DbTest',
            'error'
        );
        $this->assertFalse($res, 'pause only');
    }

    /**
     * @group cobserve
     * カラムより大きいデータを送付した時の動作確認
     */
    public function xtestLongData()
    {
        // 送信
        for ($i = 0; $i < 5; ++$i) {
            $res = self::$cobserve->entryInvalidAccess(
                '0123456789112345678921234567893123456789412345678951234567896123456789',
                '0123456789112345678921234567893123456789412345678951234567896123456789',
                '0123456789112345678921234567893123456789412345678951234567896123456789'
                .'0123456789112345678921234567893123456789412345678951234567896123456789'
                .'0123456789112345678921234567893123456789412345678951234567896123456789'
                .'0123456789112345678921234567893123456789412345678951234567896123456789'
                .'0123456789112345678921234567893123456789412345678951234567896123456789'
            );
        }
    }

    /**
     * SQLインジェクションができるかをチェック
     * http://d.hatena.ne.jp/muggles0812/20120701.
     */
    public function xtestSecurity()
    {
        for ($i = 0; $i < 5; ++$i) {
            $res = self::$cobserve->entryInvalidAccess(
                'localhost',
                '"',
                ' order by 1'
            );
        }
    }

    /**
     * @group cobserve
     * アクセス失敗の解除テスト
     */
    public function xtestReleaseInvalidAccess()
    {
        // エラーの登録
        for ($i = 0; $i < 3; ++$i) {
            self::$cobserve->entryInvalidAccess(
                'localhost',
                'DbTest',
                'error'
            );
        }

        // キーを取り出す
        $row = InvalidAccessTable::where('remote_host', 'like', 'localhost');
        $this->assertEquals(3, $row->count(), 'get key.');

        // 成功
        $keycode = $row->take(1)->get();
        $res = self::$cobserve->releaseInvalidAccess($keycode[0]->keycode, 'remotehost');
        $this->assertEquals(3, $res, 'delete response.');

        // DBの中身をチェック
        $end = InvalidAccessTable::all()->count();
        $this->assertEquals(0, $end, 'delete complete check.');
    }

    /**
     * 不正な解放ミスを繰り返して、ホストが停止するかを確認.
     */
    public function xtestReleaseMissReport()
    {
        for ($i = 0; $i < 4; ++$i) {
            $res = self::$cobserve->releaseInvalidAccess('invalid', 'remotehost');
            $this->assertEquals(0, $res);
        }

        // エラー報告
        self::$cobserve->releaseInvalidAccess('invalid', 'remotehost');
    }

    /**
     * @group cobserve
     * 指定のHOSTがNGリストにあるかを確認
     */
    public function xtestIsNG()
    {
        $res = self::$cobserve->isNG('localhost');
        $this->assertEquals(0, $res);
    }

    /**
     * @group cobserve
     * NGリストの登録テスト
     */
    public function xtestNG()
    {
        $res = self::$cobserve->isNG('localhost');
        $this->assertEquals(0, $res, 'check before set ng.');

        self::$cobserve->entryNGList('localhost');
        $res = self::$cobserve->isNG('localhost');
        $this->assertEquals(1, $res, 'check after set ng.');
        $update = NGIPsTable::where('remote_host', 'like', 'localhost')->get();

        sleep(1);

        self::$cobserve->entryNGList('localhost');
        $res = self::$cobserve->isNG('localhost');
        $this->assertEquals(1, $res, 'check double set ng.');
        $update2 = NGIPsTable::where('remote_host', 'like', 'localhost')->get();
        $this->assertNotEquals($update[0]->updated_at, $update2[0]->updated_at, 'check update.');

        $res = self::$cobserve->isNG('another');
        $this->assertEquals(0, $res, 'check another host ng.');
    }

    /**
     * @group cobserve
     * NGリストの解除テスト
     */
    public function xtestReleaseNG()
    {
        // 登録
        self::$cobserve->entryNGList('localhost');
        $res = self::$cobserve->isNG('localhost');
        $this->assertEquals(1, $res, 'check entry ng success.');

        // キーコードを取得
        $key = NGIPsTable::where('remote_host', 'like', 'localhost')->get()[0];

        // 削除
        self::$cobserve->releaseNGList($key->keycode, 'remote_host');
        $res = self::$cobserve->isNG('localhost');
        $this->assertEquals(0, $res, 'check release ng list.');
    }

    /**
     * @group cobserve
     * 不正なNGリストの登録リクエストの要求をブロックするテスト
     * メールが送信されればOK
     */
    public function xtestInvalidReleaseNG()
    {
        // 不正な要求
        for ($i = 0; $i < 5; ++$i) {
            self::$cobserve->entryNGList('none', 'remote_host');
        }
    }

    /**
     * データの登録テスト.
     */
    public function xtestInsert()
    {
        // 追加
        $err = new ErrorTable();
        $err->keycode = Am1Util::makeRandWords($this->settings['settings']['app']['KEYCODE_LENGTH']);
        $err->description = 'YuTanaka';
        $err->save();

        // 予想
        $expected = new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            __DIR__.'/init-error-data.yml'
        );

        // チェック
        $this->assertDataSetsEqual($expected, $dataSet);
    }

    /**
     * データの確認テスト.
     */
    public function xtestListAll()
    {
        $errs = ErrorTable::all();
        foreach ($errs as $k => $v) {
            echo "[$k]=$v\n";
        }

        $this->assertEquals(1, $this->getConnection()->getRowCount('error_data'), 'entry data test');
    }
}
