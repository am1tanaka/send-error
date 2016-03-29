<?php
/**
 * アクセスを監視するライブラリ。
 * データベースを共有するために、CErrorとセットで運用
 * @copyright 2016 YuTanaka@AmuseOne
 */

namespace Am1\Utils;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;

/** 不正アクセステーブル*/
class InvalidAccessTable extends Eloquent {
    protected $table = 'invalid_access';
}

/** NGテーブル*/
class NGIPsTable extends Eloquent {
    protected $table = 'ng_ips';
}

/**
 * アクセス監視用のクラス
 */
class CObserveAccess {
    /** 監視用のデフォルト設定*/
    var $settings = [
        "PAUSE_SEC" => 600,    // 一時停止を判定するのに有効なデータの経過秒数
        "PAUSE_COUNT" => 5,     // 一時停止を判定する回数
        "NG_COUNT" => 10,       // 一度も成功しないでこの回数失敗し続けた場合、NGリストに追加する
        "KEYCODE_LENGTH" => 16, // キーコードの文字数
        "ADMIN_EMAIL" => "",    // 管理者メールアドレス
        "FROM_EMAIL" => ""      // 送信元メールアドレス
    ];

    /**
     * コンストラクタ
     */
    function __construct($set=[]) {
        // 設定を上書き
        foreach($this->settings as $k) {
            if (array_key_exists($k, $set)) {
                $this->settings[$k] = $set[$k];
            }
        }
    }

    /**
     * 停止などの判定。すでにNGの時は、そのままfalseを返す
     * @param string $host リモートホスト
     * @param string $appname アプリ名
     * @param string $err エラーメッセージ
     * @return bool true=継続して良い / false=アクセス停止
     */
    public function entryInvalidAccess($host, $appname, $err) {
        // NGリストに登録されているホストの場合はすぐにアクセス停止を返す
        if($this->isNG($host)) {
            return false;
        }

        // データを登録する
        $newtbl = new InvalidAccessTable;
        $newtbl->remote_host = $host;
        $newtbl->app_name = $appname;
        $newtbl->error_message = $err;
        $newtbl->keycode = Am1Util::makeRandWords($this->settings['KEYCODE_LENGTH']);
        $newtbl->save();

        // NGチェック
        $hostapp = InvalidAccessTable::where('remote_host', 'like', $host)->where('app_name', 'like', $appname);
        $cnt = $hostapp->count();
        if ($cnt >= $this->settings['NG_COUNT']) {
            entryNGList($host);
            return true;
        }

        // 指定時間内の登録回数を確認
        $limit = date("Y-m-d H:i:s", time()-$this->settings['PAUSE_SEC']);
        $cnt = $hostapp->where('created_at', '>=', $limit)->count();
        if ($cnt >= $this->settings['PAUSE_COUNT']) {
            // ぴったりの時は、システム管理者に報告
            if ($cnt == $this->settings['PAUSE_COUNT']) {
                $this->reportPause($host);
            }

            return false;
        }

        // まだなので継続許可
        return true;
    }

    /**
     * アクセスに成功したときに呼び出す関数。指定のホストのデータを削除する
     * @param string $host リモートホスト
     */
    public function releaseInvalidAccess($host) {
        // 指定のホストのデータを削除
    }

    /**
     * 一時停止に伴う管理者への報告
     */
    function reportPause($host) {

    }

    /**
     * 指定のホストをNGリストに追加
     * @param string $host NGリストに追加するホスト
     */
    function entryNGList($host) {

    }

    /**
     * 指定のキーコードのホストをNGリストから削除
     * @param string $keycode 削除
     */
    public function releaseNGList($keycode) {

    }

    /**
     * 指定のホストがNGリストに登録されているかを確認
     * @param string $host 確認するホスト
     * @return true=NG / false=NGじゃない
     */
    public function isNG($host) {
        return false;
    }
}

?>
