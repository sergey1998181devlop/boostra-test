<?php

require_once 'View.php';

/**
 * Class AutoReturnAdditionalServices
 * Класс для работы с автовозвратами доп услуг
 */
class AutoRefundAdditionalServicesView extends View
{
    /**
     * @throws Exception
     */
    public function fetch()
    {
        return $this->design->fetch('auto_refund_additional_services.tpl');
    }
}
