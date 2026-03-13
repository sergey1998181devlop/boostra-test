<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Creates s_sites table to manage landing pages/sites in the CRM.
 *
 * This table serves as the central registry of all sites/landing pages,
 * allowing per-site configuration and management.
 */
final class CreateSitesTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('s_sites', [
            'id' => false,
            'primary_key' => ['site_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => 'Реестр сайтов/лендингов в CRM'
        ]);

        $table
            ->addColumn('site_id', 'string', [
                'limit' => 20,
                'null' => false,
                'comment' => 'Уникальный идентификатор сайта (slug для URL и отображения: boostra, soyaplace, rubl)'
            ])
            ->addColumn('title', 'string', [
                'limit' => 100,
                'null' => false,
                'comment' => 'Название сайта для отображения в CRM'
            ])
            ->addColumn('domain', 'string', [
                'limit' => 255,
                'null' => false,
                'comment' => 'Домен сайта для ссылок в SMS и переходов в ЛК'
            ])
            ->addColumn('is_active', 'boolean', [
                'null' => false,
                'default' => true,
                'comment' => 'Активен ли сайт (1 - да, 0 - нет)'
            ])
            ->addIndex(['is_active'], [
                'name' => 'idx_is_active'
            ])
            ->addIndex(['domain'], [
                'name' => 'idx_domain'
            ])
            ->create();

        $sites = [
            ['site_id' => 'boostra', 'title' => 'Бустра', 'domain' => 'boostra.ru', 'is_active' => 1],
            ['site_id' => 'soyaplace', 'title' => 'Соя Плейс', 'domain' => 'soyaplace.ru', 'is_active' => 1],
            ['site_id' => 'rubl', 'title' => 'Рубль', 'domain' => 'rubl.ru', 'is_active' => 1],
        ];

        $this->table('s_sites')->insert($sites)->save();

        $sitesOrganizationsTable = $this->table('s_sites_organizations');
        $sitesOrganizationsTable->addForeignKey('site_id', 's_sites', 'site_id', [
            'delete' => 'RESTRICT',
            'update' => 'RESTRICT'
        ])
        ->update();
    }

    public function down(): void
    {
        $sitesOrganizationsTable = $this->table('s_sites_organizations');
        if ($sitesOrganizationsTable->hasForeignKey('site_id')) {
            $sitesOrganizationsTable->dropForeignKey('site_id')->update();
        }

        $this->table('s_sites')->drop()->save();
    }
}