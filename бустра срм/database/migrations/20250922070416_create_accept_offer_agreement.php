<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAcceptOfferAgreement extends AbstractMigration
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
    public function change(): void
    {
        if ($this->hasTable('s_document_types')) {

//            Добавляет новый документ - 'Соглашение_об_акцепте_оферты_6_1'
            $this->execute("
                INSERT INTO s_document_types(type, template, name, client_visible) VALUES 
                ('OFFER_AGREEMENT', 'accept_offer_aggreement.tpl', 'Соглашение_об_акцепте_оферты_6_1', 1)
            ");
        }
    }
}
