<?php


use Faker\Factory;
use Phinx\Seed\AbstractSeed;
use MarketBoard\Utils;

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
        $listings = $this->table('listings');
        $listings->truncate(); //Empty table to avoid duplicates


        $data = $this->generateFakeData();
        $this->execute('SET foreign_key_checks=0');
        $listings->insert($data)->save();
        $this->execute('SET foreign_key_checks=1');
    }

    private function generateFakeData()
    {
        $faker = Factory::create();
        $data = [];

        for ($i = 0; $i < 100; $i++) {
            $data[] = [
                'email' => $faker->email,
                'subcategory_id' => $faker->numberBetween(1, 5),
                'unit_price' => $faker->numberBetween(100, 1000),
                'quantity' =>  $faker->numberBetween(1, 2),
                'removal_code' => Utils::generate_removal_code(),
                'description' => $faker->text(100),
                'created_at' => $faker->date($format = 'Y-m-d H:i:s', $max = 'now'),
            ];
        }

        return $data;
    }
}
