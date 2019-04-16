<?php


use Phinx\Seed\AbstractSeed;

class Categories extends AbstractSeed
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
        $users = $this->table('categories');
        $users->truncate(); //Empty table to avoid duplicated users


        $data = $this->generateFakeData();
        $users->insert($data)->save();
    }
    
    private function generateCategories()
    {
        $data[] = [
                [
                    'category_name'      => "Löpning",
                ],[
                    'category_name'      => "Simning",
                ],[
                    'category_name'      => "Triahtlong",
                ],[
                    'category_name'      => "Cykling",
                ],[
                    'category_name'      => "Skidor",
                ]
            ];
    }
}
