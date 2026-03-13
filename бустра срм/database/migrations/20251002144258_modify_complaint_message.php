<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ModifyComplaintMessage extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('s_complaint');

        if ($table->hasColumn('message')) {
            $table->changeColumn('message', 'text', [
                'null' => false,
            ])->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('s_complaint');

        if ($table->hasColumn('message')) {
            $table->changeColumn('message', 'string', [
                'limit' => 100,
                'null'  => false,
            ])->update();
        }
    }
}
