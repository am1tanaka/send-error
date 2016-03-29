<?php
/**
 * DBのテスト
 */
//namespace Am1\SendError\Tests;

require_once(__DIR__."/../../src/am1/utils/am1util.php");
require_once(__DIR__."/../../src/am1/utils/cerror.php");

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Am1\Utils\Am1Util;
use Am1\Utils\CError;

// id
// keycode varchar(16)
// description text

class ErrorTable extends Eloquent {
    protected $table = 'error_data';
}

class DbTest extends \PHPUnit_Extensions_Database_TestCase
{
    public static $pdo = null;
    var $pdo_conn = null;
    var $settings;
    static $cerror = null;

    /**
     * TestCaseからデータベースへの接続
     */
    public function getConnection() {
        if ($this->pdo_conn == null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', TEST_DB_USER, TEST_DB_PASS);
            }

            $this->pdo_conn = $this->createDefaultDBConnection(self::$pdo);
        }
        return $this->pdo_conn;
    }

    /**
     * テスト用のデータセットを作成する。テストごとに実行
     */
    public function getDataSet() {
        // 初期化
        $this->settings = require __DIR__ . '/../../src/settings.php';

        // クラスを初期化
        if (self::$cerror == null) {
            self::$cerror = new CError($this->settings['settings']['db']['config']);
        }

        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            __DIR__ . '/init-error-data.yml');
    }

    /**
     * データ登録
     * 指定の文字列をデータベースに登録する
     */
    public function testDataEntry() {
        // テストデータを読み込む
        $data = file_get_contents(__DIR__ . '/entry-test-data.json');

        // データ登録
        self::$cerror->entryErrorData($data, '0123456789112345');

        // 登録成功チェック
        $this->assertEquals(2, $this->getConnection()->getRowCount('error_data'));

        // 成功チェック
        $succ = self::$cerror->getDescriptionArrayFromDB("0123456789112345");
        $this->assertEquals(1080, $succ['clientWidth']);

        /*
        // データを読み出す
        $queryTable = $this->getConnection()->createQueryTable('error_data', 'select keycode,description from error_data');
        // データセットを返す
        $yaml = new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            __DIR__ . '/expect-error-data.yml');
        $expected = $yaml->getTable("error_data");
        // チェック
        $this->assertTablesEqual($expected, $queryTable);
        */
    }

    /**
     * データの取得
     * メールにCSVを添付して送信
     */
    public function testGetKey() {
        // エラーチェック
        $fail = self::$cerror->getDescriptionArrayFromDB("0");
        $this->assertFalse($fail);

        // 成功チェック
        $succ = self::$cerror->getDescriptionArrayFromDB("0123456789abcdef");
        $this->assertNotFalse($succ);
        $this->assertEquals(1080, $succ['clientWidth']);
    }

    /**
     * データの削除
     */


    /**相棒
     * DBに接続
     */
    public function _testConnection() {
        $results = Capsule::table(TABLE_ERROR)->get();

        $this->assertNotNull($results);
    }

    /**
     * データの登録テスト
     */
    public function _testInsert() {
        // 追加
        $err = new ErrorTable;
        $err->keycode = Am1Util::makeRandWords($this->settings['settings']['app']['KEYCODE_LENGTH']);
        $err->description = "YuTanaka";
        $err->save();

        // 予想
        $expected = new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            __DIR__ . '/init-error-data.yml'
        );

        // チェック
        $this->assertDataSetsEqual($expected, $dataSet);
    }

    /**
     * データの確認テスト
     */
    public function _testListAll() {
        $errs = ErrorTable::all();
        foreach($errs as $k => $v) {
            echo "[$k]=$v\n";
        }

        $this->assertEquals(1, $this->getConnection()->getRowCount("error_data"), "entry data test");
    }
}
 ?>
