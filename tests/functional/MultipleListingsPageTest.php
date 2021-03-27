<?php

namespace MarketBoard\Tests\Functional;

class MultipleListingsPageTest extends BaseTestCase
{
    /**
     * Verify that multiple listings are displayed when
     * viewing webpage on  '/listings/'
     */
    public function testGETAllListings()
    {
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description, title, created_at, type) VALUES(?,?,?,?,?,?,?,?,?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute(array_values(self::$listing_data[0]));
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values(self::$listing_data[1]));

        $response = $this->processRequest('GET', '/listings/');
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertLogDoesNotContain(['ERROR']);

        $htmlBody = (string)$response->getBody();

        // Verify input fields
        $this->assertStringContainsString(self::$listing_data[0]['unit_price'], $htmlBody);
        $this->assertStringContainsString(self::$listing_data[1]['unit_price'], $htmlBody);
        $this->assertStringContainsString(self::$subcategory['subcategory_name'], $htmlBody);
    }
}
