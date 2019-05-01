<?php

namespace Startplats\Tests\Functional;

class HomepageTest extends BaseTestCase
{
    public function testGetHomepageWithoutName()
    {
        $baseTest = new BaseTestCase();
        $response = $baseTest->runApp('GET', '/name');
        $this->assertEquals(200, $response->getStatusCode());

        $htmlBody = (string)$response->getBody();
        $this->assertStringContainsString('Hello name', $htmlBody);
        $this->assertStringNotContainsString('Goodbye name', $htmlBody);
    }
}
