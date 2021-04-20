<?php


namespace MarketBoard\Tests\Unit;

use MarketBoard\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testLocalizeUrl()
    {
        $this->assertEquals(Utils::localizeUrl("", "de", "en"), "/de");
        $this->assertEquals(Utils::localizeUrl("/en", "de", "en"), "/de");
        $this->assertEquals(Utils::localizeUrl("/de", "de", "de"), "/de");
        $this->assertEquals(Utils::localizeUrl("/en/listings", "de", "en"), "/de/listings");
    }
}
