<?php

namespace boostra\domains\extraServices;

/**
 * @property int    payment_id
 * @property string number
 * @property int    is_sent
 */
class Multipolis extends extraService{
    
    public const ACTIVE_DAYS_PERIOD = 16;
    
    public function init()
    {
        // @todo Это должно храниться в базе данных и быть подключено через внешний ключ к таблице доп.услуги
        $this->slug              = 'multipolis';
        $this->return_slug       = 'return_multipolis';
        $this->title             = 'Консьерж сервис';
        $this->description       = 'Уникальная программа поддержки клиентов в разных жизненных ситуациях, консультации в области права, финансов, налогооблажений и психологии. Приобретая услугу «Консьерж сервис» заемщик получает возможность использовать консультации профессиональных специалистов в области права, финансов, налогооблажения и психологии. Имущественное благо заключается в стоимости предоставляемых услуг по Консьерж сервису, которая значительно ниже рыночной на аналогичные услуги при приобретении их отдельно.';
        
        method_exists( parent::class, 'init') && parent::init();
    }
    
    public static function table(): string
    {
        return 's_multipolis';
    }

    public function isActive(): bool
    {
        return ! $this->fully_refunded &&
               $this->status === 'SUCCESS' &&
               strtotime( $this->date_added ) + self::ACTIVE_DAYS_PERIOD * 24 * 60 * 60 > time();
    }
}