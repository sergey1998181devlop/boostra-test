<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUseInTicketsToOrganizations extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('s_organizations');

        if (!$table->hasColumn('use_in_tickets')) {
            $table->addColumn('use_in_tickets', 'boolean', [
                'default' => false,
                'null' => false,
                'comment' => 'Показывать организацию при создании тикета',
            ])->update();

            // Сразу отметим те организации, которые должны отображаться при создании тикета
            $this->execute("UPDATE s_organizations SET use_in_tickets = 1 WHERE id IN (1, 6, 7, 11, 15, 12, 14, 13)");
        }
    }

    public function down(): void
    {
        $table = $this->table('s_organizations');

        if ($table->hasColumn('use_in_tickets')) {
            $table->removeColumn('use_in_tickets')->update();
        }
    }
}