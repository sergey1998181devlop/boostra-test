<?php

require_once 'Simpla.php';

/**
 * Класс проверяет совпадение номера телефона с ФИО + дата рождения пользователя по IDX
 */
class Idx extends Simpla
{
    /** @var int Кол-во секунд для генерации нового applicationId для акси */
    private const SECONDS_TO_REQUEST_NEW_APPLICATION_ID = 180;
    private const LOG_FILE = 'idx.txt';

    /** @var string Номер не принадлежит существующему клиенту, запрещаем авторизация */
    public const FAIL = 'fail';

    /** @var string Не получено решение, принадлежит ли номер существующему клиенту (показываем ошибку) */
    public const NO_DECISION = 'no_decision';

    /** @var string Номер принадлежит существующему клиенту, авторизуем и редиректим в ЛК */
    public const SUCCESS = 'success';

    public function getIdxDecision(stdClass $user, string $phone): string
    {
        $applicationId = $this->createIdxApplication($user, $phone);
        return $this->getIdxApplication($user, $applicationId);
    }

    private function createIdxApplication(stdClass $user, string $phone): string
    {
        $idxAxiApplicationId = $this->user_data->get((int)$user->id, $this->user_data::IDX_DECISION_AXI_APPLICATION_ID);

        // applicationId для акси вида IDX_03011729
        $applicationIdPrefix = 'IDX_';

        // Если первый запрос, то отправляем запрос в акси
        if (empty($idxAxiApplicationId)) {
            $applicationId = $applicationIdPrefix . '0' . $user->id;

            $this->axi->createIdxApplication($user, $phone, $applicationId);
            $this->user_data->set((int)$user->id, $this->user_data::IDX_DECISION_AXI_APPLICATION_ID, $applicationId);

            // Ждем, т.к. проверка в акси отрабатывает не сразу
            sleep(3);
        } // Если прошло меньше установленного времени, то получаем результат акси по старому applicationId
        elseif (time() - strtotime($idxAxiApplicationId->updated) < self::SECONDS_TO_REQUEST_NEW_APPLICATION_ID) {
            $applicationId = $idxAxiApplicationId->value;
        } // Если прошло больше установленного времени, то получаем новый applicationId для акси и отправляем запрос в акси
        else {
            $digitAfterPrefix = (int)substr(str_replace($applicationIdPrefix, '', $idxAxiApplicationId->value), 0, 1);
            $applicationId = $applicationIdPrefix . ($digitAfterPrefix + 1) . $user->id;

            $this->axi->createIdxApplication($user, $phone, $applicationId);
            $this->user_data->set((int)$user->id, $this->user_data::IDX_DECISION_AXI_APPLICATION_ID, $applicationId);

            // Ждем, т.к. проверка отрабатывает не сразу
            sleep(3);
        }

        return $applicationId;
    }

    private function getIdxApplication(stdClass $user, string $applicationId): string
    {
        // Перезапрашиваем результат акси в течение 30 сек
        for ($i = 1; $i <= 10; $i++) {
            $result = $this->axi->getApplication($applicationId);

            if (empty($result)) {
                $this->logging(__METHOD__, '', 'Не получен результат акси по IDX!', ['order_id' => $user->id, 'result' => $result], self::LOG_FILE);
                return self::NO_DECISION;
            }

            $result = json_decode($result);

            if (empty($result) || empty($result->Application->AXI->application_e->decision_e)) {
                $this->logging(__METHOD__, '', 'Некорректный результат акси по IDX!', ['order_id' => $user->id, 'result' => $result], self::LOG_FILE);
                return self::NO_DECISION;
            }

            $finalDecision = $result->Application->AXI->application_e->decision_e->{'@final_decision'};

            // Получили окончательный ответ, выходим из цикла
            if (!empty($finalDecision)) {
                break;
            }

            sleep(3);
        }

        if (empty($finalDecision)) {
            $this->logging(__METHOD__, '', 'Не получен ответ из акси по idx', ['user_id' => $user->id, 'finalDecision' => $finalDecision], self::LOG_FILE);
            return self::NO_DECISION;
        }

        $idxDecision = $result->Application->AXI->application_e->IDX_VerifyPhone->{'@operationResult'};

        $isNewPhoneBelongsToUser = false;
        if ($finalDecision === $this->axi::FINAL_DECISION_APPROVE && $idxDecision === self::SUCCESS) {
            $isNewPhoneBelongsToUser = true;
        }

        $dataToLog = [
            'user_id' => $user->id,
            'applicationId' => $applicationId,
            'finalDecision' => $finalDecision,
            'idxDecision' => $idxDecision,
            'result' => $isNewPhoneBelongsToUser,
            'Кол-во запросов к акси' => $i,
        ];

        $this->logging(__METHOD__, '', 'Результат проверки на IDX: ', $dataToLog, self::LOG_FILE);

        return $isNewPhoneBelongsToUser ? self::SUCCESS : self::FAIL;
    }
}
