<?php


use Phinx\Seed\AbstractSeed;

class UserSeed extends AbstractSeed
{

    /**
     * Defines the variable which is a foreign key dependencies.
     */
    public function getDependencies()
    {
        return [
            'id',
        ];
    }


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
        $users = $this->table('users');
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

    private function generateConstantData()
    {
        $data = [
            [
                'email'    => 'test@test.com',
                'username'    => 'test',
                'password'    => 'test',
                'active'    => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ], [
                'email'    => 'test2@test.com',
                'username'    => 'test2',
                'password'    => 'test2',
                'active'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];

        return $data;
    }
}
