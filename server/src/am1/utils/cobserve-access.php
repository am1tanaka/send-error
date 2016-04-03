<?php
/**
 * アクセスを監視するライブラリ。
 * データベースを共有するために、CErrorとセットで運用.
 *
 * @copyright 2016 YuTanaka@AmuseOne
 */
namespace Am1\Utils;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * アクセス監視用のクラス.
 */
class CObserveAccess
{
    /* 監視用のデフォルト設定*/
    public $settings = [
        'PAUSE_SEC' => 600,     // 一時停止を判定するのに有効なデータの経過秒数
        'PAUSE_COUNT' => 5,     // 一時停止を判定する回数
        'NG_COUNT' => 10,       // 一度も成功しないでこの回数失敗し続けた場合、
                                // NGリストに追加する
        'KEYCODE_LENGTH' => 16, // キーコードの文字数
        'ADMIN_EMAIL' => '',    // 管理者メールアドレス
        'FROM_EMAIL' => '',     // 送信元メールアドレス
        'FROM_NAME' => 'AmuseOneSystem', // メール送信元名
    ];

    /** このシステムの名前*/
    const MY_APP_NAME = 'Am1ObserveAccess';
    /** リモートホストの長さ*/
    const REMOTE_HOST_LENGTH = 64;
    /** アプリ名の長さ*/
    const APP_NAME_LENGTH = 64;
    /** エラーメッセージの長さ*/
    const ERROR_LENGTH = 255;

    /**
     * コンストラクタ
     */
    public function __construct($set = [])
    {
        // 設定を上書き
        foreach ($set as $k => $v) {
            $this->settings[$k] = $v;
        }
    }

    /**
     * 停止などの判定。すでにNGの時は、そのままfalseを返す.
     *
     * @param string $host    リモートホスト
     * @param string $appname アプリ名
     * @param string $err     エラーメッセージ
     *
     * @return bool true=継続して良い / false=アクセス停止
     */
    public function entryInvalidAccess($host, $appname, $err)
    {
        // NGリストに登録されているホストの場合はすぐにアクセス停止を返す
        if ($this->isNG($host)) {
            return false;
        }

        // データの長さを調整
        $host = substr($host, 0, self::REMOTE_HOST_LENGTH);
        $appname = substr($appname, 0, self::APP_NAME_LENGTH);
        $err = substr($err, 0, self::ERROR_LENGTH);

        // データを登録する
        $newtbl = new InvalidAccessTable();
        $newtbl->remote_host = $host;
        $newtbl->app_name = $appname;
        $newtbl->error_message = $err;
        $newtbl->keycode = Am1Util::makeRandWords($this->settings['KEYCODE_LENGTH']);
        $newtbl->save();

        // NGチェック
        $hostapp = InvalidAccessTable::where('remote_host', '=', $host)->where('app_name', '=', $appname);
        $cnt = $hostapp->count();
        if ($cnt >= $this->settings['NG_COUNT']) {
            $this->entryNGListWithHost($host);

            return true;
        }

        // 指定時間内の登録回数を確認
        $limit = date('Y-m-d H:i:s', time() - $this->settings['PAUSE_SEC']);
        $cnt = $hostapp->where('created_at', '>=', $limit)->count();
        if ($cnt >= $this->settings['PAUSE_COUNT']) {
            // ぴったりの時は、システム管理者に報告
            if ($cnt == $this->settings['PAUSE_COUNT']) {
                $this->reportPause($host, $appname, $err, $newtbl->keycode);
            }

            return false;
        }

        // まだなので継続許可
        return true;
    }

    /**
     * 指定のキーコードに対応するホストをアクセス失敗テーブルから探して返す。
     * 見つからない場合はfalse.
     *
     * @param string $key キーコード
     *
     * @return 成功したらstringのホスト / ない場合はfalse
     */
    public function getHostWithKeyInvalidHost($key)
    {
        $key = substr($key, 0, $this->settings['KEYCODE_LENGTH']);
        $host = InvalidAccessTable::where('keycode', '=', $key);
        if ($host->count() == 0) {
            return false;
        }

        return $host->take(1)->get()[0]->remote_host;
    }

    /**
     * アクセスに成功したときに呼び出す関数。指定のホストのデータを削除する.
     *
     * @param string $key         削除するホストのキーコード
     * @param string $remote_host 接続元のホスト。キーがなかった時のエラー処理
     *
     * @return int 削除した件数。0の時は失敗
     */
    public function releaseInvalidAccess($key, $remote_host)
    {
        $host = $this->getHostWithKeyInvalidHost($key);

        // 見つからない場合は不正なアクセスなので、不正なアクセスを登録
        if ($host === false) {
            $this->entryInvalidAccess(
                $remote_host,
                self::MY_APP_NAME,
                "不正なキーでのアクセス失敗の削除要求:$key"
            );

            return 0;
        }

        // 指定のホストを削除
        return InvalidAccessTable::where('remote_host', '=', $host)->delete();
    }

    /**
     * 一時停止に伴う管理者への報告.
     */
    public function reportPause($host, $appname, $err, $keycode)
    {
        $subject = '[AM1-SYS]ホストの一時停止レポート';
        $mes = "以下のホストからのアクセスを一時停止しました。\n";
        $mes .= "\n";
        $mes .= 'APP   : '.$appname."\n";
        $mes .= 'HOST  : '.$host."\n";
        $mes .= 'DOMAIN: '.@gethostbyaddr($host)."\n";
        $mes .= 'RESUME: '.INVALID_ROOT.'/'.$keycode."/release\n";
        $mes .= 'ADD NG: '.INVALID_ROOT.'/'.$keycode."/ng\n";
        $mes .= "ERROR :\n";
        $mes .= "----\n";
        $mes .= $err."\n";
        $mes .= "----\n\n";
        $mes .= "AmuseOne Service SystemMail.\n";

        Am1Util::sendMail(
            $this->settings['ADMIN_EMAIL'],
            $this->settings['FROM_EMAIL'],
            $this->settings['FROM_NAME'],
            $subject,
            $mes
        );
    }

    /**
     * 指定のキーコードで、InvalidAccessTableに登録されているホストを
     * NGリストに登録する.
     *
     * @param string $key         キーコード
     * @param string $remote_host 接続元のリモートアドレス
     *
     * @return string=成功したら、停止したホストアドレスを返す / false=失敗
     */
    public function entryNGList($key, $remote_host)
    {
        $host = $this->getHostWithKeyInvalidHost($key);
        if ($host === false) {
            // 見つからないので不正なアクセス
            $this->entryInvalidAccess(
                $remote_host,
                self::MY_APP_NAME,
                "entryNGList:Invalid KeyCode:$key"
            );

            return false;
        }

        // 指定のホストをNGリストにする
        $this->entryNGListWithHost($host);

        return $host;
    }

    /**
     * 指定のホストをNGリストに追加.
     *
     * @param string $host NGリストに追加するホスト
     */
    public function entryNGListWithHost($host)
    {
        $host = substr($host, 0, self::REMOTE_HOST_LENGTH);
        $ng = NGIPsTable::where('remote_host', '=', $host);
        // 指定のホストがあるかを確認
        if ($ng->count() == 0) {
            // 新規に登録
            $new = new NGIPsTable();
            $new->remote_host = $host;
            $new->keycode = Am1Util::makeRandWords($this->settings['KEYCODE_LENGTH']);
            $new->save();

            // メールを送信
            $this->reportNG($host, $new->keycode);
        } else {
            // すでにある場合は時間の更新だけ行う
            $ng->get()[0]->touch();
        }
    }

    /**
     * NGリスト登録に伴う管理者への報告.
     */
    public function reportNG($host, $keycode)
    {
        $host = substr($host, 0, self::REMOTE_HOST_LENGTH);
        $subject = '[AM1-SYS]NGホストの追加レポート';
        $mes = "以下のホストの操作ミスが規定数を超えたので、";
        $mes .= "NGリストに追加しました。\n";
        $mes .= "\n";
        $mes .= 'HOST  : '.$host."\n";
        $mes .= 'DOMAIN: '.@gethostbyaddr($host)."\n";
        $mes .= 'NG解除 : '.INVALID_ROOT.'/ng/'.$keycode."/release\n";
        $mes .= "\n----\n";
        $mes .= "AmuseOne Service SystemMail.\n";

        Am1Util::sendMail(
            $this->settings['ADMIN_EMAIL'],
            $this->settings['FROM_EMAIL'],
            $this->settings['FROM_NAME'],
            $subject,
            $mes
        );
    }

    /**
     * 指定のキーコードのホストをNGリストから削除.
     *
     * @param string $keycode     削除
     * @param string $remote_host アクセスしてきたホスト名
     *
     * @return stringの時、削除したホスト / false=キーコード無効
     */
    public function releaseNGList($keycode, $host)
    {
        $keycode = substr($keycode, 0, $this->settings['KEYCODE_LENGTH']);

        // 削除
        $where = NGIPsTable::where('keycode', '=', $keycode);
        // 数が0の時、不正なアクセス
        if ($where->count() == 0) {
            $this->entryInvalidAccess($host, self::MY_APP_NAME, "不正なキーでのNG削除要求:$keycode");

            return false;
        }

        $ret = $where->get()[0]->remote_host;
        $where->delete();

        return $ret;
    }

    /**
     * 指定のホストがNGリストに登録されているかを確認.
     *
     * @param string $host 確認するホスト
     *
     * @return 0=NGじゃない / 1=NG
     */
    public function isNG($host)
    {
        $host = substr($host, 0, self::REMOTE_HOST_LENGTH);

        return NGIPsTable::where('remote_host', '=', $host)->count();
    }
}
