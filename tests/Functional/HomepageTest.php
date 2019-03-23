<?php

namespace Tests\Functional;

class HomepageTest extends BaseTestCase
{
    public function testGetHomepageWithoutName()
    {
        $response = $this->runApp('GET', '/name');
        $this->assertEquals(200, $response->getStatusCode());

        $htmlBody = (string)$response->getBody();
        $this->assertStringContainsString('Hello name', $htmlBody);
        $this->assertStringNotContainsString('Godbye name', $htmlBody);
    }
}
