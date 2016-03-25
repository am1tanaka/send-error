<?php
/**
 * DBのテスト
 */
require_once(__DIR__."/../../src/config-debug.php");
require_once(__DIR__."/../testConfig.php");
//require_once("./server/src/am1/utils/cerror.php");
//require_once("./server/vendor/autoload.php");
//require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;

// id
// keycode varchar(16)
// description text

class ErrorTable extends Eloquent {
    protected $table = 'error_data';
}

class DbTest extends \PHPUnit_Framework_TestCase
{
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

        // カプセル
        /*
        Capsule::schema()->create('users', function($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
        */

        // 追加
        $err = new ErrorTable;
        $err->keycode = "abc";
        $err->description = "YuTanaka";
        $err->save();

    }
}
 ?>
