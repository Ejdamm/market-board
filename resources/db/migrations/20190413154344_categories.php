<?php


use Phinx\Migration\AbstractMigration;

class Categories extends AbstractMigration
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
     * rollback the migration.a
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $exists = $this->hasTable('categories');

        if ($exists) {
            $categories = $this->table('categories', ['id' => false, 'primary_key' => ['category_id']]);
            $categories
                    ->addColumn('category_id', 'integer', ['identity' =>true, 'signed' => false])
                    ->addColumn('category_name', 'string', ['limit' => 128])
                    ->create();
        }

    }

    // public function down()
    // {
    //      $this->table('categories')->drop();
    // }
}
