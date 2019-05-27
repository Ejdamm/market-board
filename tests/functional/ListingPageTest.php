<?php

namespace Startplats\Tests\Functional;

class ListingPageTest extends BaseTestCase
{
    private static $listing_data1 = [
        "email" => "test@test.com",
        "subcategory_id" => 1,
        "price" => "123",
        "quantity" => "123",
    ];

    private static $listing_data2 = [
        "email" => "test2@test.com",
        "subcategory_id" => 1,
        "price" => "456",
        "quantity" => "456",
    ];


    /**
     * Verify correct html element are displayed when
     * viewing webpage on  '/listings/new'
     */
    public function testGETNewListing()
    {
        $baseTest = new BaseTestCase();
        $baseTest->clearLog();
        $response = $baseTest->runApp('GET', '/listings/new');
        $this->assertEquals(200, $response->getStatusCode());

        $baseTest->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        $this->assertStringContainsString('<input type="email" class="form-control" id="new_listing_email" placeholder="Enter email" name="email">', $htmlBody);
        $this->assertStringContainsString('<select class="form-control" id="new_listing_category" name="category_id" required>', $htmlBody);
        $this->assertStringContainsString('<select class="form-control" id="new_listing_category" name="subcategory_id" required>', $htmlBody);
        $this->assertStringContainsString('<input type="text" class="form-control" id="new_listing_amount" placeholder="Enter number of items" name="quantity">', $htmlBody);
        $this->assertStringContainsString('<button type="submit" class="btn btn-primary">Submit</button>', $htmlBody);

        $baseTest->clearLog();
    }

    /**
     * Verify that values are correctly inserted into the database
     * when received as POST on '/listings/new'
     */
    public function testPOSTNewListing()
    {
        $baseTest = new BaseTestCase();
        $baseTest->clearLog();

        $response = $baseTest->runApp('POST', '/listings/new', self::$listing_data1);
        $this->assertEquals(200, $response->getStatusCode());

        $baseTest->assertLogDoesNotContain(['ERROR']);
        $baseTest->assertLogContains(["INFO: Parameters inserted"]);

        $baseTest->verifyEntryInserted("listings", self::$listing_data1);

        $baseTest->clearLog();
        $baseTest->clearDatabaseOf("listings", self::$listing_data1);
    }

    /**
     * Verify that multiple listings are displayed when
     * viewing webpage on  '/listings/listings/'
     */
    public function testGETAllListings()
    {
        $baseTest = new BaseTestCase();
        $baseTest->clearLog();

        $query = "INSERT INTO listings(email, subcategory_id, price, quantity) VALUES(?,?,?,?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute(array_values(self::$listing_data1));
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values(self::$listing_data2));

        $response = $baseTest->runApp('GET', '/listings/');
        $this->assertEquals(200, $response->getStatusCode());

        $baseTest->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        foreach (self::$listing_data1 as $value) {
            $this->assertStringContainsString($value, $htmlBody);
        }
        foreach (self::$listing_data2 as $value) {
            $this->assertStringContainsString($value, $htmlBody);
        }

        $baseTest->clearLog();
        $baseTest->clearDatabaseOf("listings", self::$listing_data1);
        $baseTest->clearDatabaseOf("listings", self::$listing_data2);
    }

    /**
     * Verify that a single listing are displayed when
     * viewing webpage on  '/listings/{id}/'
     */
    public function testGETSingleListing()
    {
        $baseTest = new BaseTestCase();
        $baseTest->clearLog();

        $query = "INSERT INTO listings(email, subcategory_id, price, quantity) VALUES(?,?,?,?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute(array_values(self::$listing_data1));
        $insertedId = self::$container['db']->lastInsertId();
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values(self::$listing_data2));

        $response = $baseTest->runApp('GET', "/listings/$insertedId");
        $this->assertEquals(200, $response->getStatusCode());

        $baseTest->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        foreach (self::$listing_data1 as $value) {
            $this->assertStringContainsString($value, $htmlBody);
        }
        $this->assertStringNotContainsString(self::$listing_data2['email'], $htmlBody);

        $baseTest->clearLog();
        $baseTest->clearDatabaseOf("listings", self::$listing_data1);
        $baseTest->clearDatabaseOf("listings", self::$listing_data2);
    }
}
