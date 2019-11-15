<?php

namespace Startplats\Tests\Functional;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Twig\TwigFilter;

/**
 * Class created by slims framework
 *
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
class BaseTestCase extends TestCase // https://github.com/symfony/symfony/issues/21816
{
    private $logFile;
    private $config;

    protected $app;
    protected static $container;

    public function __construct(string $logFile = null)
    {
        parent::__construct();
        $this->config = include __DIR__ . '/../../config/config.php';
        if ($logFile == null) {
            $this->logFile = $this->config['settings']['logger']['test_path'];
        } else {
            $this->logFile = $logFile;
        }
        $this->app = $this->runApp();
    }

    private static function createPDOPreparedConditions($data)
    {
        $index = 0;
        $conditions = "";
        $params = [];
        foreach ($data as $value => $key) {
            $conditions .= "$value=?";
            $params[] = $key;
            $index++;
            if ($index != sizeof($data)) {
                $conditions .= " AND ";
            }
        }
        return ["conditions" => $conditions, "params" => $params];
    }


    public function runApp()
    {
        // Instantiate the application
        $app = new App($this->config);

        self::$container =  $app->getContainer();
        self::$container['db'] = function ($container) {
            $default = $container->get('environments')['default_database'];
            $conf = $container->get('environments')[$default];
            $pdo = new PDO(
                $conf['adapter'] . ':host=' . $conf['host'] . ';dbname=' . $conf['name'],
                $conf['user'],
                $conf['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        };
        $this->prepareDatabase();

        self::$container['view'] = function ($container) {
            $view = new Twig(__DIR__ . '/../../resources/views/', [
                'cache' => false
            ]);
            $router = $container->get('router');
            $uri = Uri::createFromEnvironment(new Environment($_SERVER));
            $view->addExtension(new TwigExtension($router, $uri));
            $filter = new TwigFilter('castToArray', function ($stdClassObject) {
                $response = array();
                if ($stdClassObject) {
                    foreach ($stdClassObject as $key => $value) {
                        $response[] = $value;
                    }
                }
                return $response;
            });
            $view->getEnvironment()->addFilter($filter);
            return $view;
        };

        self::$container['logger'] = function ($c) {
            $logger = new Logger('functional_test');
            $file_handler = new StreamHandler($this->logFile);
            $logger->pushHandler($file_handler);
            return $logger;
        };

        // To make sure the log is empty at the start of the run
        $this->clearLog();

        // Register routes
        require __DIR__ . '/../../src/routes.php';

        return $app;
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return ResponseInterface
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     */
    public function processRequest($requestMethod, $requestUri, $requestData = null)
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

        // Process the application
        $response = $this->app->process($request, $response);

        // Return the response
        return $response;
    }

    public function prepareDatabase()
    {
        //stuff..
    }

    public static function clearDatabaseOf($table, $data)
    {
        $prepare = self::createPDOPreparedConditions($data);
        $statement = self::$container['db']->prepare("DELETE FROM $table WHERE " . $prepare['conditions'] . ";");
        $statement->execute($prepare['params']);
    }

    public function verifyEntryInserted($table, $data)
    {
        $prepare = self::createPDOPreparedConditions($data);
        $statement = self::$container['db']->prepare("SELECT * FROM $table WHERE " . $prepare['conditions'] . ";");
        $statement->execute($prepare['params']);
        $queryResult = $statement->fetch();

        // False if no data was found. An object full of data if found.
        $this->assertNotFalse($queryResult, "Sql query did not find a result from given data!");
    }

    /**
     * Verify the content of logFile,
     *
     * @param array $expectedContent Array of strings with expected Strings to contain.
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
     * @param array $expectedNotContain Array of strings with expected strings to NOT contain.
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
        if ($this->logFile != null) {
            file_put_contents($this->logFile, "");
        }
    }

    public function clearDatabase()
    {
    }
}
