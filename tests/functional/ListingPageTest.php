<?php

namespace Startplats\Tests\Functional;

class ListingPageTest extends BaseTestCase
{
    private static $listing_data = [
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

    /**
     * Verify that multiple listings are displayed when
     * viewing webpage on  '/listings/listings/'
     */
    public function testGETAllListings()
    {
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description, created_at) VALUES(?,?,?,?,?,?,?);";
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

    /**
     * Verify that a single listing are displayed when
     * viewing webpage on  '/listings/{id}/'
     */
    public function testGETSingleListing()
    {
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description, created_at) VALUES(?,?,?,?,?,?,?);";
        $statement1 = self::$container['db']->prepare($query);
        $statement1->execute(array_values(self::$listing_data[0]));
        $inserted_id = self::$container['db']->lastInsertId();
        $statement2 = self::$container['db']->prepare($query);
        $statement2->execute(array_values(self::$listing_data[1]));

        $response = $this->processRequest('GET', "/listings/$inserted_id");
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
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description, created_at) VALUES(?,?,?,?,?,?,?);";
        $statement = self::$container['db']->prepare($query);
        $statement->execute(array_values(self::$listing_data[0]));
        $inserted_id = self::$container['db']->lastInsertId();

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
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description, created_at) VALUES(?,?,?,?,?,?,?);";
        $statement = self::$container['db']->prepare($query);
        $statement->execute(array_values(self::$listing_data[0]));
        $inserted_id = self::$container['db']->lastInsertId();

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
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description, created_at) VALUES(?,?,?,?,?,?,?);";
        $statement = self::$container['db']->prepare($query);
        $statement->execute(array_values(self::$listing_data[0]));
        $inserted_id = self::$container['db']->lastInsertId();

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
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description, created_at) VALUES(?,?,?,?,?,?,?);";
        $statement = self::$container['db']->prepare($query);
        $statement->execute(array_values(self::$listing_data[0]));
        $inserted_id = self::$container['db']->lastInsertId();

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
        $this->assertLogContains(["INFO: Sending email from: " . $email_from . " to: " . self::$listing_data[0]['email']]);
        $this->assertStringContainsString("Your E-mail was sent.", $htmlBody);
    }
}
