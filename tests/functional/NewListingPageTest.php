<?php


namespace MarketBoard\Tests\Functional;

class NewListingPageTest extends BaseTestCase
{
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
        $this->assertStringContainsString('<input type="email" class="form-control" id="new_listing_email" value="" placeholder="Enter your E-mail" name="email" required>', $htmlBody);
    }

    /**
     * Verify that values are correctly inserted into the database
     * when received as POST on '/listings/new'
     */
    public function testPOSTNewListing()
    {
        $listing_data = self::$listing_data[0];
        unset($listing_data['removal_code']);
        unset($listing_data['created_at']);
        $listing_data['captcha'] = self::$container['session']->get('captcha');
        $response = $this->processRequest('POST', '/listings/new', $listing_data);
        unset($listing_data['captcha']);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertLogDoesNotContain(['ERROR']);
        $this->assertLogContains(["INFO: Parameters inserted"]);

        $this->verifyEntryInserted("listings", $listing_data);
    }
}
