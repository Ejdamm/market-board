<?php

use Phinx\Migration\AbstractMigration;

class BuyingMigration extends AbstractMigration
{
    public function up()
    {
        $listings = $this->table('listings');
        $listings->addColumn('type', 'string', ['limit' => 8, 'default' => 'selling'])
            ->save();

        $language = $this->table('language');
        $language->renameColumn('contact_the_seller', 'contact_the_advertiser')
            ->renameColumn('are_you_the_seller', 'are_you_the_advertiser')
            ->renameColumn('email_contact_seller_subject', 'email_contact_advertiser_subject')
            ->changeColumn('contact_the_advertiser', 'string', ['default' => 'Contact the advertiser'])
            ->changeColumn('are_you_the_advertiser', 'string', ['default' => 'Are you the advertiser?'])
            ->addColumn('selling', 'string', ['default' => 'Selling'])
            ->addColumn('buying', 'string', ['default' => 'Buying'])
            ->addColumn('new_listing', 'string', ['default' => 'New listing'])
            ->save();
    }

    public function down()
    {
        $listings = $this->table('listings');
        $listings->removeColumn('type')
            ->save();

        $language = $this->table('language');
        $language->renameColumn('contact_the_advertiser', 'contact_the_seller')
            ->renameColumn('are_you_the_advertiser', 'are_you_the_seller')
            ->renameColumn('email_contact_advertiser_subject', 'email_contact_seller_subject')
            ->changeColumn('contact_the_seller', 'string', ['default' => 'Contact the seller'])
            ->changeColumn('are_you_the_seller', 'string', ['default' => 'Are you the seller?'])
            ->removeColumn('selling')
            ->removeColumn('buying')
            ->removeColumn('new_listing')
            ->save();
    }
}
