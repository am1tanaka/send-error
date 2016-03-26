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
    public $pdo_conn = null;

    /**
     * TestCaseからデータベースへの接続
     */
    public function getConnection() {
        if ($this->pdo_conn == null) {
            $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', TEST_DB_USER, TEST_DB_PASS);
            $this->pdo_conn = $this->createDefaultDBConnection($pdo);
        }
        return $this->pdo_conn;
    }

    /**
     * テスト用のデータセットを作成する
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            __DIR__ . '/init-error-data.yml'
        );
    }

    /**
     * DBに接続
     */
    public function testConnection() {
        $settings = require __DIR__ . '/../../src/settings-debug.php';

        $capsule = new Capsule;

        $capsule->addConnection($settings['settings']['db']['config']);
        $capsule->setAsGlobal();

        $results = Capsule::table(TABLE_ERROR)->get();

        $this->assertNotNull($results);
    }

    /**
     * データの登録テスト
     */
    public function testInsert() {
        $settings = require __DIR__ . '/../../src/settings-debug.php';

        $capsule = new Capsule;

        $capsule->addConnection($settings['settings']['db']['config']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // 追加
        $err = new ErrorTable;
        $err->keycode = Am1Util::makeRandWords($settings['settings']['app']['KEYCODE_LENGTH']);
        $err->description = "YuTanaka";
        $err->save();
    }

    /**
     * データの確認テスト
     */
    public function testListAll() {

    }
}
 ?>
