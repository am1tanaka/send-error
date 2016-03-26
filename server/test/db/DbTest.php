<?php
/**
 * DBのテスト
 */
require_once(__DIR__."/../../src/config-debug.php");
require_once(__DIR__."/../testConfig.php");
require_once(__DIR__."/../../src/am1/utils/am1util.php");
//require_once("./server/src/am1/utils/cerror.php");
//require_once("./server/vendor/autoload.php");
//require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Am1\Utils\Am1Util;

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
    var $capsule = null;
    var $settings;

    /**
     * TestCaseからデータベースへの接続
     */
    public function getConnection() {
        if ($this->pdo_conn == null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO('sqlite::memory:');
                //self::$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', TEST_DB_USER, TEST_DB_PASS);
            }

            //$this->pdo_conn = $this->createDefaultDBConnection(self::$pdo);
            $this->pdo_conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
        }
        return $this->pdo_conn;
    }

    /**
     * テスト用のデータセットを作成する。最初の１回だけ実行
     */
    public function getDataSet() {
        // 初期化
        $this->settings = require __DIR__ . '/../../src/settings-debug.php';

        $this->capsule = new Capsule;

        //$this->capsule->addConnection($this->settings['settings']['db']['config']);
        $this->capsule->addConnection(
        [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ]);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        // データセットを返す
        //return $this->createFlatXMLDataSet(__DIR__.'/init-error-data.xml');
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            __DIR__ . '/init-error-data.yml'
        );
    }

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
    public function testListAll() {
        $errs = ErrorTable::all();
        foreach($errs as $k => $v) {
            echo "[$k]=$v\n";
        }

        $this->assertEquals(1, $this->getConnection()->getRowCount("error_data"), "entry data test");
    }
}
 ?>
