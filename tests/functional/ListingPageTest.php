<?php

namespace Startplats\Tests\Functional;

class ListingPageTest extends BaseTestCase
{
    private static $listing_data1 = [
        "email" => "test@test.com",
        "subcategory_id" => null,
        "price" => "123",
        "quantity" => "2",
    ];

    private static $listing_data2 = [
        "email" => "test2@test.com",
        "subcategory_id" => null,
        "price" => "456",
        "quantity" => "3",
    ];

    private static $category = [
        "category_name" => "category_test"
    ];

    private static $subcategory = [
        "subcategory_name" => "subcategory_test",
        "category_id" => null
    ];

    public static function setUpBeforeClass(): void
    {
        $query = "INSERT INTO categories(category_name) VALUES(?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute(array_values(self::$category));
        $categoryId = self::$container['db']->lastInsertId();
        self::$category["id"] = $categoryId; // Set id so clearDatabaseOf() only removes one entry

        self::$subcategory["category_id"] = $categoryId;
        $query = "INSERT INTO subcategories(subcategory_name, category_id) VALUES(?, ?);";
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values(self::$subcategory));
        $subcategoryId = self::$container['db']->lastInsertId();

        self::$listing_data1["subcategory_id"] = $subcategoryId;
        self::$listing_data2["subcategory_id"] = $subcategoryId;
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
        self::clearDatabaseOf("listings", self::$listing_data1);
        self::clearDatabaseOf("listings", self::$listing_data2);
        self::clearDatabaseOf("subcategories", self::$subcategory);
        self::clearDatabaseOf("categories", self::$category);
    }

    /**
     * Verify correct html element are displayed when
     * viewing webpage on  '/listings/new'
     */
    public function testGETNewListing()
    {
        $response = $this->processRequest('GET', '/listings/new');
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        $this->assertStringContainsString('<input type="email" class="form-control" id="new_listing_email" placeholder="Enter email" name="email">', $htmlBody);
        $this->assertStringContainsString('<select class="form-control" id="new_listing_category" name="category_id" required>', $htmlBody);
        $this->assertStringContainsString('<select class="form-control" id="new_listing_category" name="subcategory_id" required>', $htmlBody);
        $this->assertStringContainsString('<input type="number" class="form-control" id="new_listing_amount" placeholder="Enter number of items" name="quantity" value=1 min="1">', $htmlBody);
        $this->assertStringContainsString('<button type="submit" class="btn btn-primary">Submit</button>', $htmlBody);
    }

    /**
     * Verify that values are correctly inserted into the database
     * when received as POST on '/listings/new'
     */
    public function testPOSTNewListing()
    {
        $response = $this->processRequest('POST', '/listings/new', self::$listing_data1);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertLogDoesNotContain(['ERROR']);
        $this->assertLogContains(["INFO: Parameters inserted"]);

        $this->verifyEntryInserted("listings", self::$listing_data1);
    }

    /**
     * Verify that multiple listings are displayed when
     * viewing webpage on  '/listings/listings/'
     */
    public function testGETAllListings()
    {
        $query = "INSERT INTO listings(email, subcategory_id, price, quantity) VALUES(?,?,?,?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute(array_values(self::$listing_data1));
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values(self::$listing_data2));

        $response = $this->processRequest('GET', '/listings/');
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        $unit_price1 = self::$listing_data1['price'] / self::$listing_data1['quantity'];
        $unit_price2 = self::$listing_data2['price'] / self::$listing_data2['quantity'];
        $this->assertStringContainsString($unit_price1, $htmlBody);
        $this->assertStringContainsString($unit_price2, $htmlBody);
        $this->assertStringContainsString(self::$subcategory['subcategory_name'], $htmlBody);
    }

    /**
     * Verify that a single listing are displayed when
     * viewing webpage on  '/listings/{id}/'
     */
    public function testGETSingleListing()
    {
        $query = "INSERT INTO listings(email, subcategory_id, price, quantity) VALUES(?,?,?,?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute(array_values(self::$listing_data1));
        $insertedId = self::$container['db']->lastInsertId();
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values(self::$listing_data2));

        $response = $this->processRequest('GET', "/listings/$insertedId");
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        $this->assertStringContainsString(self::$listing_data1['email'], $htmlBody);
        $this->assertStringContainsString(self::$listing_data1['price'], $htmlBody);
        $this->assertStringContainsString(self::$listing_data1['quantity'], $htmlBody);
        $this->assertStringContainsString(self::$subcategory['subcategory_name'], $htmlBody);
        $this->assertStringContainsString(self::$category['category_name'], $htmlBody);
        $this->assertStringNotContainsString(self::$listing_data2['email'], $htmlBody);
    }
}
