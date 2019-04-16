<?php


use Phinx\Seed\AbstractSeed;

class SubCategories extends AbstractSeed
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
        $users->truncate(); //Empty table to avoid duplicated users


        $data = $this->generateFakeData();
        $users->insert($data)->save();
    }

    private function generateFakeData()
    {
        $faker = Faker\Factory::create();
        $data = [];

        for ($i = 0; $i < 100; $i++) {
            $data[] = [
                'username'      => $faker->userName,
                'password'      => sha1($faker->password),
                //'password_salt' => sha1('foo'), // Guess we need this later
                'email'         => $faker->email,
                'created_at'       => date('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }
}
