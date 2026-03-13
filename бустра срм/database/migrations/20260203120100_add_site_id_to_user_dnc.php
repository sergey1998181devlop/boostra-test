<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSiteIdToUserDnc extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('s_user_dnc');
        if (!$table->hasColumn('site_id')) {
            $table
                ->addColumn('site_id', 'string', [
                    'limit' => 50,
                    'null' => true,
                    'default' => null,
                    'comment' => 'Идентификатор сайта',
                ])
                ->addIndex(['user_id', 'site_id', 'date_end'], [
                    'name' => 'idx_user_site_date_end',
                ])
                ->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('s_user_dnc');
        if ($table->hasColumn('site_id')) {
            $table
                ->removeIndexByName('idx_user_site_date_end')
                ->removeColumn('site_id')
                ->update();
        }
    }
}
