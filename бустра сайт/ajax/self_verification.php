<?php

use api\services\FileStorageService;

error_reporting(0);
ini_set('display_errors', 'Off');

session_start();

chdir('..');
require 'api/Simpla.php';

/**
 * Класс для работы с API Cyberity, предоставляющий антифрод систему для сканирования лица и ее сверки с фотографией паспорта заемщика
 *
 * Панель разработчика: https://api.cyberity.ru/
 * Документация по API: https://developers.cyberity.ru/web-sdk/
 */
class SelfVerification extends Simpla
{
    /** @var string URL API сервиса */
    private const BASE_URL = 'https://api.cyberity.ru';

    /** @var string Уровень проверки (уровни проверок позволяют детально настроить процесс верификации Ваших пользователей) */
    private const CHECK_LEVEL = 'basic-kyc-level';

    /** @var string Длительность существование токена (в секундах) */
    private const TOKEN_AVAILABILITY_DURATION = 600;

    /** @var string Лог-файл */
    private const LOG_FILE = 'self_verification.txt';

    public function run()
    {
        $action = $this->request->get('action');
        switch ($action):
            case 'started_application':
                return $this->changeApplicationStatus($this->cyberity::STATUS_STARTED_APPLICATION);
            case 'added_passport':
                return $this->changeApplicationStatus($this->cyberity::STATUS_ADDED_PASSPORT);
            case 'added_selfie':
                return $this->changeApplicationStatus($this->cyberity::STATUS_ADDED_SELFIE);
            case 'app_token':
                return $this->getAppToken();
            case 'access_token':
                return $this->getAccessToken();
            case 'check_files':
                return $this->checkFiles();
            case 'test_review_result':
                return $this->testReviewResult();
            default:
                return json_encode(['success' => true]);
        endswitch;
    }

    /**
     * Сохранение этапа верификации
     *
     * @param string $newStatus
     * @return string
     */
    private function changeApplicationStatus(string $newStatus): string
    {
        $user = $this->getUser();

        if (empty($user)) {
            $this->logging(__METHOD__, '', $_REQUEST, ['Пользователь не найден', $_SESSION], self::LOG_FILE);
            return json_encode(['success' => false]);
        }

        $userVerification = $this->cyberity->getUserVerification([
            'user_id' => (int)$user->id
        ]);

        if (empty($userVerification)) {

            $applicantData = $this->getApplicantData($user->id);

            if (empty($applicantData)) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Не удалось получить данные пользователя', $_SESSION], self::LOG_FILE);
                return json_encode(['success' => false]);
            }

            $this->cyberity->insertUserVerification([
                'user_id' => (int)$user->id,
                'applicant_id' => $applicantData->id,
                'inspection_id' => $applicantData->inspectionId,
                'status' => $newStatus,
                'date_create' => date('Y-m-d H:i:s'),
                'date_update' => date('Y-m-d H:i:s'),
            ]);
        } else if ($userVerification->status !== $this->cyberity::STATUS_COMPLETED) {

            $update = [
                'status' => $newStatus,
                'date_update' => date('Y-m-d H:i:s'),
            ];

            if (empty($userVerification->applicant_id)) {
                $applicantData = $this->getApplicantData($user->id);

                if (empty($applicantData)) {
                    $this->logging(__METHOD__, '', $_REQUEST, ['Не удалось получить данные пользователя', $_SESSION], self::LOG_FILE);
                    return json_encode(['success' => false]);
                }

                $update['applicant_id'] = $applicantData->id;
                $update['inspection_id'] = $applicantData->inspectionId;
            }

            $this->cyberity->updateUserVerification((int)$userVerification->id, $update);
        }

        return json_encode(['success' => true]);
    }

    /**
     * Получение app token
     *
     * @return string
     */
    private function getAppToken(): string
    {
        $user = $this->getUser();

        if (empty($user)) {
            return json_encode(['success' => false]);
        }

        return json_encode([
            'success' => true,
            'app_token' => $this->cyberity->getToken(),
            'phone' => $user->phone_mobile,
            'email' => $user->email
        ]);
    }

    /**
     * @return stdClass|null
     */
    private function getUser(): ?stdClass
    {
        $userId = (int)$_SESSION['user_id'];

        if (empty($userId)) {
            return null;
        }

        $user = $this->users->get_user($userId);

        return $user ?: null;
    }

    /**
     * Получение access token
     *
     * API: https://developers.cyberity.ru/api-reference/#access-tokens-for-sdks
     *
     * @return string
     */
    private function getAccessToken(): string
    {
        $user = $this->getUser();

        if (empty($user)) {
            $this->logging(__METHOD__, '', $_REQUEST, ['Пользователь не найден', $_SESSION], self::LOG_FILE);
            return json_encode(['success' => false]);
        }

        $url = '/resources/accessTokens';

        $data = [
            'userId' => $user->id,
            'levelName' => self::CHECK_LEVEL,
            'ttlInSecs' => (string)self::TOKEN_AVAILABILITY_DURATION
        ];

        $url .= '?' . http_build_query($data);

        $headers = $this->getHeaders($url, 'POST');

        $response = $this->request(self::BASE_URL . $url, 'POST', [], $headers);

        $response = json_decode($response);

        if (empty($response->token)) {
            return json_encode([
                'success' => false,
                'message' => 'Произошла ошибка. Повторите попытку позже. При сохранении ошибки обратитесь в техническую поддержку'
            ]);
        }

        return json_encode([
            'success' => true,
            'access_token' => $response->token
        ]);
    }

    /**
     * Получить заголовки для запросов
     *
     * API: https://developers.cyberity.ru/api-reference/#app-tokens
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @param bool $isJson
     * @return string[]
     */
    private function getHeaders(string $url, string $method = 'GET', array $data = [], bool $isJson = true): array
    {
        $curTimestamp = time();

        $signature = $curTimestamp . $method . $url;

        if (!empty($data)) {
            $signature .= json_encode($data);
        }

        $signatureHash = hash_hmac('sha256', $signature, $this->cyberity->getSecretKey());

        $headers = [
            'X-App-Token: ' . $this->cyberity->getToken(),
            'X-App-Access-Sig: ' . $signatureHash,
            'X-App-Access-Ts: ' . $curTimestamp,
        ];

        if ($isJson) {
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
        }

        return $headers;
    }

    /**
     * Получаем и сохраняем фото паспорта и скана лица
     *
     * @return string
     */
    private function checkFiles(): string
    {
        $user = $this->getUser();

        if (empty($user)) {
            $this->logging(__METHOD__, '', $_REQUEST, ['Пользователь не найден', $_SESSION], self::LOG_FILE);
            return json_encode(['success' => false]);
        }

        // 1. Для получения фото паспорта и скана лица нужно предварительно получить inspectionId клиента
        $applicantData = $this->getApplicantData($user->id);

        if (empty($applicantData)) {
            $this->logging(__METHOD__, '', $_REQUEST, ['Не удалось получить данные пользователя', $_SESSION], self::LOG_FILE);
            return json_encode(['success' => false]);
        }

        // 2. Для получения фото паспорта и скана лица нужно предварительно получить id этих файлов
        $applicantStatus = $this->getApplicantStatus($applicantData->id);

        if (empty($applicantStatus)) {
            $this->logging(__METHOD__, '', $_REQUEST, ['Не удалось получить статус верификации', $_SESSION], self::LOG_FILE);
            return json_encode(['success' => false]);
        }

        // 3. Получение и сохранение фото паспорта и скана лица
        $areFilesSaved = $this->savePhotos($applicantStatus, $applicantData->inspectionId, $user->id);

        if (empty($areFilesSaved)) {
            $this->logging(__METHOD__, '', $_REQUEST, ['Ошибка при сохранении фотографий', $_SESSION], self::LOG_FILE);
            return json_encode(['success' => false]);
        }

        $userVerification = $this->cyberity->getUserVerification([
            'user_id' => (int)$user->id
        ]);

        if (empty($userVerification)) {
            $this->cyberity->insertUserVerification([
                'user_id' => (int)$user->id,
                'applicant_id' => $applicantData->id,
                'inspection_id' => $applicantData->inspectionId,
                'status' => $this->cyberity::STATUS_PROGRESS,
                'date_create' => date('Y-m-d H:i:s'),
                'date_update' => date('Y-m-d H:i:s'),
                'date_start_verification' => date('Y-m-d H:i:s'),
            ]);
          } else if ($userVerification->status !== $this->cyberity::STATUS_COMPLETED) {
            $this->cyberity->updateUserVerification((int)$userVerification->id, [
                'applicant_id' => $applicantData->id,
                'inspection_id' => $applicantData->inspectionId,
                'status' => $this->cyberity::STATUS_PROGRESS,
                'date_update' => date('Y-m-d H:i:s'),
                'date_start_verification' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->addCyberityScoring($user, $applicantData, $applicantStatus);

        return json_encode(['success' => true]);
    }

    /**
     * Получение и сохранение фото паспорта и скана лица
     *
     * @param stdClass $applicantStatus
     * @param string $inspectionId
     * @param int $userId
     * @return bool
     */
    private function savePhotos(stdClass $applicantStatus, string $inspectionId, int $userId): bool
    {
        try {
            $this->savePassportPhoto($applicantStatus, $inspectionId, $userId);
        } catch (Throwable $e) {
        }

        try {
            $this->saveFaceScanPhoto($applicantStatus, $inspectionId, $userId);
        } catch (Throwable $e) {
        }

        return true;
    }

    /**
     * Получение и сохранение фото паспорта
     *
     * @param stdClass $applicantStatus
     * @param string $inspectionId
     * @param int $userId
     * @return void
     */
    private function savePassportPhoto(stdClass $applicantStatus, string $inspectionId, int $userId): void
    {
        $fileCounter = 1;

        // Получение и сохранение фото паспорта
        foreach ($applicantStatus->IDENTITY->imageIds as $photoId) {
            $photoBinary = $this->getApplicantPhoto($inspectionId, $photoId);

            if (empty($photoBinary)) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Фотография паспорта не получена', $_SESSION], self::LOG_FILE);
                continue;
            }

            $isPhotoSaved = $this->savePhoto($userId, $photoBinary, 'passport' . $fileCounter++);
            if (!$isPhotoSaved) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Ошибка при сохранении фотографии паспорта', $_SESSION], self::LOG_FILE);
            }
        }
    }


    /**
     * Получение и сохранение скана лица
     *
     * @param stdClass $applicantStatus
     * @param string $inspectionId
     * @param int $userId
     * @return void
     */
    private function saveFaceScanPhoto(stdClass $applicantStatus, string $inspectionId, int $userId): void
    {
        $fileCounter = 2;

        // Получение и сохранение скана лица
        foreach ($applicantStatus->SELFIE->imageIds as $photoId) {
            $photoBinary = $this->getApplicantPhoto($inspectionId, $photoId);

            if (empty($photoBinary)) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Скан лица не получен', $_SESSION], self::LOG_FILE);
                continue;
            }

            $isPhotoSaved = $this->savePhoto($userId, $photoBinary, 'face' . $fileCounter++);

            if (!$isPhotoSaved) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Ошибка при сохранении скана лица', $_SESSION], self::LOG_FILE);
            }
        }
    }

    /**
     * Сохранение фото паспорта или скана лица
     *
     * @param int $userId
     * @param string $photoBinary
     * @param string $type
     * @return bool
     */
    public function savePhoto(int $userId, string $photoBinary, string $type): bool
    {
        $ext = 'png';

        do {
            $new_filename = md5(microtime() . rand()) . '.' . $ext;
        } while ($this->users->check_filename($new_filename));

        // Проверяем сохраняем в папку загрузки
        $file_local_path = $this->config->root_dir . $this->config->original_images_dir . $new_filename; // Пример: /var/www/html/files/users/1cfa200358a5a4c2f35c38beadac2c1c.png
        $file_uploaded = file_put_contents($file_local_path, $photoBinary);
        if (!$file_uploaded) {
            return false;
        }

        $s3_name = 'S3/' . date('Ymd') . '/' . $new_filename;

        $fileStorage = new FileStorageService(
            $this->config->s3['endpoint'],
            $this->config->s3['region'],
            $this->config->s3['key'],
            $this->config->s3['secret'],
            $this->config->s3['Bucket']
        );
        $fileStorage->putFile($file_local_path, $s3_name);

        $file_id = $this->users->add_file([
            'user_id' => $userId,
            'name' => $new_filename,
            'type' => $type,
            'status' => 0,
            's3_name' => $s3_name,
        ]);

        $returned = $this->soap->soap_send_files($userId);

        if ($returned->return == 'OK') {
            $this->users->update_file($file_id, ['status' => 1]);
        }

        return true;
    }

    /**
     * Получение данных клиента
     *
     * API: https://developers.cyberity.ru/api-reference/#getting-applicant-data
     *
     * @param int $userId
     * @return stdClass|null
     */
    private function getApplicantData(int $userId): ?stdClass
    {
        $url = '/resources/applicants/-;externalUserId=' . $userId . '/one';
        $headers = $this->getHeaders($url);
        $applicantData = $this->request(self::BASE_URL . $url, 'GET', [], $headers);

        $applicantData = json_decode($applicantData);

        if (empty($applicantData) || empty($applicantData->id) || empty($applicantData->inspectionId)) {
            return null;
        }

        return $applicantData;
    }

    /**
     * Получение статуса заявления клиента
     *
     * API: https://developers.cyberity.ru/api-reference/#getting-applicant-status-api
     *
     * @param string $applicantId
     * @return stdClass|null
     */
    private function getApplicantStatus(string $applicantId): ?stdClass
    {
        $url = '/resources/applicants/' . $applicantId . '/requiredIdDocsStatus';
        $headers = $this->getHeaders($url);
        $applicantStatus = $this->request(self::BASE_URL . $url, 'GET', [], $headers);

        $applicantStatus = json_decode($applicantStatus);

        if (
            empty($applicantStatus) ||
            empty($applicantStatus->IDENTITY->imageIds) ||
            empty($applicantStatus->SELFIE->imageIds)
        ) {
            return null;
        }

        return $applicantStatus;
    }

    /**
     * Получение фотографии паспорта и скана лица клиента
     *
     * API: https://developers.cyberity.ru/api-reference/#getting-document-images
     *
     * @param string $inspectionId
     * @param int $photoId
     * @return null|string (binary)
     */
    private function getApplicantPhoto(string $inspectionId, int $photoId): ?string
    {
        $url = '/resources/inspections/' . $inspectionId . '/resources/' . $photoId;
        $headers = $this->getHeaders($url, 'GET', [], false);
        $photoBinary = $this->request(self::BASE_URL . $url, 'GET', [], $headers);

        if (empty($photoBinary)) {
            return null;
        }

        return $photoBinary;
    }

    /**
     * Добавление скоринга TYPE_CYBERITY
     *
     * @param stdClass $user
     * @param stdClass $applicantData
     * @param stdClass $applicantStatus
     * @return void
     */
    private function addCyberityScoring(stdClass $user, stdClass $applicantData, stdClass $applicantStatus): void
    {
        $lastOrder = $this->orders->get_last_order($user->id);

        $newScoringData = array(
            'user_id' => $user->id,
            'order_id' => $lastOrder->id,
            'type' => $this->scorings::TYPE_CYBERITY,
            'status' => $this->scorings::STATUS_PROCESS,
            'created' => date('Y-m-d H:i:s'),
            'start_date' => date('Y-m-d H:i:s'),
            'body' => json_encode(['applicantData' => $applicantData, 'applicantStatus' => $applicantStatus])
        );

        $this->scorings->add_scoring($newScoringData);
    }

    /**
     * Метод для тестирования. Устанавливает успешный или неуспешный результата верификации для заявки
     *
     * Пример applicantId = 674f349fd61a536c7f2f318b
     * Нужно в GET-параметры передать applicantId и reviewResult (GREEN или RED)
     *
     * @return string
     */
    private function testReviewResult(): string
    {
        $applicantId = $this->request->get('applicantId');

        if (empty($applicantId)) {
            $this->logging(__METHOD__, '', '', 'Не передан applicantId в тестовом методе', self::LOG_FILE);
            return json_encode(['success' => false]);
        }

        $reviewResult = $this->request->get('applicantId');

        if ($reviewResult === 'GREEN') {
            $data = [
                'reviewAnswer' => 'GREEN',
                'rejectLabels' => [],
            ];
        } else {
            $data = [
                'reviewAnswer' => 'RED',
                'moderationComment' => 'We do not accept screenshots. Please upload an original photo.',
                'clientComment' => 'Screenshots are not accepted.',
                'reviewRejectType' => 'RETRY',
                'rejectLabels' => ['UNSATISFACTORY_PHOTOS', 'SCREENSHOTS']
            ];
        }

        $url = '/resources/applicants/' . $applicantId . '/status/testCompleted';

        $headers = $this->getHeaders($url, 'POST', $data);

        $response = $this->request(self::BASE_URL . $url, 'POST', $data, $headers);

        $response = json_decode($response);

        if (empty($response->ok)) {
            $this->logging(__METHOD__, '', 'Неуспешный ответ в тестовом методе', $response, self::LOG_FILE);
            return json_encode([
                'success' => false,
            ]);
        }

        return json_encode([
            'success' => true
        ]);
    }

    /**
     * Выполнить запрос
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $headers
     * @return bool|string
     */
    private function request(string $url, string $method = 'GET', array $data = [], array $headers = [])
    {
        $curl = curl_init();

        $curlData = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
        ];

        if (!empty($data)) {
            $curlData[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        curl_setopt_array($curl, $curlData);

        $response = curl_exec($curl);

        if (strpos($url, '/resources/inspections/') !== false) {
            $this->logging(__METHOD__, $url, $data, ['response' => 'Получение фото', 'session' => $_SESSION], self::LOG_FILE);
        } else {
            $this->logging(__METHOD__, $url, $data, ['response' => $response, 'session' => $_SESSION], self::LOG_FILE);
        }

        $error = curl_error($curl);

        if (!empty($error)) {
            $this->logging(__METHOD__, $url, $data, ['error' => $error, 'session' => $_SESSION], self::LOG_FILE);
        }

        curl_close($curl);

        return $response;
    }
}

$selfVerification = new SelfVerification();
$result = $selfVerification->run();

header('Content-type: application/json; charset=UTF-8');
echo($result);
