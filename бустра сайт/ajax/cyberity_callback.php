<?php

error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'api/Simpla.php';

/**
 * Коллбек получения результата антифрод верификации из Cyberity по вебхуку applicantReviewed
 *
 * API: https://developers.cyberity.ru/api-reference/#getting-verification-results
 */
class CyberityCallback extends Simpla
{
    /** @var string Успешная верификация */
    private const CYBERITY_SUCCESS_VERIFICATION_RESULT = 'GREEN';

    /** @var string Неуспешная верификация */
    private const CYBERITY_FAILED_VERIFICATION_RESULT = 'RED';

    /** @var string Лог-файл */
    private const LOG_FILE = 'cyberity_callback.txt';

    /**
     * Пример успешного коллбек вебхука applicantReviewed (поле reviewAnswer)
     * (object) array(
     * 'applicantId' => '674f349fd61a536c7f2f318b',
     * 'inspectionId' => '674f349fd61a536c7f2f318c',
     * 'applicantType' => 'individual',
     * 'correlationId' => '5b7b0b1796dc1761cb4694e162d5eb99',
     * 'levelName' => 'basic-kyc-level',
     * 'sandboxMode' => true,
     * 'externalUserId' => '2255093',
     * 'type' => 'applicantReviewed',
     * 'reviewResult' =>
     * (object) array(
     * 'reviewAnswer' => 'GREEN',
     * ),
     * 'reviewStatus' => 'completed',
     * 'createdAt' => '2024-12-05 12:24:45+0000',
     * 'createdAtMs' => '2024-12-05 12:24:45.244',
     * 'clientId' => 'akvarius',
     * )
     *
     * Пример неуспешного коллбек вебхука applicantReviewed (поле reviewAnswer)
     * (object) array(
     * 'applicantId' => '674f349fd61a536c7f2f318b',
     * 'inspectionId' => '674f349fd61a536c7f2f318c',
     * 'applicantType' => 'individual',
     * 'correlationId' => '5b7b0b1796dc1761cb4694e162d5eb99',
     * 'levelName' => 'basic-kyc-level',
     * 'sandboxMode' => true,
     * 'externalUserId' => '2255093',
     * 'type' => 'applicantReviewed',
     * 'reviewResult' =>
     * (object) array(
     * 'moderationComment' => 'We do not accept screenshots. Please upload an original photo.',
     * 'clientComment' => 'Screenshots are not accepted.',
     * 'reviewAnswer' => 'RED',
     * 'rejectLabels' =>
     * array (
     * 0 => 'UNSATISFACTORY_PHOTOS',
     * 1 => 'SCREENSHOTS',
     * ),
     * 'reviewRejectType' => 'RETRY',
     * ),
     * 'reviewStatus' => 'completed',
     * 'createdAt' => '2024-12-05 12:24:45+0000',
     * 'createdAtMs' => '2024-12-05 12:24:45.244',
     * 'clientId' => 'akvarius',
     * )
     *
     * @return void
     */
    public function run()
    {
        if (!$this->request->method('post')) {
            $this->logging(__METHOD__, '', '', 'Некорректный метод запроса', self::LOG_FILE);
            return;
        }

        $verificationResult = json_decode($this->request->post());

        if (empty($verificationResult)) {
            $this->logging(__METHOD__, '', 'Пустой ответ из сервиса', $verificationResult, self::LOG_FILE);
            return;
        }

        $this->logging(__METHOD__, '', '', $verificationResult, self::LOG_FILE);

        $user = $this->users->get_user((int)$verificationResult->externalUserId);

        if (empty($user)) {
            $this->logging(__METHOD__, '', 'Пользователь не найден', $verificationResult, self::LOG_FILE);
            return;
        }

        $scorings = $this->getCyberityScorings($user);

        if (empty($scorings)) {
            $this->addScoring((int)$user->id);
            $scorings = $this->getCyberityScorings($user);
        }

        if (empty($scorings)) {
            $this->logging(__METHOD__, '', 'Скоринги не найдены. Не удалось добавить скоринг', $verificationResult, self::LOG_FILE);
            return;
        }

        $this->saveCyberityScoring($scorings, $verificationResult);
        $this->saveVerificationResult((int)$user->id, $verificationResult);
        $this->updateFilesStatus((int)$user->id, $verificationResult);
    }

    /**
     * Обновление статуса фото пользователя, при непройденном антифроде.
     */
    private function updateFilesStatus(int $userId, $verificationResult): void
    {
        if(
            !empty($verificationResult->reviewResult->reviewAnswer) &&
            ($verificationResult->reviewResult->reviewAnswer === self::CYBERITY_FAILED_VERIFICATION_RESULT)
        ){
            $files = $this->users->get_files([
                'user_id' => $userId,
            ]);
            foreach ($files as $file){
                $this->users->update_file($file->id, [
                    'status' => $this->users::PHOTO_STATUS_REJECT,
                ]);
            }
        }
    }

    /**
     * Получение скорингов с TYPE_CYBERITY
     *
     * @param stdClass $user
     * @return array
     */
    private function getCyberityScorings(stdClass $user): array
    {
        $scorings = $this->scorings->get_scorings([
            'user_id' => (int)$user->id,
            'type' => $this->scorings::TYPE_CYBERITY,
            'status' => [
                $this->scorings::STATUS_NEW,
                $this->scorings::STATUS_PROCESS,
                $this->scorings::STATUS_STOPPED,
                $this->scorings::STATUS_ERROR
            ]
        ]);

        return $scorings ?: [];
    }

    /**
     * Добавление скоринга TYPE_CYBERITY
     *
     * @param int $userId
     * @return void
     */
    private function addScoring(int $userId)
    {
        $lastOrder = $this->orders->get_last_order($userId);

        $newScoringData = array(
            'user_id' => $userId,
            'order_id' => (int)$lastOrder->id,
            'type' => $this->scorings::TYPE_CYBERITY,
            'status' => $this->scorings::STATUS_PROCESS,
            'created' => date('Y-m-d H:i:s'),
            'start_date' => date('Y-m-d H:i:s')
        );

        $this->scorings->add_scoring($newScoringData);
    }

    /**
     * Сохранить результат верификации в скоринг TYPE_CYBERITY
     *
     * @param array $scorings
     * @param stdClass $verificationResult
     * @return void
     */
    private function saveCyberityScoring(array $scorings, stdClass $verificationResult): void
    {
        if (empty($verificationResult->reviewResult->reviewAnswer)) {
            $update = [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Ошибка! Некорректный ответ из сервиса'
            ];
        } elseif ($verificationResult->reviewResult->reviewAnswer === self::CYBERITY_SUCCESS_VERIFICATION_RESULT) {
            $update = [
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => 1,
                'string_result' => 'Успешная верификация'
            ];
        } elseif ($verificationResult->reviewResult->reviewAnswer === self::CYBERITY_FAILED_VERIFICATION_RESULT) {
            $stringResult = 'Неуспешная верификация';

            if (!empty($verificationResult->reviewResult->clientComment)) {
                $stringResult .= '. Причина: ' . $verificationResult->reviewResult->clientComment;
            }

            $update = [
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => 0,
                'string_result' => $stringResult
            ];
        } else {
            $update = [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Ошибка! Некорректный результат верификации'
            ];
        }

        $update['end_date'] = date('Y-m-d H:i:s');

        $body = [];

        if (!empty($scoring->body)) {
            $body = json_decode($scoring->body);
        }

        $body['callbackResponse'] = $verificationResult;
        $update['body'] = json_encode($body);

        foreach ($scorings as $scoring) {
            $this->scorings->update_scoring((int)$scoring->id, $update);
        }
    }

    /**
     * Сохранить результат верификации из Cyberity в s_cyberity_verifications
     *
     * @param int $userId
     * @param stdClass $verificationResult
     * @return void
     */
    private function saveVerificationResult(int $userId, stdClass $verificationResult): void
    {
        $userVerification = $this->cyberity->getUserVerification([
            'user_id' => $userId
        ]);

        if (empty($userVerification)) {
            $this->cyberity->insertUserVerification([
                'user_id' => $userId,
                'applicant_id' => $verificationResult->applicantId,
                'inspection_id' => $verificationResult->inspectionId,
                'status' => $this->cyberity::STATUS_COMPLETED,
                'date_create' => date('Y-m-d H:i:s'),
                'date_start_verification' => date('Y-m-d H:i:s'),
                'date_end_verification' => date('Y-m-d H:i:s'),
                'verification_result' => $verificationResult->reviewResult->reviewAnswer,
                'verification_result_comment' => $verificationResult->reviewResult->clientComment ?? null
            ]);
        } else {
            $this->cyberity->updateUserVerification((int)$userVerification->id, [
                'status' => $this->cyberity::STATUS_COMPLETED,
                'date_end_verification' => date('Y-m-d H:i:s'),
                'verification_result' => $verificationResult->reviewResult->reviewAnswer,
                'verification_result_comment' => $verificationResult->reviewResult->clientComment ?? null
            ]);
        }
    }
}

$cyberityCallback = new CyberityCallback();
$cyberityCallback->run();