<?php

namespace Startplats\Tests\Functional;

class ListingPageTest extends BaseTestCase
{
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
        $this->assertStringContainsString('<input type="text" class="form-control" id="new_listing_category"', $htmlBody);
        $this->assertStringContainsString('<input type="text" class="form-control" id="new_listing_subcategory" placeholder="Enter subcategory" name="subcategory">', $htmlBody);
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
        $data = [
            "email" => "test@test.com",
            "category" => "testcategory",
            "subcategory" => "testsubcategory",
            "price" => "123",
            "quantity" => "123",
            ];

        $response = $baseTest->runApp('POST', '/listings/new', $data);
        $this->assertEquals(200, $response->getStatusCode());

        $baseTest->assertLogDoesNotContain(['ERROR']);
        $baseTest->assertLogContains(["INFO: Parameters inserted"]);

        $baseTest->verifyEntryInserted("listings", $data);

        $baseTest->clearLog();
        $baseTest->clearDatabaseOf("listings", $data);
    }

    /**
     * Verify that multiple listings are displayed when
     * viewing webpage on  '/listings/listings/'
     */
    public function testGETAllListings()
    {
        $baseTest = new BaseTestCase();
        $baseTest->clearLog();

        $data1 = [
            "email" => "test1@test.com",
            "category" => "testcategory",
            "subcategory" => "testsubcategory",
            "price" => "123",
            "quantity" => "123",
        ];
        $data2 = [
            "email" => "test2@test.com",
            "category" => "testcategory",
            "subcategory" => "testsubcategory",
            "price" => "456",
            "quantity" => "456",
        ];

        // TODO Use POST /listings/new instead of "manually" inserting data?
        $query = "INSERT INTO listings(email, category, subcategory, price, quantity) VALUES(?,?,?,?,?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute(array_values($data1));
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values($data2));

        $response = $baseTest->runApp('GET', '/listings/');
        $this->assertEquals(200, $response->getStatusCode());

        $baseTest->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        foreach ($data1 as $value) {
            $this->assertStringContainsString($value, $htmlBody);
        }
        foreach ($data2 as $value) {
            $this->assertStringContainsString($value, $htmlBody);
        }

        $baseTest->clearLog();
        $baseTest->clearDatabaseOf("listings", $data1);
        $baseTest->clearDatabaseOf("listings", $data2);
    }

    /**
     * Verify that a single listing are displayed when
     * viewing webpage on  '/listings/{id}/'
     */
    public function testGETSingleListing()
    {
        $baseTest = new BaseTestCase();
        $baseTest->clearLog();

        $data1 = [
            "email" => "test1@test.com",
            "category" => "testcategory",
            "subcategory" => "testsubcategory",
            "price" => "123",
            "quantity" => "123",
        ];
        $data2 = [
            "email" => "test2@test.com",
            "category" => "testcategory",
            "subcategory" => "testsubcategory",
            "price" => "456",
            "quantity" => "456",
        ];

        // TODO Use POST /listings/new instead of "manually" inserting data?
        $query = "INSERT INTO listings(email, category, subcategory, price, quantity) VALUES(?,?,?,?,?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute(array_values($data1));
        $insertedId = self::$container['db']->lastInsertId();
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values($data2));

        $response = $baseTest->runApp('GET', "/listings/$insertedId");
        $this->assertEquals(200, $response->getStatusCode());

        $baseTest->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        foreach ($data1 as $value) {
            $this->assertStringContainsString($value, $htmlBody);
        }
        $this->assertStringNotContainsString($data2['email'], $htmlBody);

        $baseTest->clearLog();
        $baseTest->clearDatabaseOf("listings", $data1);
        $baseTest->clearDatabaseOf("listings", $data2);
    }
}
