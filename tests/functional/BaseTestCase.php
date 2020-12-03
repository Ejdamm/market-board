<?php

namespace MarketBoard\Tests\Functional;

use Anddye\Mailer\Mailer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use Slim\Middleware\Session;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use SlimSession\Helper;
use Throwable;
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

    protected static $listing_data = [
        [
            "email" => "test@test.com",
            "subcategory_id" => null,
            "unit_price" => "123",
            "quantity" => "2",
            "removal_code" => "AAAAAA",
            "description" => "Lorem Ipsum1",
            "created_at" => "2020-06-18 23:14:18",
        ],
        [
            "email" => "test3@test.com",
            "subcategory_id" => null,
            "unit_price" => "789",
            "quantity" => "1",
            "removal_code" => "AAAAAA",
            "description" => "Lorem Ipsum2",
            "created_at" => "2020-06-18 23:14:17",
        ],
        [
            "email" => "test2@test.com",
            "subcategory_id" => null,
            "unit_price" => "456",
            "quantity" => "3",
            "removal_code" => "AAAAAA",
            "description" => "Lorem Ipsum3",
            "created_at" => "2020-06-18 23:14:16",
        ]
    ];

    protected static $category = [
        "category_name" => "category_test"
    ];

    protected static $subcategory = [
        "subcategory_name" => "subcategory_test",
        "category_id" => null
    ];

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

    public static function setUpBeforeClass(): void
    {
        $query = "INSERT INTO categories(category_name) VALUES(?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute([self::$category['category_name']]);
        $categoryId = self::$container['db']->lastInsertId();
        self::$category["id"] = $categoryId; // Set id so clearDatabaseOf() only removes one entry

        self::$subcategory["category_id"] = $categoryId;
        $query = "INSERT INTO subcategories(subcategory_name, category_id) VALUES(?, ?);";
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values(self::$subcategory));
        $subcategoryId = self::$container['db']->lastInsertId();

        self::$listing_data[0]["subcategory_id"] = $subcategoryId;
        self::$listing_data[1]["subcategory_id"] = $subcategoryId;
    }

    public function setUp(): void
    {
        $this->clearLog();
    }

    public function tearDown(): void
    {
        $this->clearLog();
    }

    public static function tearDownAfterClass(): void
    {
        self::clearListingsTable();

        unset(self::$subcategory['category_id']);
        self::clearDatabaseOf("subcategories", self::$subcategory);
        unset(self::$category['id']);
        self::clearDatabaseOf("categories", self::$category);
        self::$category['id'] = null;
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

        $app->add(new Session([
            'name' => 'my_session',
            'autorefresh' => true
        ]));

        self::$container =  $app->getContainer();
        self::$container['db'] = function (Container $container) {
            $conf = $container->get('settings')['db_test'];
            $pdo = new PDO(
                $conf['adapter'] . ':host=' . $conf['host'] . ';dbname=' . $conf['name'],
                $conf['user'],
                $conf['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        };

        self::$container['view'] = function (Container $container) {
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

        self::$container['logger'] = function (Container $container) {
            $logger = new Logger('functional_test');
            $file_handler = new StreamHandler($this->logFile);
            $logger->pushHandler($file_handler);
            return $logger;
        };

        self::$container['mailer'] = function (Container $container) {
            $conf = $container->get('settings')['email'];
            $mailer = new Mailer($container['view'], [
                'host'      => $conf['smtp']['host'],
                'port'      => $conf['smtp']['port'],
                'username'  => $conf['smtp']['username'],
                'password'  => $conf['smtp']['password'],
                'protocol'  => $conf['smtp']['protocol']
            ]);
            $mailer->setDefaultFrom($conf['from'], $conf['name']);
            return $mailer;
        };

        self::$container['session'] = function (Container $container) {
            return new Helper;
        };

        self::$container['language'] = function (Container $container) {
            // Set default language
            $language_code = $container->get('session')->get('language', 'default');
            $query = "SELECT * FROM language WHERE language = ?;";
            $statement = $container->get('db')->prepare($query);
            $statement->execute([$language_code]);
            $language = $statement->fetch();
            return $language;
        };

        self::$container['errorHandler'] = function ($container) {
            return function ($request, $response, $exception) use ($container) {
                $container->logger->addError("Exception thrown. Stacktrace: " . $exception);
                return $container->view->render($response->withStatus(500), 'errors/error500.html.twig', [
                    'language' => $container->language,
                ]);
            };
        };

        self::$container['phpErrorHandler'] = function ($container) {
            return function ($request, $response, $exception) use ($container) {
                $container->logger->addError("Exception thrown. Stacktrace: " . $exception);
                return $container->view->render($response->withStatus(500), 'errors/error500.html.twig', [
                    'language' => $container->language,
                ]);
            };
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
     * @throws Throwable
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
        $response = @$this->app->process($request, $response);

        // Return the response
        return $response;
    }

    protected static function clearListingsTable()
    {
        $statement = self::$container['db']->prepare("DELETE FROM listings WHERE 1=1;");
        $statement->execute();
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

        // Empty if no data was found. An object full of data if found.
        $this->assertNotEmpty($queryResult, "Sql query did not find a result from given data!");
    }

    public function verifyEntryRemoved($table, $id)
    {
        $prepare = self::createPDOPreparedConditions(['id' => $id]);
        $query = "SELECT * FROM $table WHERE " . $prepare['conditions'] . ";";
        $statement = self::$container['db']->prepare($query);
        $statement->execute($prepare['params']);
        $queryResult = $statement->fetch();

        // Empty result if listing with id was removed.
        $this->assertEmpty($queryResult, "Sql query did find a result even if it should be removed!");
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
            $this->assertStringContainsString($singleContent, $actualLogging);
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
}
