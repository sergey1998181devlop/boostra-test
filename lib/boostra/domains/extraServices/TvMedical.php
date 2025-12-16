<?php

namespace boostra\domains\extraServices;

/**
 * @property int    payment_id
 * @property int    tv_medical_id
 * @property bool   sent_to_api
 * @property string date_modified
 * @property int    return
 *
 */
class TvMedical extends extraService{
    
    public const ACTIVE_DAYS_PERIOD = 16;
    
    public static function table(): string
    {
        return 's_tv_medical_payments';
    }
    
    public function init()
    {
        // @todo Это должно храниться в базе данных и быть подключено через внешний ключ к таблице доп.услуги
        $this->slug              = 'tv_medical';
        $this->return_slug       = 'return_tv_medical';
        $this->title             = 'Вита-мед';
        $this->description       = 'Уникальная программа поддержки клиентов в разных жизненных ситуациях, Приобретая услугу «Вита-мед» заемщик получает возможность использовать консультации профессиональных специалистов в медицины, в том числе медицины узкой направленности. Имущественное благо заключается в стоимости предоставляемых услуг, которая значительно ниже рыночной на аналогичные услуги при приобретении их отдельно.';

        method_exists( parent::class, 'init') && parent::init();
    }
    
    public function isActive(): bool
    {
        return ! $this->fully_refunded &&
               $this->status === 'SUCCESS' &&
               strtotime( $this->date_added ) + self::ACTIVE_DAYS_PERIOD * 24 * 60 * 60 > time();
    }
}