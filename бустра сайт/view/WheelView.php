<?php
require_once('View.php');

class WheelView extends View
{
    public function fetch()
    {
        return; // TODO: временно отключено

        $action = $this->request->get('action');

        if ($action === 'spin')     return $this->spin();
        if ($action === 'complete') return $this->complete();

        $this->wheelFileLog([
            'event'   => 'Клиент нажал кнопку "Играть"',
            'user_id' => !empty($this->user->id) ? (int)$this->user->id : 0,
            'ts'      => date('d-m-Y H:i:s')
        ], 'view');

        try {
            $this->promo_events->saveEvent($this->user->id, 'play_button');
        } catch (Exception $e) {
            $this->logging(__METHOD__, 'wheel', [], ['promo_event_error' => $e->getMessage()], 'wheel_errors.txt');
        }

        return $this->json(['html' => $this->design->fetch('wheel.tpl')]);
    }

    /* ===========================
     * Actions
     * =========================== */

    /**
     * Розыгрыш сектора:
     *  - логирование (БД + файл)
     *  - взвешенное случайное распределение по ИНДЕКСАМ (жёстко совпадает с фронтом)
     *  - бонус-спин режет шансы всех призов (кроме "ничего"), дефицит уходит в "ничего"
     *  - поддержка ×2 на следующий выигрыш (применение с вероятностью 50%)
     */
    private function spin()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isAuthorized())                        return $this->jsonError('unauthorized');
        if ($this->userAlreadyHasSpun($this->user->id))    return $this->jsonError('already_spined');

        try {
            $this->promo_events->saveEvent($this->user->id, 'spin');
        } catch (Exception $e) {
            $this->logging(__METHOD__, 'wheel', [], ['promo_event_error' => $e->getMessage()], 'wheel_errors.txt');
        }

        $userId               = (int)$this->user->id;
        $isBonusSpinRequested = (int)$this->request->post('bonus', 'integer') ? 1 : 0;

        // Конфигурация
        $sectorMap                = $this->sectorMap();             // index => [type, value]
        $sectorWeightsPercent     = $this->sectorWeightsPercent();  // index => weight(%), сумма 100
        $giftValueWeights         = $this->giftValueWeights();      // value => weight

        // Бонус-спин: скорректируем веса
        if ($isBonusSpinRequested) {
            $sectorWeightsPercent = $this->adjustWeightsForBonus($sectorWeightsPercent, $sectorMap);
        }

        // Проверка «висящего» ×2 с прошлого спина
        $hasPendingX2FromPreviousSpin = $this->hasPendingX2($userId);

        // Розыгрыш индекса по весам
        $randomUnitValue = $this->randomUnitFloat();
        $chosenSectorIndex = $this->pickIndexByWeights($sectorWeightsPercent, $randomUnitValue);

        // Тип и значение — строго из карты индексов
        list($resultType, $resultValue) = $sectorMap[$chosenSectorIndex];

        // Для «Сюрприза» тянем сумму из пула
        if ($resultType === 'gift') {
            $resultValue = $this->pickFromWeightedPool($giftValueWeights);
        }
        // «discount» — фикс по индексу (никаких внутренних пулов)

        // Применяем «висящий» ×2 к ближайшему выигрышу (discount/gift) с вероятностью 50%
        $multiplierMarkedForNextSpin = ($resultType === 'multiplier') ? 1 : 0;
        $multiplierAppliedNow        = 0;
        if ($hasPendingX2FromPreviousSpin && in_array($resultType, ['discount','gift'], true)) {
            $shouldApplyX2 = (random_int(0, 1) === 1); // 50%
            if ($shouldApplyX2) {
                $resultValue *= 2;
                $multiplierAppliedNow = 1;
            }
        }

        // Лог в БД (start)
        $spinId = $this->insertSpinStartLog($userId, [
            'bonus_spin'          => $isBonusSpinRequested,
            'rand_u'              => $randomUnitValue,
            'weights_json'        => json_encode(['weights_pct' => $sectorWeightsPercent, 'sectors' => $sectorMap], JSON_UNESCAPED_UNICODE),
            'result_value'        => (int)$resultValue,
            'sector_index'        => (int)$chosenSectorIndex,
            'result_type'         => $resultType,
            'multiplier_pending'  => $multiplierMarkedForNextSpin ? 1 : 0,
            'multiplier_applied'  => $multiplierAppliedNow ? 1 : 0,
        ]);

        // Файловый лог (подстраховка)
        $this->wheelFileLog([
            'spin_id' => $spinId,
            'user_id' => $userId,
            'result'  => [
                'chosen_index'        => $chosenSectorIndex,
                'type'                => $resultType,
                'value'               => (int)$resultValue,
                'bonus_spin'          => $isBonusSpinRequested,
                'has_pending_x2'      => $hasPendingX2FromPreviousSpin ? 1 : 0,
                'multiplier_applied'  => $multiplierAppliedNow ? 1 : 0,
            ],
            'u'       => $randomUnitValue,
            'ts'      => date('d-m-Y H:i:s'),
        ]);

        // Ответ фронту
        return $this->json([
            'success' => true,
            'index'   => (int)$chosenSectorIndex,
            'prize'   => ['type' => $resultType, 'value' => (int)$resultValue],
            'spin_id' => (int)$spinId,
        ]);
    }

    /**
     * Завершение спина.
     * Идемпотентно. Если НЕ бонус — ставим признак s_user_data.wheel_spined = 1
     */
    private function complete()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isAuthorized()) return $this->jsonError('unauthorized');

        try {
            $this->promo_events->saveEvent($this->user->id, 'spin_complete');
        } catch (Exception $e) {
            $this->logging(__METHOD__, 'wheel', [], ['promo_event_error' => $e->getMessage()], 'wheel_errors.txt');
        }

        $userId      = (int)$this->user->id;
        $spinId      = (int)$this->request->post('spin_id', 'integer');
        $isBonusSpin = (int)$this->request->post('bonus', 'integer');

        if (empty($spinId)) return $this->jsonError('no_spin_id');

        $spinRow = $this->readSpinById($spinId, $userId);
        if (empty($spinRow)) return $this->jsonError('spin_not_found');

        // Если спин не бонусный — считаем, что пользователь уже крутил
        if (empty($isBonusSpin)) {
            $this->user_data->set($userId, 'wheel_spined', 1);
        }

        // Идемпотентность
        if ($spinRow->status === 'finished') {
            return $this->json(['success' => true]);
        }

        // Закрываем спин
        $updateQuery = $this->db->placehold(
            'UPDATE s_wheel_spins SET ?% WHERE id=?',
            ['finished_at' => date('Y-m-d H:i:s'), 'status' => 'finished'],
            $spinId
        );
        $this->db->query($updateQuery);

        // Файловый лог
        $this->wheelFileLog([
            'event'   => 'finished',
            'user_id' => $userId,
            'ts'      => date('d-m-Y H:i:s'),
            'spin_id' => (int)$spinId
        ], 'complete');

        return $this->json(['success' => true]);
    }

    /* ===========================
     * Config
     * =========================== */

    // Карта секторов: индекс => [type, value]
    private function sectorMap(): array
    {
        return [
            ['multiplier', 2],     // ×2
            ['discount',   200],   // Скидка 200 ₽
            ['nothing',      0],   // Ничего
            ['discount',  1000],   // Скидка 1000 ₽
            ['nothing',      0],
            ['discount',   200],   // Скидка 200 ₽
            ['jackpot',  30000],   // Джекпот (маркер)
            ['nothing',      0],
            ['gift',         0],   // Сюрприз — сумма выбирается из giftValueWeights
            ['bonus',        1],   // Бонусный спин
        ];
    }

    // Веса по индексам (в %), сумма = 100
    private function sectorWeightsPercent(): array
    {
        return [
            0 =>  2.00,  // multiplier
            1 => 22.00,  // discount 200
            2 =>  6.66,  // nothing
            3 => 14.74,  // discount 1000
            4 =>  6.67,  // nothing
            5 => 22.00,  // discount 200
            6 =>  0.01,  // jackpot
            7 =>  6.67,  // nothing
            8 =>  9.25,  // gift
            9 => 10.00,  // bonus
        ];
    }

    // Внутреннее распределение для «Сюрприз»
    private function giftValueWeights(): array
    {
        return [ 10 => 22, 25 => 23, 50 => 22, 100 => 15, 150 => 10, 200 => 8 ];
    }

    /* ===========================
     * Helpers (probability / data)
     * =========================== */

    // Бонус-спин: урезаем все НЕ "nothing" в 2 раза, дефицит раздаём "nothing" пропорционально их текущему весу
    private function adjustWeightsForBonus(array $weightsPercentByIndex, array $sectorMap): array
    {
        $totalAfterCut = 0.0;

        foreach ($weightsPercentByIndex as $index => $weightPercent) {
            $isNothing = ($sectorMap[$index][0] === 'nothing');
            $weightsPercentByIndex[$index] = $isNothing ? $weightPercent : round($weightPercent / 2, 4);
            $totalAfterCut += $weightsPercentByIndex[$index];
        }

        if ($totalAfterCut >= 100.0) {
            return $weightsPercentByIndex;
        }

        $percentMissing         = 100.0 - $totalAfterCut;
        $nothingWeightsSubtotal = 0.0;

        foreach ($weightsPercentByIndex as $index => $weightPercent) {
            if ($sectorMap[$index][0] === 'nothing') {
                $nothingWeightsSubtotal += $weightPercent;
            }
        }
        if ($nothingWeightsSubtotal <= 0.0) {
            return $weightsPercentByIndex;
        }

        foreach ($weightsPercentByIndex as $index => $weightPercent) {
            if ($sectorMap[$index][0] === 'nothing') {
                $proportion = $weightPercent / $nothingWeightsSubtotal;
                $weightsPercentByIndex[$index] = round($weightPercent + $percentMissing * $proportion, 4);
            }
        }

        return $weightsPercentByIndex;
    }

    // Выбор индекса по весам (randomUnit in [0;1))
    private function pickIndexByWeights(array $weightsPercentByIndex, float $randomUnit): int
    {
        $cumulativeProbability = 0.0;
        foreach ($weightsPercentByIndex as $index => $weightPercent) {
            $cumulativeProbability += $weightPercent / 100.0;
            if ($randomUnit <= $cumulativeProbability) {
                return (int)$index;
            }
        }
        end($weightsPercentByIndex);
        return (int)key($weightsPercentByIndex); // на всякий случай — последний индекс
    }

    private function randomUnitFloat(): float
    {
        return random_int(0, 1000000) / 1000000; // [0;1)
    }

    // Выбор значения из словаря "значение => вес"
    private function pickFromWeightedPool(array $weightByValue): int
    {
        $totalWeight = array_sum($weightByValue);
        $randomTicket = random_int(1, $totalWeight);
        $cumulativeWeight = 0;

        foreach ($weightByValue as $value => $weight) {
            $cumulativeWeight += $weight;
            if ($randomTicket <= $cumulativeWeight) {
                return (int)$value;
            }
        }

        // fallback — первое значение
        return (int)array_key_first($weightByValue);
    }

    private function hasPendingX2(int $userId): bool
    {
        $sql = $this->db->placehold(
            "SELECT result_type, status FROM s_wheel_spins WHERE user_id=? ORDER BY id DESC LIMIT 1",
            $userId
        );
        $this->db->query($sql);
        $lastSpinRow = $this->db->result();

        return $lastSpinRow
            && $lastSpinRow->result_type === 'multiplier'
            && $lastSpinRow->status === 'finished';
    }

    private function insertSpinStartLog(int $userId, array $data): int
    {
        $insertPayload = [
            'user_id'            => $userId,
            'started_at'         => date('Y-m-d H:i:s'),
            'status'             => 'started',
            'bonus_spin'         => (int)$data['bonus_spin'],
            'price'              => 200,
            'rand_u'             => $data['rand_u'],
            'weights_json'       => $data['weights_json'],
            'ip'                 => $_SERVER['REMOTE_ADDR'] ?? '',
            'ua'                 => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'result_value'       => (int)$data['result_value'],
            'sector_index'       => (int)$data['sector_index'],
            'result_type'        => $data['result_type'],
            'multiplier_pending' => (int)$data['multiplier_pending'],
            'multiplier_applied' => (int)$data['multiplier_applied'],
        ];

        $insertQuery = $this->db->placehold("INSERT INTO s_wheel_spins SET ?%", $insertPayload);
        $this->db->query($insertQuery);

        return (int)$this->db->insert_id();
    }

    private function readSpinById(int $spinId, int $userId)
    {
        $this->db->query("SELECT * FROM s_wheel_spins WHERE id=? AND user_id=? LIMIT 1", $spinId, $userId);
        return $this->db->result();
    }

    private function isAuthorized(): bool
    {
        return !empty($this->user) && !empty($this->user->id);
    }

    private function userAlreadyHasSpun(int $userId): bool
    {
        return (bool)$this->user_data->read($userId, 'wheel_spined');
    }

    /* ===========================
     * Logging / JSON helpers
     * =========================== */

    private function wheelFileLog(array $data, string $url = 'spin'): void
    {
        $this->logging(__METHOD__, 'wheel/'.$url, [], $data, 'wheel.txt');
    }

    private function json(array $payload)
    {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        die;
    }

    private function jsonError(string $code)
    {
        echo json_encode(['success' => false, 'error' => $code], JSON_UNESCAPED_UNICODE);
        die;
    }
}
