<?php
use Phinx\Migration\AbstractMigration;

final class AddIsResendToScorings extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('s_scorings')) {
            $table = $this->table('s_scorings');

            if (!$table->hasColumn('is_resend')) {
                $table->addColumn('is_resend', 'boolean', [
                    'default' => 0,
                    'null'    => false,
                    'after'   => 'end_date',
                ]);
            }

            $table->update();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_scorings')) {
            $table = $this->table('s_scorings');

            if ($table->hasColumn('is_resend')) {
                $table->removeColumn('is_resend');
            }

            $table->update();
        }
    }
}
