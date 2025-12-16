<?php
/**
 * All events for mobile push
 * @author Jewish Programmer
 */

class PushToken extends Simpla {

    /**
     * @var object $order
     */
    protected $order;

    /**
     * @var object $user
     */
    protected $user;

    private const PUSH_METHOD = 'push/send';
    private const PUSH_STOP_METHOD = 'stop_push';
    private const PUSH_TYPE = 'mobile';

    private const CLOSED = '6.Закрыт';
    private const CONFIRMED = '5.Выдан';
    private const APPROVED = '3.Одобрено';
    private const REJECTED = '2.Отказано';

    /**
     * Add tasks for queue
     * @param object $order
     * @param $status
     * @return void
     */
    public function addTasks(object $order, $status): void
    {
        if (!$this->hasToken($order->user_id)) {
            return;
        }
        $this->order = $order;
        $this->user = $this->users->get_user($order->user_id);
        switch ($status) {
            case self::REJECTED:
                $this->addRejectedTasks();
                break;
            case self::APPROVED:
                $this->addApprovedTasks();
                break;
            case self::CONFIRMED:
                $this->addConfirmedTasks();
                break;
            case self::CLOSED:
                $this->addClosedTasks();
                break;
        }
    }

    /**
     * Check token has of user
     * @param int $userId
     * @return int
     */
    protected function hasToken(int $userId): bool
    {
        $url = $this->config->mobile_push_api_url . 'push/check-token?user_id=' . $userId;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Api-Key: ' . $this->config->mobile_push_api_key
            ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);

        if ($response === false) {
            curl_close($ch);
            return false; // в случае ошибки считаем, что токена нет
        }

        curl_close($ch);

        $data = json_decode($response, true);

        // API возвращает { "result": true/false }
        if (isset($data['result']) && $data['result'] === true) {
            return true;
        }

        return false;
    }

    /**
     * Add only rejected tasks to cron
     * @return void
     */
    protected function addRejectedTasks(): void
    {
        $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'title' => 'По заявке отказано',
            'body' => 'По Вашей заявке отказано, но вы можете получить заём у наших партнёров!',
            'user_id' => $this->order->user_id
        ]);
    }

    /**
     * Add only approved tasks to cron
     * @return void
     */
    protected function addApprovedTasks(): void
    {
        $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'title' => 'По заявке одобрено',
            'body' => "Вам одобрено {$this->order->amount} рублей. Успейте забрать!",
            'user_id' => $this->order->user_id]);

        $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'title' => 'По заявке одобрено',
            'body' => "Вам ещё нужны {$this->order->amount} рублей? Деньги ждут!",
            'user_id' => $this->order->user_id
        ], 'approved', date('Y-m-d 12:00:00', strtotime('+1day')));

        $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'title' => 'По заявке одобрено',
            'body' => "{$this->user->firstname}, завтра срок одобрения истекает. Успейте забрать свои {$this->order->amount} рублей!",
            'user_id' => $this->order->user_id
        ], 'approved', date('Y-m-d 12:00:00', strtotime('+6days')));

        $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'title' => 'По заявке одобрено',
            'body' => 'Деньги сгорят СЕГОДНЯ! Успейте забрать!',
            'user_id' => $this->order->user_id
        ], 'approved', date('Y-m-d 12:00:00', strtotime('+7days')));
    }

    /**
     * Add only confirmed tasks to cron
     * @return void
     */
    protected function addConfirmedTasks(): void
    {
        $this->queue->add(self::PUSH_STOP_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'tag' => 'approved',
            'user_id' => $this->order->user_id
        ], 'approved');
        foreach ([10, 5, 1] as $day) {
            if ($this->order->period > $day) {
                $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
                    'title' => 'Выдан заём под 0%. Договор действует',
                    'body' => 'Закрой действующий договор сейчас и возьми 25 000!',
                    'user_id' => $this->order->user_id
                ], 'confirmed', date('Y-m-d 13:00:00', strtotime('+' . ($this->order->period - $day) . 'days')));
            }
        }
        $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'title' => 'Договор действует. До даты платежа, указанной в договоре займа 1 день.',
            'body' => "{$this->user->firstname}, завтра оплата по договору займа",
            'user_id' => $this->order->user_id
        ], 'confirmed', date('Y-m-d 10:00:00', strtotime('+' . ($this->order->period - 1) . 'days')));

        $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'title' => 'Договор действует. День платежа',
            'body' => 'Сегодня оплата по договору займа',
            'user_id' => $this->order->user_id
        ], 'confirmed', date('Y-m-d 10:00:00', strtotime('+' . $this->order->period . 'days')));

        $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'title' => 'Договор действует. День платежа',
            'body' => 'Не забудьте совершить оплату по договору займа',
            'user_id' => $this->order->user_id
        ], 'confirmed', date('Y-m-d 13:00:00', strtotime('+' . $this->order->period . 'days')));

        $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'title' => 'Договор действует. День платежа',
            'body' => 'Не допускайте просрочку! Закройте договор займа или перенесите дату платежа, оформив продление',
            'user_id' => $this->order->user_id
        ], 'confirmed', date('Y-m-d 19:00:00', strtotime('+' . $this->order->period . 'days')));

        foreach (range(1, 8) as $day) {
            $this->queue->add(self::PUSH_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
                'title' => 'Договор просрочен',
                'body' => 'У Вас имеется задолженность! Внесите минимальный платеж!',
                'user_id' => $this->order->user_id
            ], 'confirmed', date('Y-m-d 11:00:00', strtotime('+' . ($this->order->period + $day) . 'days')));
        }
    }

    /**
     * Add only closed tasks to cron
     * @return void
     */
    protected function addClosedTasks(): void
    {
        $this->queue->add(self::PUSH_STOP_METHOD, $this->config->mobile_push_api_url, self::PUSH_TYPE, [
            'tag' => 'confirmed',
            'user_id' => $this->order->user_id
        ], 'confirmed');
    }
}