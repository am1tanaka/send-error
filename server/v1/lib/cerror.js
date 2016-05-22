/**
 * エラーを報告するためのJavaScriptクラス
 * 接続先アドレス、アプリ名、報告したい内容を設定したオブジェクト、必要な場合は送信後の
 * メッセージとステータスコードを受け取るコールバックを設定して、CError.sendErrorを呼び出す。
 * @copyright 2016 YuTanaka@AmuseOne
 */
class CError {
    /**
     * エラー情報を指定の送信先に送信する
     * @param string url 接続先
     * @param string appname アプリ名
     * @param object add メッセージに追加要素を、タイトルをオブジェクトのプロパティ名、メッセージを値に入れて渡す
     * @param function cb コールバック関数。第１引数にステータスコード、第２引数にメッセージを渡す
     */
    static sendError(url, appname, add, cb) {
        var senddata = CError.makeParams(appname, add);

        $.post(
            url, {
                description: senddata
            }
        ).always(function(responseText, textStatus, xhr) {
            // 通信後の処理
            if (typeof cb == "function") {
                cb(responseText, xhr.status);
            }
        });
    }

    /**
     * 送信するパラメータを生成して返す
     * @param string appname アプリ名
     * @param array add 報告に追加する連想配列
     * @return string 送信するオブジェクトのJSON文字列
     */
    static makeParams(appname, add) {
        var datas = {
            appname: appname
        };
        // 追加データ
        if (add) {
            for (var data in add) {
                datas[data] = add[data];
            }
        }
        // 画面サイズ
        datas.clientWidth = document.body.clientWidth;
        datas.clientHeight = document.body.clientHeight;
        datas.screenWidth = window.screenWidth;
        datas.screenHeight = window.screenHeight;
        // ナビゲーターデータ
        for (var data in navigator) {
            if (navigator[data] == "") {
                continue;
            }
            if (((typeof navigator[data]) != "function") && ((typeof navigator[data]) != "object")) {
                datas[data] = navigator[data];
            }
        }
        return JSON.stringify(datas);
    }
}
