<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: CreateVoxCallsArchiveMeta
 *
 * Creates the s_vox_calls_archive_meta table in the main database.
 * This table tracks metadata about archived call tables in the archive database.
 */
final class CreateVoxCallsArchiveMeta extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('s_vox_calls_archive_meta')) {
            $table = $this->table('s_vox_calls_archive_meta', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Metadata for archived Voximplant calls tables',
            ]);

            $table
                ->addColumn('id', 'biginteger', [
                    'null' => false,
                    'identity' => true,
                    'signed' => false,
                ])
                ->addColumn('table_name', 'string', [
                    'limit' => 64,
                    'null' => false,
                    'comment' => 'Name of the archived table (e.g., s_vox_calls_2025_12)',
                ])
                ->addColumn('year_month', 'string', [
                    'limit' => 7,
                    'null' => false,
                    'comment' => 'Year-month in YYYY-MM format',
                ])
                ->addColumn('records_count', 'biginteger', [
                    'null' => true,
                    'default' => null,
                    'signed' => false,
                    'comment' => 'Number of records in the table at rotation time',
                ])
                ->addColumn('min_datetime', 'datetime', [
                    'null' => true,
                    'default' => null,
                    'comment' => 'Earliest call datetime in the table',
                ])
                ->addColumn('max_datetime', 'datetime', [
                    'null' => true,
                    'default' => null,
                    'comment' => 'Latest call datetime in the table',
                ])
                ->addColumn('rotated_at', 'datetime', [
                    'null' => false,
                    'default' => 'CURRENT_TIMESTAMP',
                    'comment' => 'When the table was rotated/created',
                ])
                ->addColumn('expires_at', 'datetime', [
                    'null' => true,
                    'default' => null,
                    'comment' => 'When the table should be deleted (3 years after rotation)',
                ])
                ->addColumn('is_deleted', 'boolean', [
                    'null' => false,
                    'default' => false,
                    'comment' => 'Whether the table has been deleted during cleanup',
                ])
                ->addColumn('deleted_at', 'datetime', [
                    'null' => true,
                    'default' => null,
                    'comment' => 'When the table was deleted',
                ])
                ->addIndex(['table_name'], [
                    'unique' => true,
                    'name' => 'ux_vox_archive_meta_table_name',
                ])
                ->addIndex(['year_month'], [
                    'name' => 'ix_vox_archive_meta_year_month',
                ])
                ->addIndex(['expires_at', 'is_deleted'], [
                    'name' => 'ix_vox_archive_meta_expiry',
                ])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_vox_calls_archive_meta')) {
            $this->table('s_vox_calls_archive_meta')->drop()->save();
        }
    }
}
