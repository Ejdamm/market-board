<?php

namespace Startplats\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Startplats\Listings;
use PDO;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class ListingsTest extends TestCase
{
    use ArraySubsetAsserts;

    private static $listing_data = [
        [
            "email" => "test@test.com",
            "subcategory_id" => null,
            "unit_price" => "123",
            "quantity" => "2",
            "removal_code" => "AAAAAA",
            "created_at" => "2020-06-18 23:14:18",
        ],
        [
            "email" => "test3@test.com",
            "subcategory_id" => null,
            "unit_price" => "789",
            "quantity" => "1",
            "removal_code" => "AAAAAA",
            "created_at" => "2020-06-18 23:14:17",
        ],
        [
            "email" => "test2@test.com",
            "subcategory_id" => null,
            "unit_price" => "456",
            "quantity" => "3",
            "removal_code" => "AAAAAA",
            "created_at" => "2020-06-18 23:14:16",
        ]
    ];

    private static $category = [
        "category_name" => "category_test",
    ];

    private static $subcategory = [
        "subcategory_name" => "subcategory_test",
        "category_id" => null
    ];

    private static $db;
    private static $listings;
    private $last_inserted_id;

    public static function setUpBeforeClass(): void
    {
        $configFile = include __DIR__ . '/../../config/config.php';
        $dbConf = $configFile['settings']['db_test'];
        self::$db = new PDO(
            $dbConf['adapter'] . ':host=' . $dbConf['host'] . ';dbname=' . $dbConf['name'],
            $dbConf['user'],
            $dbConf['pass']
        );
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $query = "INSERT INTO categories(category_name) VALUES(?);";
        $statement1 = self::$db->prepare($query);
        $statement1->execute(array_values(self::$category));
        $categoryId = self::$db->lastInsertId();
        self::$category["id"] = $categoryId; // Set id so clearDatabaseOf() only removes one entry

        self::$subcategory["category_id"] = $categoryId;
        $query = "INSERT INTO subcategories(subcategory_name, category_id) VALUES(?, ?);";
        $statement2 = self::$db->prepare($query);
        $statement2->execute(array_values(self::$subcategory));
        $subcategoryId = self::$db->lastInsertId();

        self::$listing_data[0]["subcategory_id"] = $subcategoryId;
        self::$listing_data[1]["subcategory_id"] = $subcategoryId;
        self::$listing_data[2]["subcategory_id"] = $subcategoryId;

        self::clearListingsTable();
    }

    public function setUp(): void
    {
        self::$listings = new Listings(self::$db);

        foreach (self::$listing_data as $listing) {
            $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, created_at) VALUES(?,?,?,?,?,?);";
            $statement = self::$db->prepare($query);
            $statement->execute(array_values($listing));
            $this->last_inserted_id = self::$db->lastInsertId();
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::clearDatabaseOf("subcategories", self::$subcategory);
        self::clearDatabaseOf("categories", self::$category);
    }

    public function tearDown(): void
    {
        $this->clearListingsTable();
    }

    private static function clearListingsTable()
    {
        $statement = self::$db->prepare("DELETE FROM listings WHERE 1=1;");
        $statement->execute();
    }

    private static function clearDatabaseOf($table, $data)
    {
        $prepare = self::createPDOPreparedConditions($data);
        $statement = self::$db->prepare("DELETE FROM $table WHERE " . $prepare['conditions'] . ";");
        $statement->execute($prepare['params']);
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

    public function testInsertListing()
    {
        $insert_id = self::$listings->insertListing(self::$listing_data[0]);
        $this->assertNotNull($insert_id);
    }

    public function testGetSingleListingThatExist()
    {
        $expected = [
            "subcategory_name" => self::$subcategory["subcategory_name"],
            "category_name" => self::$category["category_name"],
            "email" => self::$listing_data[2]["email"],
            "unit_price" => self::$listing_data[2]["unit_price"],
            "quantity" => self::$listing_data[2]["quantity"]
        ];
        $actual = self::$listings->getSingleListing($this->last_inserted_id);
        $this->assertArraySubset($expected, $actual);
    }

    public function testGetSingleListingThatDontExist()
    {
        $actual = self::$listings->getSingleListing($this->last_inserted_id + 1);
        $this->assertFalse($actual);
    }

    public function testRemoveListingThatExists()
    {
        $before = self::$listings->getNrOfListings();
        $actual = self::$listings->removeListing($this->last_inserted_id, self::$listing_data[0]['removal_code']);
        $after = self::$listings->getNrOfListings();

        $this->assertEquals(1, $actual, "Last inserted id: $this->last_inserted_id");
        $this->assertGreaterThan($after, $before);
    }

    public function testRemoveListingThatDoesntExists()
    {
        $before = self::$listings->getNrOfListings();
        $actual = self::$listings->removeListing($this->last_inserted_id+10, self::$listing_data[0]['removal_code']);
        $after = self::$listings->getNrOfListings();

        $this->assertEquals(0, $actual, "Last inserted id: $this->last_inserted_id");
        $this->assertEquals($before, $after);
    }

    public function testGetMultipleListings()
    {
        $expected1 = [
            "subcategory_name" => self::$subcategory["subcategory_name"],
            "category_name" => self::$category["category_name"],
            "email" => self::$listing_data[0]["email"],
            "unit_price" => self::$listing_data[0]["unit_price"],
            "quantity" => self::$listing_data[0]["quantity"]
        ];

        $expected2 = [
            "subcategory_name" => self::$subcategory["subcategory_name"],
            "category_name" => self::$category["category_name"],
            "email" => self::$listing_data[1]["email"],
            "unit_price" => self::$listing_data[1]["unit_price"],
            "quantity" => self::$listing_data[1]["quantity"]
        ];

        $expected3 = [
            "subcategory_name" => self::$subcategory["subcategory_name"],
            "category_name" => self::$category["category_name"],
            "email" => self::$listing_data[2]["email"],
            "unit_price" => self::$listing_data[2]["unit_price"],
            "quantity" => self::$listing_data[2]["quantity"]
        ];

        $actual = self::$listings->getMultipleListings();

        $this->assertArraySubset($expected1, $actual[0]);
        $this->assertArraySubset($expected2, $actual[1]);
        $this->assertArraySubset($expected3, $actual[2]);
    }

    public function testGetMultipleListingsLimit()
    {
        self::$listings->setLimit(1);
        self::$listings->setOffset(0);
        $actual = self::$listings->getMultipleListings();
        $this->assertEquals(1, count($actual));
    }

    public function testGetMultipleListingsOffset()
    {
        $expected = [
            "subcategory_name" => self::$subcategory["subcategory_name"],
            "category_name" => self::$category["category_name"],
            "email" => self::$listing_data[1]["email"],
            "unit_price" => self::$listing_data[1]["unit_price"],
            "quantity" => self::$listing_data[1]["quantity"]
        ];

        self::$listings->setLimit(1);
        self::$listings->setOffset(1);
        $actual = self::$listings->getMultipleListings();

        $this->assertArraySubset($expected, $actual[0]);
    }

    public function testGetNrOfListingsIsNumber()
    {
        $this->assertIsInt(self::$listings->getNrOfListings());
    }

    /**
     * @depends testInsertListing
     */
    public function testGetNrOfListingsIncrements()
    {
        $before = self::$listings->getNrOfListings();
        self::$listings->insertListing(self::$listing_data[0]);
        $after = self::$listings->getNrOfListings();
        $this->assertGreaterThan($before, $after);
    }

    public function testSortedListingsAscendingPrice()
    {
        self::$listings->setSortingColumn("unit_price");
        self::$listings->setSortingOrder("ASC");
        $actual = self::$listings->getMultipleListings();
        $this->assertLessThanOrEqual($actual[1]['unit_price'], $actual[0]['unit_price']);
        $this->assertLessThanOrEqual($actual[2]['unit_price'], $actual[1]['unit_price']);
    }

    public function testSortedListingsDescendingPrice()
    {
        self::$listings->setSortingColumn("unit_price");
        self::$listings->setSortingOrder("DESC");
        $actual = self::$listings->getMultipleListings();
        $this->assertGreaterThanOrEqual($actual[1]['unit_price'], $actual[0]['unit_price']);
        $this->assertGreaterThanOrEqual($actual[2]['unit_price'], $actual[1]['unit_price']);
    }

    public function testSortedListingsAscendingDate()
    {
        self::$listings->setSortingColumn("created_at");
        self::$listings->setSortingOrder("ASC");
        $actual = self::$listings->getMultipleListings();
        $this->assertLessThanOrEqual($actual[1]['created_at'], $actual[0]['created_at']);
        $this->assertLessThanOrEqual($actual[2]['created_at'], $actual[1]['created_at']);
    }

    public function testSortedListingsDescendingDate()
    {
        self::$listings->setSortingColumn("created_at");
        self::$listings->setSortingOrder("DESC");
        $actual = self::$listings->getMultipleListings();
        $this->assertGreaterThanOrEqual($actual[1]['created_at'], $actual[0]['created_at']);
        $this->assertGreaterThanOrEqual($actual[2]['created_at'], $actual[1]['created_at']);
    }

    public function testGetMultipleListingsWhereNotCategory()
    {
        self::$listings->setWHEREFilter(0, 0);

        $actual = self::$listings->getMultipleListings();

        $this->assertEquals(3, sizeof($actual));
    }

    public function testGetMultipleListingsWhereSubCategory()
    {
        self::$listings->setWHEREFilter(0, self::$listing_data[0]["subcategory_id"] + 1);

        $actual = self::$listings->getMultipleListings();

        $this->assertEquals(0, sizeof($actual));
    }
}
