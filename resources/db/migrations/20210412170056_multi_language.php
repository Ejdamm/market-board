<?php

use Phinx\Migration\AbstractMigration;

class MultiLanguage extends AbstractMigration
{
    public function up()
    {
        $language = $this->table('language');
        $language->addColumn('code', 'string', ['limit' => 8])
            ->save();

        $language->changePrimaryKey(['code'])
            ->save();
    }

    public function down()
    {
        $language = $this->table('language');
        $language->changePrimaryKey(['language'])
            ->save();

        $language->removeColumn('code')
            ->save();
    }
}
