<?php


use Phinx\Seed\AbstractSeed;

class CategoriesSeed extends AbstractSeed
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
        $this->execute('SET foreign_key_checks=0');
        $users->truncate(); //Empty table to avoid duplicates
        $this->execute('SET foreign_key_checks=1');

        $data = $this->generateConstantData();
        $users->insert($data)->save();
    }
    
    private function generateConstantData()
    {
        return [
            ['category_name' => 'Löpning'],
            ['category_name' => 'Simning'],
            ['category_name' => 'Triathlon'],
            ['category_name' => 'Cykling'],
            ['category_name' => 'Skidor']
        ];
    }
}
