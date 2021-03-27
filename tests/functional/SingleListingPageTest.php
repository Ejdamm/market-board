<?php


namespace MarketBoard\Tests\Functional;

class SingleListingPageTest extends BaseTestCase
{
    /**
     * Verify that a single listing are displayed when
     * viewing webpage on  '/listings/{id}/'
     */
    public function testGETSingleListing()
    {
        $inserted_id1 = $this->insertListing(self::$listing_data[0]);
        $inserted_id2 = $this->insertListing(self::$listing_data[1]);

        $response = $this->processRequest('GET', "/listings/$inserted_id1");
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        $this->assertStringContainsString(self::$listing_data[0]['unit_price'], $htmlBody);
        $this->assertStringContainsString(self::$listing_data[0]['quantity'], $htmlBody);
        $this->assertStringContainsString(self::$subcategory['subcategory_name'], $htmlBody);
        $this->assertStringContainsString(self::$category['category_name'], $htmlBody);
        $this->assertStringNotContainsString(self::$listing_data[0]['email'], $htmlBody);
    }

    /**
     * Verify that a single listing are displayed when
     * viewing webpage on  '/listings/{id}/'
     */
    public function testGETNonExistingSingleListing()
    {
        $response = $this->processRequest('GET', "/listings/-1");
        $this->assertEquals(404, $response->getStatusCode());

        $this->assertLogDoesNotContain(['ERROR']);
        $this->assertLogContains(['404']);
    }

    /**
     * Verify that the listing is removed from database
     * when received as POST on '/listings/{id}'
     */
    public function testRemoveSingleListing()
    {
        $inserted_id = $this->insertListing(self::$listing_data[0]);

        $post_params = [
            'removal_code' => self::$listing_data[0]['removal_code'],
            'removal_form' => null
        ];

        $response = $this->processRequest('POST', "/listings/$inserted_id", $post_params);
        $htmlBody = (string)$response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLogDoesNotContain(['ERROR']);
        $this->assertLogContains(["INFO: Listing removed"]);
        $this->assertStringContainsString("The listing was successfully removed", $htmlBody);
        $this->verifyEntryRemoved("listings", $inserted_id);
    }

    /**
     * Verify that no listing is removed when received as POST on '/listings/{id} with a non-existing id'
     */
    public function testFailedRemoveSingleListing()
    {
        $inserted_id = $this->insertListing(self::$listing_data[0]);

        $post_params = [
            'removal_code' => self::$listing_data[0]['removal_code'],
            'removal_form' => null
        ];

        $wrong_id = $inserted_id + 1;
        $response = $this->processRequest('POST', "/listings/$wrong_id", $post_params);
        $htmlBody = (string)$response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLogDoesNotContain(['ERROR']);
        $this->assertLogContains(["WARNING: Listing was not removed: $wrong_id"]);
        $this->assertStringContainsString("Something went wrong and the listing was not removed.", $htmlBody);
        $this->verifyEntryInserted("listings", self::$listing_data[0]);
    }

    /**
     * Verify that no listing is removed when received as POST on '/listings/{id} with wrong removal code'
     */
    public function testWrongRemovalCode()
    {
        $inserted_id = $this->insertListing(self::$listing_data[0]);

        $post_params = [
            'removal_code' => "BBBBBB",
            'removal_form' => null
        ];

        $response = $this->processRequest('POST', "/listings/$inserted_id", $post_params);
        $htmlBody = (string)$response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLogDoesNotContain(['ERROR']);
        $this->assertLogContains(["WARNING: Listing was not removed: $inserted_id"]);
        $this->assertStringContainsString("Something went wrong and the listing was not removed.", $htmlBody);
        $this->verifyEntryInserted("listings", self::$listing_data[0]);
    }

    /**
     * Verify that mail is sent
     * when received as POST on '/listings/{id}'
     */
    public function testSendMail()
    {
        $inserted_id = $this->insertListing(self::$listing_data[0]);

        $email_from = "hillary.clinton@us.gov";
        $post_params = [
            'email_from' => $email_from,
            'email_text' => "Greetings stranger",
            'email_form' => null,
            'captcha' => self::$container['session']->get('captcha'),
        ];

        $response = $this->processRequest('POST', "/listings/$inserted_id", $post_params);
        $htmlBody = (string)$response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLogDoesNotContain(['ERROR']);
        $this->assertStringContainsString("Your E-mail was sent.", $htmlBody);
    }

    private function insertListing($listingData)
    {
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description, title, created_at, type) VALUES(?,?,?,?,?,?,?,?,?);";
        $statement = self::$container['db']->prepare($query);
        $statement->execute(array_values($listingData));
        $inserted_id = self::$container['db']->lastInsertId();
        return $inserted_id;
    }
}
