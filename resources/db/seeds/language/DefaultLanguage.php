<?php


use Phinx\Seed\AbstractSeed;

class DefaultLanguage extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $table = $this->table('language');
        $this->execute('DELETE FROM language WHERE language = \'default\'');
        $data = $this->generateConstantData();
        $table->insert($data)->save();
    }

    private function generateConstantData()
    {
        return [
            [
                'language' => 'default',
                'title' => 'Market Board',
                'listings' => 'Listings',
                'subcategory' => 'Subcategory',
                'category' => 'Category',
                'listing_title' => 'Title',
                'all_categories' => 'All categories',
                'all_subcategories' => 'All subcategories',
                'filter' => 'Filter',
                'new' => 'New',
                'refresh' => 'Refresh',
                'unit_price' => 'Unit price',
                'created' => 'Created',
                'first' => 'First',
                'previous' => 'Previous',
                'next' => 'Next',
                'last' => 'Last',
                'quantity' => 'Quantity',
                'description' => 'Description',
                'contact_the_advertiser' => 'Contact the advertiser',
                'enter_your_email' => 'Enter your E-mail',
                'enter_title' => 'Enter a title',
                'write_your_message_here' => 'Write your message here',
                'send' => 'Send',
                'are_you_the_advertiser' => 'Are you the advertiser?',
                'remove' => 'Remove',
                'removal_code' => 'Removal code',
                'email_address' => 'Email address',
                'choose_category' => 'Choose category',
                'choose_subcategory' => 'Choose subcategory',
                'enter_unit_price' => 'Enter unit price',
                'enter_quantity' => 'Enter number of items',
                'create_listing' => 'Create listing',
                'new_listing_inserted_message' => 'New listing inserted. It can be found',
                'here' => 'here',
                'listing_removed_success' => 'The listing was successfully removed.',
                'listing_removed_failed' => 'Something went wrong and the listing was not removed. Please try again later or contact admin.',
                'go_back_to_start' => 'Go back to start page.',
                'go_back_to_listing' => 'Go back to the listing.',
                'success' => 'Success!',
                'warning' => 'Warning!',
                'your_email_was_sent' => 'Your E-mail was sent.',
                'enter_captcha' => 'Enter the captcha code',
                'captcha' => 'Captcha',
                'wrong_captcha' => 'Input does not match with captcha code',
                'no_listings' => 'There are currently no listings for this category.',
                'return_to_homepage' => 'Return to the homepage.',
                'error404_title' => '404 Not Found',
                'error404_text' => 'The page you are looking for could not be found. Check the address bar to ensure your URL is spelled correctly.',
                'error500_title' => '500 Internal Server Error',
                'error500_text' => 'An internal server error occurred. Please try again later.',
                'error501_title' => '501 Not Implemented',
                'error501_text' => 'The functionality you tried to use is not yet implemented.',
                'email_contact_advertiser_subject' => 'You have got a message from Market Board',
                'email_new_listing_subject' => 'Market Board - Your removal code',
                'footer_contact' => 'Contact',
                'footer_made_with' => 'Made with Market Board',
                'selling' => 'Selling',
                'buying' => 'Buying',
                'new_listing' => 'New listing',
            ]
        ];
    }
}
