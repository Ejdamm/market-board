<?php


use Phinx\Seed\AbstractSeed;

class SubCategoriesSeed extends AbstractSeed
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
        $users = $this->table('subcategories');
        $users->truncate(); //Empty table to avoid duplicates


        $data = $this->generateConstantData();
        $users->insert($data)->save();
    }

    private function generateConstantData()
    {
        return [
            ['subcategory_name' => 'Göteborgsvarvet', 'category_id' => 1],
            ['subcategory_name' => 'Lidingöloppet', 'category_id' => 1],
            ['subcategory_name' => 'Vasaloppet', 'category_id' => 5],
            ['subcategory_name' => 'Vätternrundan', 'category_id' => 4],
            ['subcategory_name' => 'Vansbrosimningen', 'category_id' => 2]
        ];
    }
}
