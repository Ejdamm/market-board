<?php

namespace Startplats\Tests\Functional;

class LoggingTest extends BaseTestCase
{
    /**
     * Dependent on what routes get('/{name}' writes to logfile.
     */
    public function testInfoLogginHomePage()
    {
        $logFile = 'logs/apptest.log';
        $expectedHomePageLogging = "functional_test.INFO: Hello testUser123";

        $baseTest = new BaseTestCase($logFile);
        $response = $baseTest->runApp('GET', '/testUser123');
        $actualLogging = file_get_contents($logFile);
        $this->assertStringContainsString($expectedHomePageLogging, $actualLogging);
        $baseTest->clearLog($logFile);
    }
}
