<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAutodebitAndAutodebitChangedAtToB2pSbpAccountAndB2pCardsTables extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('b2p_sbp_accounts');
        if (!$table->hasColumn('autodebit') && !$table->hasColumn('autodebit_changed_at')) {
            $table->addColumn('autodebit', 'boolean', [
                'null' => false,
                'comment' => 'Можно ли списывать со счёта долг рекуррентом',
                'default' => true,
            ])->addColumn('autodebit_changed_at', 'datetime', [
                'null' => true,
                'comment' => 'Дата включения/отключения рекуррентов по счёту',
                'default' => false,
            ])->update();
        }

        $table = $this->table('b2p_cards');
        if (!$table->hasColumn('autodebit')) {
            $table->addColumn('autodebit_changed_at', 'datetime', [
                'null' => true,
                'comment' => 'Дата включения/отключения рекуррентов по карте',
                'default' => false,
            ])->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('b2p_sbp_accounts');
        if ($table->hasColumn('autodebit') && $table->hasColumn('autodebit_changed_at')) {
            $table
                ->removeColumn('autodebit')
                ->removeColumn('autodebit_changed_at')
                ->update();
        }

        $table = $this->table('b2p_cards');
        if ($table->hasColumn('autodebit_changed_at')) {
            $table
                ->removeColumn('autodebit_changed_at')
                ->update();
        }
    }
}
