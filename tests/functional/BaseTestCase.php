<?php

namespace Startplats\Tests\Functional;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;
use \Mos\Database\CDatabaseBasic;

/**
 * Class created by slims framework
 *
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
class BaseTestCase extends \PHPUnit\Framework\TestCase // https://github.com/symfony/symfony/issues/21816
{
    private $logFile;
    private $phinxWrapper;

    private static $container;
    public function __construct(String $logFile = "logs/apptest.log")
    {
        //TODO: Should we always use a test log?
        parent::__construct();
        $this->logFile = $logFile;
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return \Slim\Http\Response
     */
    public function runApp($requestMethod, $requestUri, $requestData = null)
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Set up a response object
        $response = new Response();

        // Instantiate the application
        $config = include '/var/www/html/webb/startplats/config/config.php'; //TODO: fix
        $app = new App(['settings' => $config['settings']]);

        self::$container =  $app->getContainer();
        self::$container['db'] = function ($c) {
            $conf = $c['settings']['db'];
            $db = new CDatabaseBasic([
                'dsn' => 'mysql:host=' . $conf['host'] . ';dbname=' . $conf['dbname'],
                'username' => $conf['user'],
                'password' => $conf['pass']]);
            $db->connect();
            return $db;
        };
        $this->prepareDatabase();

        self::$container['view'] = function ($container) {
            $view = new \Slim\Views\Twig(__DIR__ . '/../../resources/views/', [
                'cache' => false
            ]);
            $router = $container->get('router');
            $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
            $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
            return $view;
        };

        self::$container['logger'] = function ($c) {
            $logger = new \Monolog\Logger('functional_test');
            $file_handler = new \Monolog\Handler\StreamHandler('logs/apptest.log');
            $logger->pushHandler($file_handler);
            return $logger;
        };

        // To make sure the log is empty at the start of the run
        $this->clearLog();

        // Register routes
        require __DIR__ . '/../../src/routes.php';

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }

    public function prepareDatabase()
    {
        //stuff..
    }

    public function clearDatabaseOf($table, $data)
    {
        $index = 0;
        $conditions = "";
        foreach ($data as $value => $key) {
            $conditions .= "$value='$key'";
            $index ++;
            if ($index != sizeof($data)) {
                $conditions .= " AND ";
            }
        }
        self::$container['db']->execute("delete from $table where $conditions;");
    }

    public function verifyEntryInserted($table, $data)
    {
        $index = 0;
        $conditions = "";
        foreach ($data as $value => $key) {
            $conditions .= "$value='$key'";
            $index ++;
            if ($index != sizeof($data)) {
                $conditions .= " AND ";
            }
        }
        self::$container['db']->execute("select * from $table where $conditions;");
        $queryResult = self::$container['db']->fetchOne();

        // False if no data was found. An object full of data if found.
        $this->assertNotFalse($queryResult, "Sql query did not find a result from given data!");
    }

    /**
     * Verify the content of logFile,
     *
     * @param Array of strings with expected Strings to contain.
     */
    public function assertLogContains($expectedContent = array())
    {
        $actualLogging = file_get_contents($this->logFile);

        foreach ($expectedContent as $singleContent) {
            echo $singleContent;
        }
    }

    /**
     * Verify the log file does not contain the given array
     * @param Array of strings with expected strings to NOT contain.
     */
    public function assertLogDoesNotContain($expectedNotContain = array())
    {
        $actualLogging = file_get_contents($this->logFile);

        foreach ($expectedNotContain as $singleContent) {
            $this->assertStringNotContainsString($singleContent, $actualLogging);
        }
    }

    /**
     * Clear the content of target log file
     */
    public function clearLog()
    {
        file_put_contents($this->logFile, "");
    }


    //TODO: HOW THE FUCK DO I ACCESS APP VARIAVBLE HERE??? OR EVEN THE DATABASE?
    //THe docs does not even tell you that...Just "here" is the autogenerated test, have fun.
    // Read the docs for more info, whereas the docs just takes you to the same place again..
    public function clearDatabase()
    {
    }
}
