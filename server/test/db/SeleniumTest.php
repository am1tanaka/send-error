<?php
/** Seleniumの継承*/
class WebTest extends PHPUnit_Extensions_Selenium2TestCase {
    const DOMAIN = "http://0.0.0.0:8080";
    const OBSERVE_URL = "/invalid-access";

    protected function setUp() {
        $this->setBrowser('firefox');
        $this->setBrowserUrl(self::DOMAIN);
    }

    public function testReleaseInvalidList() {
        // 失敗
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->url(self::DOMAIN.self::OBSERVE_URL."/xxxx/release");
        $this->assertEquals('ok', $this->byId('info')->text());
    }

}

?>
