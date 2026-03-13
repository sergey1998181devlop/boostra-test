<?php

require_once 'View.php';

/*
 * Class ArbitrationAgreementsGeneratorView
 * Класс для работы c созданием арбитражных соглашений
 */
class ArbitrationAgreementsGeneratorView extends View
{
    /**
     * @throws Exception
     */
    public function fetch()
    {
        return $this->design->fetch('arbitration_agreements_generator.tpl');
    }
}
