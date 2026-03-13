<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddReferenceToB2pP2pcredits extends AbstractMigration
{
    public function up(): void
    {
        $this->table('b2p_p2pcredits')
            ->addColumn('reference', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'operation_id',
            ])
            ->addIndex(['reference'], ['name' => 'idx_reference'])
            ->update();
    }

    public function down(): void
    {
        $this->table('b2p_p2pcredits')
            ->removeIndex(['reference'])
            ->removeColumn('reference')
            ->update();
    }
}
