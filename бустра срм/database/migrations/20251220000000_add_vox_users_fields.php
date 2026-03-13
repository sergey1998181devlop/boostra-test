<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddVoxUsersFields extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('s_vox_users')) {
            $table = $this->table('s_vox_users');

            if (!$table->hasColumn('email')) {
                $table
                    ->addColumn('email', 'string', ['limit' => 255, 'null' => true, 'after' => 'vox_user_id'])
                    ->addIndex(['email'], ['name' => 'ix_s_vox_users_email'])
                    ->update();
            }

            if (!$table->hasColumn('is_call_analysis')) {
                $table
                    ->addColumn('is_call_analysis', 'boolean', ['null' => false, 'default' => false, 'after' => 'full_name'])
                    ->addIndex(['is_call_analysis'], ['name' => 'ix_s_vox_users_is_call_analysis'])
                    ->update();
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_vox_users')) {
            $table = $this->table('s_vox_users');

            if ($table->hasIndex('ix_s_vox_users_is_call_analysis')) {
                $table->removeIndex('ix_s_vox_users_is_call_analysis');
            }

            if ($table->hasIndex('ix_s_vox_users_email')) {
                $table->removeIndex('ix_s_vox_users_email');
            }

            if ($table->hasColumn('is_call_analysis')) {
                $table->removeColumn('is_call_analysis');
            }

            if ($table->hasColumn('email')) {
                $table->removeColumn('email');
            }

            $table->update();
        }
    }
}
