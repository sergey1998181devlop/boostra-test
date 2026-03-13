<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSiteIdIndexOnUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('s_users')
            ->addIndex(['site_id'], ['name' => 'idx_site_id'])
            ->update();
    }
}
