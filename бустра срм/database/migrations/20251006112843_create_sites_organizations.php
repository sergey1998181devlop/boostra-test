<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSitesOrganizations extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        if (!$this->hasTable('s_sites_organizations')) {
            $table = $this->table('s_sites_organizations', [
                'id' => 'id',
                'primary_key' => ['id'],
                'engine'      => 'InnoDB',
                'encoding'    => 'utf8mb4',
                'collation'   => 'utf8mb4_unicode_ci',
                'signed'      => false,
            ]);

            $table->addColumn('site_id', 'string', [
                'limit' => 20,
                'null'   => false,
                'default' => 'boostra',
                'comment'=> 'ID сайта',
                'collation' => 'utf8mb4_unicode_ci'
            ]);
            $table->addColumn('organization_id', 'integer', [
                'signed' => false,
                'null'   => false,
                'comment'=> 'ID организации',
            ])
            ->create();

            $table->addForeignKey('organization_id', 's_organizations', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ]);

            $table->update();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_sites_organizations')) {
            $table = $this->table('s_sites_organizations');
            $table->drop();
        }
    }
}
