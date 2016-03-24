<?php
/**
 * エラー報告を処理するクラス
 * @copyright 2016 YuTanaka@AmuseOne
 */

class CError {
    /**
     * 渡したオブジェクトを、連想配列にして返す
     * 連想をtitle、データをdata
     */
    public static function convJSON2Array($data) {
        $obj = json_decode($data);

        $datas = [];
        foreach($obj as $k => $v) {
            $datas[$k] = ["title"=>$k, "data"=>$v];
        }
        return $datas;
    }
}

 ?>
