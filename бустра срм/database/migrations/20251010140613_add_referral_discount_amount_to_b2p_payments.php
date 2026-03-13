<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddReferralDiscountAmountToB2pPayments extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('b2p_payments');

        if (!$table->hasColumn('referral_discount_amount')) {
            $table->addColumn('referral_discount_amount', 'integer', [
                'null' => true,
                'default' => '0',
                'comment' => 'Сумма реферальной скидки',
                'after' => 'discount_amount',
            ])->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('b2p_payments');

        if ($table->hasColumn('referral_discount_amount')) {
            $table->removeColumn('referral_discount_amount')->update();
        }
    }
}
