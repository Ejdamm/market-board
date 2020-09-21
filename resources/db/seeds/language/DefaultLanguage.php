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
                'subcategory' => 'Subcategory',
                'category' => 'Category',
                'all_categories' => 'All categories',
                'all_subcategories' => 'All subcategories',
                'filter' => 'Filter',
                'new' => 'New',
                'unit_price' => 'Unit price',
                'created' => 'Created',
                'first' => 'First',
                'previous' => 'Previous',
                'next' => 'Next',
                'last' => 'Last',
                'quantity' => 'Quantity',
                'description' => 'Description',
                'contact_the_seller' => 'Contact the seller',
                'enter_your_email' => 'Enter your E-mail',
                'write_your_message_here' => 'Write your message here',
                'send' => 'Send',
                'are_you_the_seller' => 'Are you the seller?',
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
                'internal_server_error' => 'An internal server error occurred. Please try again later.',
                'success' => 'Success!',
                'warning' => 'Warning!',
                'your_email_was_sent' => 'Your E-mail was sent.',
                'no_listings' => 'There are currently no listings for this category.',
            ]
        ];
    }
}
