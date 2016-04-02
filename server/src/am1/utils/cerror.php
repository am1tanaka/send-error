<?php
/**
 * エラー報告を処理するクラス
 * @copyright 2016 YuTanaka@AmuseOne
 */

namespace Am1\Utils;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;

// id
// keycode varchar(16)
// description text
class ErrorTable extends Eloquent
{
    protected $table = 'error_data';
}

/**
 * エラー処理クラス
 */
class CError
{
    /** Illuminate Databaseのオブジェクト*/
    public static $capsule = null;
    /** キーコードの長さ*/
    const KEY_LENGTH = 16;

    /**
     * コンストラクタ。Illuminate Databaseの接続を開始
     */
    public function __construct($setting)
    {
        if (self::$capsule == null) {
            self::$capsule = new Capsule;

            self::$capsule->addConnection($setting);
            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
        }
    }

    /**
     * 渡したオブジェクトを、連想配列にして返す
     * 連想をtitle、データをdata
     */
    public static function convJSON2Array($data)
    {
        $obj = json_decode($data);

        $datas = [];
        foreach ($obj as $k => $v) {
            $datas[$k] = ["title"=>$k, "data"=>$v];
        }
        return $datas;
    }

    /**
     * JSON文字列をエラーデータベースに登録する。
     * キーを指定した場合はそのキーで。キーを指定していない場合は自動生成する
     */
    public function entryErrorData($json, $key="")
    {
        // キーを作成
        if (strlen($key) !== self::KEY_LENGTH) {
            $key = Am1Util::makeRandWords(self::KEY_LENGTH);
        }

        // データを登録
        $err = new ErrorTable;
        $err->keycode = $key;
        $err->description = $json;
        $err->save();
    }

    /**
     * 指定したキーコードの文字列を返す。
     * 見つからない場合はから文字を返す
     * @param string $key 取り出したいキーコード
     * @return 成功したら取り出したデータを連想配列に変換して返す。失敗したらfalseを返す
     */
    public function getDescriptionArrayFromDB($key)
    {
        $user = ErrorTable::where('keycode', '=', $key)->get();
        if (count($user) == 0) {
            return false;
        }
        // 変換
        $data = json_decode($user, true)[0];
        return json_decode($data['description'], true);
    }

    /**
     * 指定のキーのデータを削除。削除した行数を返す
     * @param string $key 取り出したいキーコード
     * @return 削除した行数を返す
     */
    public function deleteDataFromDB($key)
    {
        return ErrorTable::where('keycode', '=', $key)->delete();
    }
}
