<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStatusAndUsedeskTicketToComplaints extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('s_complaint');
        $needsUpdate = false;

        if (!$table->hasColumn('status')) {
            $table->addColumn('status', 'string', [
                'limit'   => 50,
                'null'    => false,
                'default' => 'pending',
                'comment' => 'Complaint processing status',
                'after'   => 'files',
            ]);
            $needsUpdate = true;
        }

        if (!$table->hasColumn('usedesk_ticket_id')) {
            $table->addColumn('usedesk_ticket_id', 'integer', [
                'null'    => true,
                'default' => null,
                'comment' => 'Linked Usedesk ticket ID',
                'after'   => 'status',
            ])->addIndex(
                ['usedesk_ticket_id'],
                ['name' => 'idx_complaint_usedesk_ticket']
            );
            $needsUpdate = true;
        }

        if ($needsUpdate) {
            $table->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('s_complaint');
        $needsSave = false;

        if ($table->hasIndex('idx_complaint_usedesk_ticket')) {
            $table->removeIndexByName('idx_complaint_usedesk_ticket');
            $needsSave = true;
        }

        if ($table->hasColumn('usedesk_ticket_id')) {
            $table->removeColumn('usedesk_ticket_id');
            $needsSave = true;
        }

        if ($table->hasColumn('status')) {
            $table->removeColumn('status');
            $needsSave = true;
        }

        if ($needsSave) {
            $table->save();
        }
    }
}