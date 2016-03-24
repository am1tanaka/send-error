class CError {
    static makeParams(add) {
        var datas = {};
        // 追加データ
        if (add)
        {
            for(var data in add) {
                datas[data] = add[data];
            }
        }
        // 画面サイズ
        datas.clientWidth = document.body.clientWidth;
        datas.clientHeight = document.body.clientHeight;
        datas.screenWidth = window.screenWidth;
        datas.screenHeight = window.screenHeight;
        // ナビゲーターデータ
        for(var data in navigator) {
            if (navigator[data] == "")
            {
                continue;
            }
            if (    ((typeof navigator[data]) != "function")
                &&  ((typeof navigator[data]) != "object")) {
                datas[data] = navigator[data];
            }
        }
        return JSON.stringify(datas);
    }
}
