<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateVoxSiteDncTable extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('s_vox_site_dnc')) {
            $table = $this->table('s_vox_site_dnc', [
                'id' => 'id',
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'signed' => false,
            ]);

            $table
                ->addColumn('site_id', 'string', [
                    'limit' => 50,
                    'null' => false,
                    'comment' => 'Идентификатор сайта',
                ])
                ->addColumn('organization_id', 'integer', [
                    'signed' => false,
                    'null' => false,
                    'comment' => 'ID организации',
                ])
                ->addColumn('vox_domain', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'comment' => 'Домен Voximplant',
                ])
                ->addColumn('vox_token', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'comment' => 'Токен доступа Voximplant',
                ])
                ->addColumn('api_url', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'comment' => 'URL API Voximplant',
                ])
                ->addColumn('outgoing_calls_dnc_list_id', 'integer', [
                    'signed' => false,
                    'null' => true,
                    'comment' => 'ID DNC-листа для исходящих звонков робота',
                ])
                ->addColumn('is_active', 'integer', [
                    'limit' => 1,
                    'null' => false,
                    'default' => 1,
                    'comment' => '1 — активна, 0 — отключена',
                ])
                ->addColumn('comment', 'string', [
                    'limit' => 255,
                    'null' => true,
                    'comment' => 'Комментарий',
                ])
                ->addColumn('created_at', 'datetime', [
                    'null' => false,
                    'comment' => 'Дата создания',
                ])
                ->addColumn('updated_at', 'datetime', [
                    'null' => false,
                    'comment' => 'Дата обновления',
                ])
                ->addIndex(['site_id'], ['name' => 'idx_site_id'])
                ->addIndex(['site_id', 'organization_id'], [
                    'unique' => true,
                    'name' => 'idx_site_organization_unique',
                ])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_vox_site_dnc')) {
            $this->table('s_vox_site_dnc')->drop()->save();
        }
    }
}
