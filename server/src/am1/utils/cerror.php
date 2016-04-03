<?php
/**
 * エラー報告を処理するクラス.
 *
 * @copyright 2016 YuTanaka@AmuseOne
 */
namespace Am1\Utils;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * エラー処理クラス.
 */
class CError
{
    /** Illuminate Databaseのオブジェクト*/
    public static $capsule = null;
    /** キーコードの長さ*/
    const KEY_LENGTH = 16;
    /** セッティングを記録*/
    private $settings;

    /**
     * コンストラクタ。Illuminate Databaseの接続を開始.
     */
    public function __construct($set)
    {
        $this->settings = $set;

        if (self::$capsule == null) {
            self::$capsule = new Capsule();

            self::$capsule->addConnection($set['db']);
            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
        }
    }

    /**
     * 渡したオブジェクトを、連想配列にして返す
     * 連想をtitle、データをdata.
     */
    public static function convJSON2Array($data)
    {
        $obj = json_decode($data);

        return self::makeArrayTable('', $obj);
    }

    /**
     * 指定の配列やオブジェクトをループして、titleとdataの連想配列を作成。
     * 要素が配列やオブジェクトの場合は再帰呼び出しする.
     *
     * @param string       $prefix 接頭文字列
     * @param object|array $obj    処理する配列かオブジェクト
     *
     * @return array 連想配列。要素名をtitle、値をdataに代入したもの
     */
    private static function makeArrayTable($prefix, $obj)
    {
        $response = [];

        foreach ($obj as $k => $v) {
            // オブジェクトの時はこの関数を再帰呼び出し
            if (is_object($v)) {
                $res = self::makeArrayTable($prefix.$k.'_', $v);
                $response = array_merge($response, $res);
            }
            // 配列の時は、配列の再帰呼び出し
            elseif (is_array($v)) {
                $res = self::makeArrayTable($prefix.$k.'_', $v);
                $response = array_merge($response, $res);
            }
            // bool値
            elseif (is_bool($v)) {
                $bl = $v ? 'true' : 'false';
                $response[] = ['title' => $prefix.$k, 'data' => $bl];
            }
            // 数値や文字列
            elseif (is_numeric($v) || is_string($v)) {
                $response[] = ['title' => $prefix.$k, 'data' => $v];
            }
        }

        return $response;
    }

    /**
     * JSON文字列をエラーデータベースに登録する。
     * キーを指定した場合はそのキーで。キーを指定していない場合は自動生成する.
     */
    public function entryErrorData($json, $key = '')
    {
        // キーを作成
        if (strlen($key) !== self::KEY_LENGTH) {
            $key = Am1Util::makeRandWords(self::KEY_LENGTH);
        }

        // データを登録
        $err = new ErrorTable();
        $err->keycode = $key;
        $err->description = $json;
        $err->save();

        // メール報告
        $subject = '[AM1-SYS]エラー報告';
        $mes = "エラーが報告されました。以下で参照と削除ができます。\n";
        $mes .= "\n";
        $mes .= '参照: '.ERROR_ROOT."/$key\n";
        $mes .= '削除: '.ERROR_ROOT."/$key/delete\n";
        $mes .= "\n----\n";
        $mes .= $this->settings['app']['SERVICE_NAME']."\n";

        Am1Util::sendMail(
            ADMIN_EMAIL,
            SYS_EMAIL,
            $this->settings['app']['SERVICE_NAME'],
            $subject,
            $mes
        );
    }

    /**
     * 指定したキーコードの文字列を返す。
     * 見つからない場合はから文字を返す.
     *
     * @param string $key 取り出したいキーコード
     *
     * @return 成功したら取り出したデータを連想� �列に変換して返す。
     *                                                           失敗したらfalseを返す
     */
    public function getDescriptionArrayFromDB($key)
    {
        $row = ErrorTable::where('keycode', '=', $key);
        if ($row->count() == 0) {
            return false;
        }
        // 変換
        return $this->convJSON2Array($row->take(1)->get()[0]->description);
    }

    /**
     * 指定のキーのデータを削除。削除した行数を返す.
     *
     * @param string $key 取り出したいキーコード
     *
     * @return 削除した行数を返す
     */
    public function deleteDataFromDB($key)
    {
        return ErrorTable::where('keycode', '=', $key)->delete();
    }
}
