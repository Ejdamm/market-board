<?php

namespace Startplats\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Startplats\Listings;
use PDO;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class ListingsTest extends TestCase
{
    use ArraySubsetAsserts;

    private static $listing_data1 = [
        "email" => "test@test.com",
        "subcategory_id" => null,
        "price" => "123",
        "quantity" => "2",
        "removal_code" => "AAAAAA"
    ];

    private static $listing_data2 = [
        "email" => "test2@test.com",
        "subcategory_id" => null,
        "price" => "456",
        "quantity" => "3",
        "removal_code" => "AAAAAA"
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

    public static function setUpBeforeClass(): void
    {
        $configFile = include __DIR__ . '/../../config/config.php';
        $dbConf = $configFile['settings']['db'];
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

        self::$listing_data1["subcategory_id"] = $subcategoryId;
        self::$listing_data2["subcategory_id"] = $subcategoryId;


        self::$listings = new Listings(self::$db);
    }

    public static function tearDownAfterClass(): void
    {
        self::clearDatabaseOf("listings", self::$listing_data1);
        self::clearDatabaseOf("listings", self::$listing_data2);
        self::clearDatabaseOf("subcategories", self::$subcategory);
        self::clearDatabaseOf("categories", self::$category);
    }

    public function tearDown(): void
    {
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
        $insert_id = self::$listings->insertListing(self::$listing_data1);
        $this->assertNotNull($insert_id);
        return $insert_id;
    }

    /**
     * @depends testInsertListing
     */
    public function testGetSingleListingThatExist($last_inserted_id)
    {
        $expected = [
            "subcategory_name" => self::$subcategory["subcategory_name"],
            "category_name" => self::$category["category_name"],
            "email" => self::$listing_data1["email"],
            "price" => self::$listing_data1["price"],
            "quantity" => self::$listing_data1["quantity"]
        ];
        $actual = self::$listings->getSingleListing($last_inserted_id);
        $this->assertArraySubset($expected, $actual);
    }

    /**
     * @depends testInsertListing
     */
    public function testGetSingleListingThatDontExist($last_inserted_id)
    {
        $actual = self::$listings->getSingleListing($last_inserted_id + 1);
        $this->assertFalse($actual);
    }

    /**
     * @depends testInsertListing
     */
    public function testRemoveListingThatExists($last_inserted_id)
    {
        $before = self::$listings->getNrOfListings();
        $actual = self::$listings->removeListing($last_inserted_id, self::$listing_data1['removal_code']);
        $after = self::$listings->getNrOfListings();

        $this->assertEquals(1, $actual, "Last inserted id: $last_inserted_id");
        $this->assertGreaterThan($after, $before);
    }

    /**
     * @depends testInsertListing
     */
    public function testRemoveListingThatDoesntExists($last_inserted_id)
    {
        $before = self::$listings->getNrOfListings();
        $actual = self::$listings->removeListing($last_inserted_id+10, self::$listing_data1['removal_code']);
        $after = self::$listings->getNrOfListings();

        $this->assertEquals(0, $actual, "Last inserted id: $last_inserted_id");
        $this->assertEquals($before, $after);
    }

    /**
     * @depends testInsertListing
     */
    public function testGetMultipleListings()
    {
        self::clearDatabaseOf("listings", self::$listing_data1);
        self::clearDatabaseOf("listings", self::$listing_data2);
        self::$listings->insertListing(self::$listing_data1);
        self::$listings->insertListing(self::$listing_data2);

        $expected1 = [
            "subcategory_name" => self::$subcategory["subcategory_name"],
            "category_name" => self::$category["category_name"],
            "email" => self::$listing_data1["email"],
            "price" => self::$listing_data1["price"],
            "quantity" => self::$listing_data1["quantity"]
        ];

        $expected2 = [
            "subcategory_name" => self::$subcategory["subcategory_name"],
            "category_name" => self::$category["category_name"],
            "email" => self::$listing_data2["email"],
            "price" => self::$listing_data2["price"],
            "quantity" => self::$listing_data2["quantity"]
        ];

        $actual = self::$listings->getMultipleListings(2, 0);

        $this->assertArraySubset($expected1, $actual[0]);
        $this->assertArraySubset($expected2, $actual[1]);
    }

    /**
     * @depends testGetMultipleListings
     */
    public function testGetMultipleListingsLimit()
    {
        $actual = self::$listings->getMultipleListings(1, 0);
        $this->assertEquals(1, count($actual));
    }

    /**
     * @depends testGetMultipleListings
     */
    public function testGetMultipleListingsOffset()
    {
        $expected = [
            "subcategory_name" => self::$subcategory["subcategory_name"],
            "category_name" => self::$category["category_name"],
            "email" => self::$listing_data2["email"],
            "price" => self::$listing_data2["price"],
            "quantity" => self::$listing_data2["quantity"]
        ];

        $actual = self::$listings->getMultipleListings(1, 1);

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
        self::$listings->insertListing(self::$listing_data1);
        $after = self::$listings->getNrOfListings();
        $this->assertGreaterThan($before, $after);
    }
}
