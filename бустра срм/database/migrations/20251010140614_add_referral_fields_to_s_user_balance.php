<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddReferralFieldsToSUserBalance extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('s_user_balance');

        if (!$table->hasColumn('referer_id')) {
            $table->addColumn('referer_id', 'string', [
                'limit' => 36,
                'null' => true,
                'default' => null,
                'comment' => 'Идентификатор реферера',
            ]);
        }

        if (!$table->hasColumn('referral_discount_amount')) {
            $table->addColumn('referral_discount_amount', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'null' => false,
                'default' => '0.00',
                'comment' => 'Баланс реферальных бонусов. Значение приходит из 1С, если бонусов нет = 0',
            ]);
        }

        $table->update();
    }

    public function down(): void
    {
        $table = $this->table('s_user_balance');

        if ($table->hasColumn('referral_discount_amount')) {
            $table->removeColumn('referral_discount_amount');
        }

        if ($table->hasColumn('referer_id')) {
            $table->removeColumn('referer_id');
        }

        $table->update();
    }
}
