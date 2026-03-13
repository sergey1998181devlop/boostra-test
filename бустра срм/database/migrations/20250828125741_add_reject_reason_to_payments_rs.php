<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRejectReasonToPaymentsRs extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('s_payments_rs');

        $table->addColumn('reject_reason', 'string', [
            'limit' => 50,
            'null' => true,
            'comment' => 'Причина отклонения платежа',
            'after' => 'status',
        ]);

        $table->update();
    }

    public function down(): void
    {
        $table = $this->table('s_payments_rs');
        $table->removeColumn('reject_reason');
        $table->update();
    }
}