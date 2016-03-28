<?php

class MailTest extends PHPUnit_Framework_TestCase
{
    public function testFirst() {
    }

    public function _testSendMail() {
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");
        if (mb_send_mail(
            TEST_TO_ADDR,
            "件名",
            "送信テスト",
            "From :".mb_encode_mimeheader("テスト送信元")."<".TEST_FROM_ADDR.">"
        ))
        {
            $this->assertEquals(0,0);
        }
        else {
            $this->assertEquals(0,1);
        }
    }
}
 ?>
