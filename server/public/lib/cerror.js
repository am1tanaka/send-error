/**
 * エラーを報告するためのJavaScriptクラス
 * @copyright 2016 YuTanaka@AmuseOne
 */
class CError {
    /**
     * エラー情報を指定の送信先に送信する
     * @param string url 接続先
     * @param string appname アプリ名
     * @param object add 追加要素
     */
    static sendError(url, appname, add) {
        var senddata = CError.makeParams(appname, add);

        $.post(
            url, {
                description: senddata,
                hash: CRC32B.crc32b(senddata)
            },
            function(data) {
                alert(data);
            }
        );
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
