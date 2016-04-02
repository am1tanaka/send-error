<?php

namespace Am1\Utils;

class am1util
{
    /**
     * ランダム文字列を作成する
     * @param int $len : 文字数
     */
    public static function makeRandWords($len)
    {
        //$key = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        // 紛らわしい文字は省く
        $key = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";

        $wd = "";
        for ($i=0; $i<$len; $i++) {
            $wd .= substr($key, rand(0, strlen($key)-1), 1);
        }

        return $wd;
    }

    /**
     * メールを送信
     * @param string $to 宛先
     * @param string $from 送信元メールアドレス
     * @param string $fromname 送信元名
     * @param string $subject 件名
     * @param string $body 本文
     * @return bool true=成功 / false=失敗
     */
    public static function sendMail($to, $from, $fromname, $subject, $body)
    {
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");
        return mb_send_mail(
            $to,
            $subject,
            $body,
            "From :".mb_encode_mimeheader($fromname)."<".$from.">"
        );
    }
}
