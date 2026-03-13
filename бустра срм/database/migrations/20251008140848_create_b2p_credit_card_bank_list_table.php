<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateB2pCreditCardBankListTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('b2p_credit_card_bank_list', [
            'id' => false,
            'primary_key' => [],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb3',
            'collation' => 'utf8mb3_general_ci',
            'comment' => 'Таблица для сопоставления банков и имени банка с колбека Б2П',
        ]);

        $table->addColumn('b2p_bank_id', 'string', [
            'limit' => 50,
            'null' => true,
            'comment' => 'ID банка из таблицы b2p_bank_list',
        ])
            ->addColumn('bank_name', 'string', [
                'limit' => 512,
                'null' => false,
                'comment' => 'Название банка из колбека Б2П',
            ])
            ->addColumn('has_sbp', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'null' => false,
                'default' => 0,
                'comment' => 'Поддерживает ли банк СБП',
            ])
            ->addIndex(['bank_name'], [
                'name' => 'b2p_credit_card_bank_list_bank_name_index',
                'unique' => true,
            ])
            ->addIndex(['has_sbp'], [
                'name' => 'b2p_credit_card_bank_list_has_sbp_index',
            ])
            ->addForeignKey('b2p_bank_id', 'b2p_bank_list', 'id', [
                'constraint' => 'b2p_credit_card_bank_list_b2p_bank_list_id_fk',
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->table('b2p_credit_card_bank_list')->drop()->save();
    }
}
