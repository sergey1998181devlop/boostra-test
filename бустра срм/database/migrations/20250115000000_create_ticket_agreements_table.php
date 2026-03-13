<?php

use Phinx\Migration\AbstractMigration;

class CreateTicketAgreementsTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('s_mytickets_agreements', [
            'id' => true,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci'
        ]);

        $table->addColumn('ticket_id', 'integer', ['null' => false, 'comment' => 'ID тикета'])
              ->addColumn('agreement_date', 'date', ['null' => false, 'comment' => 'Дата договоренности'])
              ->addColumn('note', 'text', ['null' => true, 'comment' => 'Суть договоренностей'])
              ->addColumn('created_by', 'integer', ['null' => false, 'comment' => 'ID менеджера, создавшего договоренность'])
              ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Дата создания'])
              ->addColumn('processed_at', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Дата обработки (создания копии)'])
              ->addIndex(['ticket_id'], ['name' => 'idx_agreement_ticket'])
              ->addIndex(['agreement_date'], ['name' => 'idx_agreement_date'])
              ->addIndex(['processed_at'], ['name' => 'idx_processed_at'])
              ->addIndex(['created_by'], ['name' => 'idx_created_by'])
              ->create();

        $statusTable = $this->table('s_mytickets_statuses');
        $statusTable->insert([
            'id' => 10,
            'name' => 'Достигнуты договоренности',
            'color' => '#FFA500'
        ])->saveData();
    }

    public function down(): void
    {
        $statusTable = $this->table('s_mytickets_statuses');
        $statusTable->getAdapter()->execute("DELETE FROM s_mytickets_statuses WHERE id = 10");

        $this->table('s_mytickets_agreements')->drop()->save();
    }
}
