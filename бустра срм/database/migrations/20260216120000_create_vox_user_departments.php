<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateVoxUserDepartments extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('s_vox_user_departments')) {
            $table = $this->table('s_vox_user_departments', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table
                ->addColumn('id', 'biginteger', ['null' => false, 'identity' => true, 'signed' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->create();
        }

        if ($this->hasTable('s_vox_users')) {
            $users = $this->table('s_vox_users');

            if (!$users->hasColumn('department_id')) {
                $users
                    ->addColumn('department_id', 'biginteger', [
                        'null' => true,
                        'default' => null,
                        'signed' => false,
                        'after' => 'is_call_analysis',
                    ])
                    ->addIndex(['department_id'], ['name' => 'ix_s_vox_users_department_id'])
                    ->update();
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_vox_users')) {
            $users = $this->table('s_vox_users');

            if ($users->hasIndex(['department_id'])) {
                $users->removeIndex(['department_id']);
            }
            if ($users->hasColumn('department_id')) {
                $users->removeColumn('department_id');
            }

            $users->update();
        }

        if ($this->hasTable('s_vox_user_departments')) {
            $this->table('s_vox_user_departments')->drop()->save();
        }
    }
}
