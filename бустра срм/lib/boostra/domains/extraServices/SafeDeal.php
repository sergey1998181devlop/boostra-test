<?php

namespace boostra\domains\extraServices;

/**
 * @property bool $transaction_id
 * @property string date_edit
 * @property string return_transaction_slug
 * @property string action_type
 */
class SafeDeal extends extraService
{

    public static function table(): string
    {
        return 's_safe_deal';
    }

    public function init()
    {
        // @todo Это должно храниться в базе данных и быть подключено через внешний ключ к таблице доп.услуги
        $this->slug = 'safe_deal';
        $this->return_slug = 'return_safe_deal';
        $this->title = 'Безопасная сделка';
        $this->description = 'Предметом оказания Услуги является предоставление Заемщику доступа к защищенной онлайн-платформе «Безопасная сделка» на маркетплейсе soyaplace.ru, в рамках которой обеспечивается:
                                • Безопасный выбор предложений (офферов) от партнерских микрофинансовых организаций (МФО);
                                • Защищенная процедура согласования и фиксации условий договора займа без возможности их одностороннего изменения МФО на этапе акцепта;
                                • Технологически обеспеченное заключение договора займа с выбранной МФО;
                                • Информационно-технологическое взаимодействие с оператором по переводу денежных средств для выдачи займа и уплаты комиссии Сервиса.
                            ';
        $this->return_transaction_slug = 'REFUND_SAFE_DEAL';

        parent::init();
    }

    public function isActive(): bool
    {
        return !$this->fully_refunded &&
            $this->status === 'SUCCESS';
    }
}
