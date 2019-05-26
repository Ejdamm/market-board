<?php


use Phinx\Seed\AbstractSeed;

class ListingsSeed extends AbstractSeed
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
        $users = $this->table('listings');
        $users->truncate(); //Empty table to avoid duplicates


        $data = $this->generateConstantData();
        $this->execute('SET foreign_key_checks=0');
        $users->insert($data)->save();
        $this->execute('SET foreign_key_checks=1');
    }

    private function generateConstantData()
    {
        return [
            [
                'email' => 'nisse.hult@test.com',
                'subcategory_id' => 1,
                'price' => 21,
                'quantity' => 1,
                'removal_code' => 'AAAAA'
            ]
        ];
    }
}
