<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateVoxReportTables extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('s_vox_users')) {
            $table = $this->table('s_vox_users', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false])
                ->addColumn('vox_user_id', 'biginteger', ['null' => false, 'signed' => false])
                ->addColumn('full_name', 'string', ['limit' => 255, 'null' => false])
                ->addIndex(['vox_user_id'], ['unique' => true, 'name' => 'ux_s_vox_users_vox_user_id'])
                ->addIndex(['full_name'], ['name' => 'ix_s_vox_users_full_name'])
                ->create();
        }

        if (!$this->hasTable('s_vox_queues')) {
            $table = $this->table('s_vox_queues', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false])
                ->addColumn('vox_queue_id', 'biginteger', ['null' => false, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('enabled_for_report', 'boolean', ['null' => false, 'default' => false])
                ->addIndex(['vox_queue_id'], ['unique' => true, 'name' => 'ux_s_vox_queues_vox_queue_id'])
                ->addIndex(['title'], ['name' => 'ix_s_vox_queues_title'])
                ->addIndex(['enabled_for_report'], ['name' => 'ix_s_vox_queues_enabled'])
                ->create();
        }

        if ($this->hasTable('s_vox_calls')) {
            $calls = $this->table('s_vox_calls');

            if (!$calls->hasColumn('queue_id')) {
                $calls->addColumn('queue_id', 'biginteger', ['null' => true, 'default' => null, 'signed' => false]);
            }
            if (!$calls->hasColumn('vox_user_id')) {
                $calls->addColumn('vox_user_id', 'biginteger', ['null' => true, 'default' => null, 'signed' => false]);
            }
            if (!$calls->hasColumn('record_url')) {
                $calls->addColumn('record_url', 'string', ['limit' => 1024, 'null' => true, 'default' => null]);
            }
            if (!$calls->hasColumn('assessment')) {
                $calls->addColumn('assessment', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'signed' => false]);
            }

            if (!$calls->hasIndex(['datetime_start'])) {
                $calls->addIndex(['datetime_start'], ['name' => 'ix_s_vox_calls_datetime_start']);
            }
            if (!$calls->hasIndex(['vox_user_id', 'datetime_start'])) {
                $calls->addIndex(['vox_user_id', 'datetime_start'], ['name' => 'ix_s_vox_calls_vox_user_dt']);
            }
            if (!$calls->hasIndex(['assessment', 'vox_user_id', 'datetime_start'])) {
                $calls->addIndex(['assessment', 'vox_user_id', 'datetime_start'], ['name' => 'ix_s_vox_calls_assessment_user_dt']);
            }
            if (!$calls->hasIndex(['queue_id', 'datetime_start'])) {
                $calls->addIndex(['queue_id', 'datetime_start'], ['name' => 'ix_s_vox_calls_queue_dt']);
            }

            $calls->update();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_vox_calls')) {
            $calls = $this->table('s_vox_calls');

            if ($calls->hasIndex(['queue_id', 'datetime_start'])) {
                $calls->removeIndex(['queue_id', 'datetime_start']);
            }

            if ($calls->hasColumn('queue_id')) {
                $calls->removeColumn('queue_id');
            }
            if ($calls->hasIndex(['assessment', 'vox_user_id', 'datetime_start'])) {
                $calls->removeIndex(['assessment', 'vox_user_id', 'datetime_start']);
            }
            if ($calls->hasIndex(['vox_user_id', 'datetime_start'])) {
                $calls->removeIndex(['vox_user_id', 'datetime_start']);
            }
            if ($calls->hasIndex(['datetime_start'])) {
                $calls->removeIndex(['datetime_start']);
            }

            if ($calls->hasColumn('assessment')) {
                $calls->removeColumn('assessment');
            }
            if ($calls->hasColumn('record_url')) {
                $calls->removeColumn('record_url');
            }
            if ($calls->hasColumn('vox_user_id')) {
                $calls->removeColumn('vox_user_id');
            }

            $calls->update();
        }

        if ($this->hasTable('s_vox_users')) {
            $this->table('s_vox_users')->drop()->save();
        }
        if ($this->hasTable('s_vox_queues')) {
            $this->table('s_vox_queues')->drop()->save();
        }
    }
}
