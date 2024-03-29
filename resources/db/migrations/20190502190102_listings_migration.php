<?php


use Phinx\Migration\AbstractMigration;

class ListingsMigration extends AbstractMigration
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
        $listings = $this->table('listings');
        $listings->addColumn('email', 'string', ['limit' => 256])
            ->addColumn('subcategory_id', 'integer')
            ->addColumn('unit_price', 'decimal', ['signed' => true])
            ->addColumn('quantity', 'integer')
            ->addColumn('removal_code', 'string', ['limit' => 6, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('description', 'text')
            ->addColumn('title', 'string', ['limit' => 40])
            ->addForeignKey('subcategory_id', 'subcategories', 'id')
            ->create();
    }
}
