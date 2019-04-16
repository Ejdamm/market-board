<?php


use Phinx\Migration\AbstractMigration;

class SubCategories extends AbstractMigration
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
    // public function change()
    // {

    // }

    public function change()
    {
        $exist = $this->hasTable('subcategories');
        if (!$exist) {


        $subcategories = $this->table('subcategories', ['id' => false, 'primary_key' => ['subcategory_id']]);
        $subcategories
                ->addColumn('subcategory_id', 'integer', ['identity' =>true, 'signed' => false])
                ->addColumn('subcategory_name', 'string', ['limit' => 128])
                ->addColumn('category_id', 'integer', ['signed' => false])

                //Hm maybe this should be added after the table is created?
                ->addForeignKey('category_id', 'categories', 'category_id', array('delete'=> 'SET_NULL', 'update'=> 'NO_ACTION'))
                ->create();
            }
    }

    // public function down()
    // {
    //      $this->table('subcategories')->drop();
    // }
}
