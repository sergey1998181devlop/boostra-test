<?php

require_once './ajaxController.php';
require_once '../api/Scorings.php';

/**
 * AJAX файл вызывается перед привязкой карты и на основании уже запущенных скорингов принимает решение о том,
 * нужно ли продать карту клиента партнёрам.
 *
 * Критерий продажи - НК клиент **И**:
 * - Клиент найден в чс
 * - Отказ по возрасту
 * - Отказ по региону
 * - Банкрот (ЕФРСБ скоринг)
 * - Отказ по скористе и акси одновременно
 *
 * Клиенты, которых мы решаем продать, получают ссылку на привязку карты не на нашем сайте.
 *
 * Если клиенту всё одобрено - пропускаем его на привязку карты на нашем сайте.
 *
 * Если один из скорингов выдал ошибку - пропускаем клиента на привязку карты на нашем сайте.
 *
 * Если клиент сидит на экране ожидания решения более 1.5 минуты - пропускаем клиента на привязку карты на нашем сайте.
 * (Например, если какой-то из скорингов долго тормозит, но не упал в ошибку).
 *
 * Признак проданности клиента партнёру - ключ `is_rejected_nk` в `user_data`:
 * - `null` - Решение ещё не принималось (В том числе для старых клиентов).
 * - `0` - Клиент хороший, с ним работаем.
 * - `1` - Клиент может быть продан партнёрам, с ним не работаем.
 *
 * ```
 * $is_good_guy = $this->user_data->read($user_id, 'is_rejected_nk') == 0;
 * ```
 *
 * Часть потока проходит мимо флоу с продажей.
 * Решение о том, прошёл ли клиент мимо флоу записывается в `rejected_nk_skipped` в `user_data`:
 * - `0` - Клиент проходил флоу (Но необязательно продан).
 * - `1` - Клиент пропустил флоу.
 * ```
 * $is_flow_skipped = $this->user_data->read($user_id, 'rejected_nk_skipped') == 1;
 * ```
 *
 * В данный момент по флоу не проходит органика с 10 до 17 МСК, остальные
 * клиенты (и органика вне этого промежутка) проходят по флоу только если  подходят
 * под настройки https://manager.boostra.ru/bonon_settings
 *
 * @see CheckScoringsNk::actionCheck()
 */
class CheckScoringsNk extends ajaxController
{
    private const LOG_FILE = 'check_scorings_nk.txt';

    /**
     * Список действий которые можно вызывать с фронта
     * @return array[]
     */
    public function actions(): array
    {
        return [
            'check' => [
                'timeout' => 'string',
            ],
            'partnerClicked' => [true],
        ];
    }

    public function actionCheck(): array
    {
        $this->bonondo->tryToSell($this->user->id);
        $is_rejected_nk = $this->user_data->read($this->user->id, 'is_rejected_nk');

        if(isset($is_rejected_nk)) {
            if($is_rejected_nk > 0) {
                $this->bonondo->userDecline($this->user->id);
                return ['ready' => true, 'decision' => 'decline'];
            } else {
                $this->bonondo->userApprove($this->user->id);
                return ['ready' => true, 'decision' => 'approve'];
            }
        }

        $is_timeout = $this->data['timeout'] == 'true';
        if ($is_timeout) {
            // Клиент ждёт скоринги слишком долго
            // Пропускаем на этап привязки карты
            $this->user_data->set($this->user->id, 'is_rejected_nk', 0);
            $this->user_data->set($this->user->id, 'rejected_nk_timeout', 1);
            $this->bonondo->userApprove($this->user->id);
            return ['ready' => true, 'decision' => 'approve'];
        }

        return ['ready' => false];
    }

    public function actionPartnerClicked(): array
    {
        $this->user_data->set($this->user->id, 'rejected_nk_visited', 1);
        $this->users->update_user($this->user->id, [
            'card_added' => 1,
            'files_added' => 1,
            'additional_data_added' => 1
        ]);

        if ($this->short_flow->isShortFlowUser($this->user->id)) {
            $this->short_flow->setRegisterStage($this->user->id, $this->short_flow::STAGE_FINAL);
        }

        return ['refresh' => true];
    }
}

new CheckScoringsNk();