<?php

namespace Startplats\Tests\Functional;

class ListingPageTest extends BaseTestCase
{
    /**
     * Verify correct html element are displayed when
     * viewing webpage on  '/listings/new'
     */
    public function testGETListing()
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
     * when recieved as POST on '/listings/new'
     */
    public function testPOSTListing()
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

        $baseTest->verifyEntryInserted("listings",$data);

        $baseTest->clearLog();
        $baseTest->clearDatabaseOf("listings", $data);
    }
}
