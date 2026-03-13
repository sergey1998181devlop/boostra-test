<?php

namespace boostra\domains\extraServices;

/**
 * @property bool   $transaction_id
 * @property string date_edit
 * @property string return_transaction_slug
 * @property string action_type
 */
class StarOracle extends extraService{

    public static function table(): string
    {
        return 's_star_oracle';
    }
    
    public function init()
    {
        // @todo Это должно храниться в базе данных и быть подключено через внешний ключ к таблице доп.услуги
        $this->slug                    = 'star_oracle';
        $this->return_slug             = 'return_star_oracle';
        $this->title                   = 'Звездный Оракул';
        $this->description             = 'Программы для ЭВМ «Звездный Оракул» размещена на сайте https://staroracle.ru, а так-же доступна посредством
                                          мессенджера Telegram t.me/Soracle_Bot. ПО объединяет возможности нейросетей для расшифровки сновидений,
                                          раскладов Таро, составления натальных карт и гороскопов. Нейросеть анализирует символы и образы, встречающиеся в
                                          сновидениях и предлагает интерпретации на основе обширной базы данных, включающей психологические и
                                          культурные аспекты. Программа автоматически выполняет расклады Таро, интерпретируя карты с учетом их значений
                                          и взаимосвязей. Используя данные о дате, времени и месте рождения, программа строит натальные карты и
                                          предоставляет астрологические прогнозы. ';
        $this->return_transaction_slug = 'REFUND_STAR_ORACLE';
        
        method_exists( parent::class, 'init') && parent::init();
    }

    public function isActive(): bool
    {
        return ! $this->fully_refunded &&
               $this->status === 'SUCCESS';
    }
}
