<?php

use Phinx\Migration\AbstractMigration;

class Language extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $users = $this->table('language', ['id' => false, 'primary_key' => 'language']);
        $users->addColumn('language', 'string')
            ->addColumn('title', 'string', ['default' => 'Market Board'])
            ->addColumn('subcategory', 'string', ['default' => 'Subcategory'])
            ->addColumn('category', 'string', ['default' => 'Category'])
            ->addColumn('all_categories', 'string', ['default' => 'All categories'])
            ->addColumn('all_subcategories', 'string', ['default' => 'All subcategories'])
            ->addColumn('filter', 'string', ['default' => 'Filter'])
            ->addColumn('new', 'string', ['default' => 'New'])
            ->addColumn('unit_price', 'string', ['default' => 'Unit price'])
            ->addColumn('created', 'string', ['default' => 'Created'])
            ->addColumn('first', 'string', ['default' => 'First'])
            ->addColumn('previous', 'string', ['default' => 'Previous'])
            ->addColumn('next', 'string', ['default' => 'Next'])
            ->addColumn('last', 'string', ['default' => 'Last'])
            ->addColumn('quantity', 'string', ['default' => 'Quantity'])
            ->addColumn('description', 'string', ['default' => 'Description'])
            ->addColumn('contact_the_seller', 'string', ['default' => 'Contact the seller'])
            ->addColumn('enter_your_email', 'string', ['default' => 'Enter your E-mail'])
            ->addColumn('write_your_message_here', 'string', ['default' => 'Write your message here'])
            ->addColumn('send', 'string', ['default' => 'Send'])
            ->addColumn('are_you_the_seller', 'string', ['default' => 'Are you the seller?'])
            ->addColumn('remove', 'string', ['default' => 'Remove'])
            ->addColumn('removal_code', 'string', ['default' => 'Removal code'])
            ->addColumn('email_address', 'string', ['default' => 'Email address'])
            ->addColumn('choose_category', 'string', ['default' => 'Choose category'])
            ->addColumn('choose_subcategory', 'string', ['default' => 'Choose subcategory'])
            ->addColumn('enter_unit_price', 'string', ['default' => 'Enter unit price'])
            ->addColumn('enter_quantity', 'string', ['default' => 'Enter number of items'])
            ->addColumn('create_listing', 'string', ['default' => 'Create listing'])
            ->addColumn('new_listing_inserted_message', 'string', ['default' => 'New listing inserted. It can be found'])
            ->addColumn('here', 'string', ['default' => 'here'])
            ->addColumn('listing_removed_success', 'string', ['default' => 'The listing was successfully removed.'])
            ->addColumn('listing_removed_failed', 'string', ['default' => 'Something went wrong and the listing was not removed. Please try again later or contact admin.'])
            ->addColumn('go_back_to_start', 'string', ['default' => 'Go back to start page.'])
            ->addColumn('go_back_to_listing', 'string', ['default' => 'Go back to the listing.'])
            ->addColumn('internal_server_error', 'string', ['default' => 'An internal server error occurred. Please try again later.'])
            ->addColumn('success', 'string', ['default' => 'Success!'])
            ->addColumn('warning', 'string', ['default' => 'Warning!'])
            ->addColumn('your_email_was_sent', 'string', ['default' => 'Your E-mail was sent.'])
            ->addColumn('enter_captcha', 'string', ['default' => 'Enter the captcha code'])
            ->addColumn('captcha', 'string', ['default' => 'Captcha'])
            ->addColumn('wrong_captcha', 'string', ['default' => 'Input does not match with captcha code.'])
            ->create();
    }
}
