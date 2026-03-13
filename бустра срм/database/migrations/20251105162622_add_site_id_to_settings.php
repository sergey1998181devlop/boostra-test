<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Adds site_id column to s_settings table to support per-landing page settings.
 *
 * Structure:
 * - site_id IS NULL: global settings (shared across all landing pages)
 * - site_id = 'boostra': settings specific to 'boostra' landing page
 * - site_id = 'soyaplace': settings specific to 'soyaplace' landing page, etc.
 */
final class AddSiteIdToSettings extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('s_settings');

        if (!$table->hasColumn('site_id')) {
            $table->addColumn('site_id', 'string', [
                'limit' => 20,
                'null' => true,
                'default' => null,
                'comment' => 'ID сайта из s_sites (NULL = глобальные)',
                'after' => 'setting_id',
                'collation' => 'utf8mb4_unicode_ci'
            ])
            ->update();

            if ($table->hasIndex(['name'])) {
                $table->removeIndex(['name']);
            }

            $table->addIndex(['name', 'site_id'], [
                'unique' => true,
                'name' => 'idx_name_site_unique'
            ])
            ->update();

            $table->addIndex(['site_id'], [
                'name' => 'idx_site_id'
            ])
            ->update();

            $table->addForeignKey('site_id', 's_sites', 'site_id', [
                'delete' => 'RESTRICT',
                'update' => 'RESTRICT'
            ])
            ->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('s_settings');

        if ($table->hasColumn('site_id')) {
            if ($table->hasForeignKey('site_id')) {
                $table->dropForeignKey('site_id');
            }

            if ($table->hasIndex(['name', 'site_id'])) {
                $table->removeIndex(['name', 'site_id']);
            }
            if ($table->hasIndex(['site_id'])) {
                $table->removeIndex(['site_id']);
            }

            $table->removeColumn('site_id')
                ->update();

            $table->addIndex(['name'], [
                'unique' => true,
                'name' => 'idx_name_unique'
            ])
            ->update();
        }
    }
}