<?php

namespace Am1\Utils;

class Am1Util {
    /**
     * ランダム文字列を作成する
     * @param int $len : 文字数
     */
    public static function makeRandWords($len) {
    	//$key = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    	// 紛らわしい文字は省く
    	$key = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";

    	$wd = "";
    	for ($i=0 ; $i<$len ; $i++) {
    		$wd .= substr($key,rand(0,strlen($key)-1),1);
    	}

    	return $wd;
    }
}

 ?>
