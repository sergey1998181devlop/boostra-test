<?php

use boostra\domains\Card;
use boostra\domains\extraServices\extraService;
use boostra\domains\Manager;
use boostra\services\Core;
use boostra\repositories\Repository;
use boostra\services\extraServices;

require_once './AjaxController.php';

/**
 * Contains validation rules and handlers
 */
class RefuseFromService extends AjaxController{
    
    private $refund_percent;
    private $service_type;
    private $service_id;
    private $card_id;
    
    /**
     * @var extraService
     */
    private  $service;
    
    /**
     * @var Card
     */
    private $card;
    
    public function actions(): array
    {
        return [
            'refund_extra_service' => [
                'card_id'      => 'integer',
                'service_id'   => 'integer',
                'return_size'  => [ 'half', 'all', 'seventy_five', 'twenty_five' ],
                'service'      => [ 'multipolis', 'credit_doctor', 'tv_medical','star_oracle', 'safe_deal'],
                'return_type'  => [ 'card', 'sbp' ],
                'sbp_account_id' => 'integer'
            ],
        ];
    }
    
    /**
     * Init properties depends on input data
     *
     * @return void
     * @throws Exception
     */
    protected function init(): void
    {
        $return_percentages = [
            'all' => 100,
            'half' => 50,
            'seventy_five' => 75,
            'twenty_five' => 25,
        ];

        if(isset($return_percentages[$this->data['return_size']])){
            $this->refund_percent = $return_percentages[$this->data['return_size']];
        } else {
            throw new \Exception('Неправильный размер возврата');
        }
        
        $this->service_type   = $this->data['service']      ?? null;
        $this->service_id     = $this->data['service_id']   ?? null;
        $this->card_id        = $this->data['card_id']      ?? null;
        $this->service        = ( new Repository( extraServices::convertEntityNameToClassname( $this->service_type, 'extraServices' ) ) )
                                  ->read( ['id' => $this->service_id ] );
        $this->card = $this->card_id
            ? (new Repository(Card::class))->read(['id' => $this->card_id])
            : null;

        $manager = (new Repository(Manager::class))->read(['id' => $this->manager->id]);

        if (!$manager || $manager->blocked == 1) {
            throw new \Exception("Менеджер заблокирован, возврат невозможен.");
        }

        if (strtoupper($this->service->status) !== 'SUCCESS') {
            throw new \Exception("Дополнительная услуга не в статусе 'SUCCESS', возврат невозможен.");
        }

        if ($this->card && $this->card->id) {
            if ($this->card->user_id !== $this->service->user_id) {
                throw new \Exception("Карта не принадлежит пользователю.");
            }
        }
        
        if( ! $this->service->id ){
            throw new \Exception("Не удалось найти дополнительную услугу типа '{$this->service_type}");
        }
        
        if( ! isset( $this->manager->id ) ){
            throw new \Exception('Менеджер неизвестен');
        }
    }
    
    /**
     *
     * @return array
     * @throws Exception
     */
    public function actionRefundExtraService(): array
    {
        $this->service->return_by_manager_id = $this->manager->id;
        
        $services = new extraServices();

        $secondReturnText = $this->service->return_status === 2 ? ' оставшейся части' : '';

        $refund_method = $this->data['return_type'];
        
        $services->refund(
            $this->service,
            $this->refund_percent,
            $this->card,
            $refund_method,
            $this->data['sbp_account_id'] ?? null
        );
        
        Core::instance()->comments->add_comment( [
            'manager_id' => $this->manager->id,
            'user_id'    => $this->service->user->id,
            'order_id'   => $this->service->order->id,
            'block'      => 'return_extra_service',
            'text'       => "Возврат {$secondReturnText} {$this->service->title} от {$this->service->date_added} (Дата услуги) при продлении",
            'created'    => date( 'Y-m-d H:i:s' ),
        ] )
            && Core::instance()->soap->send_comment( [
                'manager' => $this->manager->name_1c,
                'text'    => "Возврат {$secondReturnText} {$this->service->title} от {$this->service->date_added} (Дата услуги) при продлении",
                'created' => date( 'Y-m-d H:i:s' ),
                'number'  => $this->service->order->id_1c,
            ] );

        $this->message = 'Отказ оформлен, возврат поступит в течение 3-х рабочих дней.';

        Core::instance()->order_data->set($this->service->order->id, Core::instance()->order_data::PAYMENT_DEFERMENT);

        return [];
    }
}

new RefuseFromService;
