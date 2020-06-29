<?php


use Phinx\Seed\AbstractSeed;

class DefaultLanguage extends AbstractSeed
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
        $table = $this->table('language');
        $this->execute('DELETE FROM language WHERE language_code = \'default\'');
        $data = $this->generateConstantData();
        $table->insert($data)->save();
    }

    private function generateConstantData()
    {
        return [
            [
                'language_code' => 'default',
                'subcategory' => 'Subcategory',
                'category' => 'Category',
            ]
        ];
    }
}
