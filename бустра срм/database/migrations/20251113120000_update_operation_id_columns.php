<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateOperationIdColumns extends AbstractMigration
{
    public function up(): void
    {
        $this->table('s_service_return_requests')
            ->changeColumn('operation_id', 'biginteger', [
                'null'   => false,
                'signed' => false,
            ])
            ->changeColumn('status', 'enum', [
                'values'  => ['new', 'sent', 'approved', 'rejected', 'error'],
                'null'    => false,
                'default' => 'new',
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('s_service_return_requests')
            ->changeColumn('operation_id', 'integer', [
                'null' => false,
            ])
            ->changeColumn('status', 'enum', [
                'values'  => ['sent', 'approved', 'rejected', 'error'],
                'null'    => false,
                'default' => 'approved',
            ])
            ->update();
    }
}