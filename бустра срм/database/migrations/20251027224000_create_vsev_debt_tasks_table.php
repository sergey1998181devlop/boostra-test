<?php

use Phinx\Migration\AbstractMigration;

class CreateVsevDebtTasksTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('s_vsev_debt_tasks', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['identity' => true])
            ->addColumn('filename', 'string', ['limit' => 255])
            ->addColumn('original_filename', 'string', ['limit' => 255])
            ->addColumn('status', 'enum', ['values' => ['pending', 'processing', 'completed', 'error'], 'default' => 'pending'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('log', 'text', ['null' => true])
            ->addColumn('last_processed_row', 'integer', ['default' => 0])
            ->create();
    }
}