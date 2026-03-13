<?php

declare(strict_types=1);

use api\terrorist\TerroristMatchService;

/**
 * Скоринг: TerroristCheck
 * Локальная проверка по актуальным террористическим спискам (is_current=1, is_terrorist=1).
 *
 * При совпадении:
 * - success = 0
 * - stop other scorings
 * - сохранить флаг в s_user_data: terrorist_status=1, terrorist_scoring_id=<id>
 *
 * При отсутствии:
 * - success = 1
 * - сохранить флаг: terrorist_status=0, terrorist_scoring_id=0
 */
class Terrorist extends Simpla
{
    // s_user_data keys
    public const USERDATA_TERRORIST_STATUS = 'terrorist_status';        // 0|1
    public const USERDATA_TERRORIST_SCORING_ID = 'terrorist_scoring_id';// scoring id or 0

    private const MATCHES_LIMIT_FOR_BODY = 20;

    private TerroristMatchService $matchService;

    public function __construct()
    {
        parent::__construct();
        $this->matchService = new TerroristMatchService($this->db);
    }

    /**
     * @param int|string $scoringId
     * @return array|null
     */
    public function run_scoring($scoringId)
    {
        $scoringId = (int)$scoringId;

        $scoring = $this->scorings->get_scoring($scoringId);
        if (empty($scoring)) {
            return null;
        }

        // 1) Проверка активности типа
        $scoringType = $this->scorings->get_type($this->scorings::TYPE_TERRORIST_CHECK);
        if (!empty($scoringType) && (int)$scoringType->active === 0) {
            $result = [
                'status'        => $this->scorings::STATUS_COMPLETED,
                'success'       => 1,
                'string_result' => 'Проверка отключена',
                'body'          => '',
                'end_date'      => date('Y-m-d H:i:s'),
            ];

            // сбросим флаг (чтобы старая плашка не висела)
            $this->setUserTerroristUserData((int)$scoring->user_id, $scoringId, false);

            $this->scorings->update_scoring($scoringId, $result);
            return $result;
        }

        // 2) Загрузка клиента
        $userId = (int)$scoring->user_id;
        $client = $this->users->get_user($userId);

        if (empty($client)) {
            $result = [
                'status'        => $this->scorings::STATUS_ERROR,
                'success'       => 0,
                'string_result' => 'Клиент не найден',
                'body'          => '',
                'end_date'      => date('Y-m-d H:i:s'),
            ];

            $this->scorings->update_scoring($scoringId, $result);
            return $result;
        }

        // 3) clientData (нормализованный) — нужен и для поиска
        $clientData = $this->matchService->getClientData($client);

        try {
            // 4) Матчинг
            $matches = $this->matchService->findMatchesForClient($clientData);

            $found = !empty($matches);

            // 5) s_user_data: флаг + указатель на scoring_id
            $this->setUserTerroristUserData($userId, $scoringId, $found);

            if (!$found) {
                $result = [
                    'status'        => $this->scorings::STATUS_COMPLETED,
                    'success'       => 1,
                    'string_result' => 'Клиент в террористических списках не найден',
                    'body'          => '',
                    'end_date'      => date('Y-m-d H:i:s'),
                ];

                $this->scorings->update_scoring($scoringId, $result);
                return $result;
            }

            // 6) найдено
            $payload = $this->buildMatchesPayload($clientData, $matches);

            $result = [
                'status'        => $this->scorings::STATUS_COMPLETED,
                'success'       => 0,
                'string_result' => 'Клиент найден в террористических списках',
                'body'          => serialize($payload),
                'end_date'      => date('Y-m-d H:i:s'),
            ];

            $this->scorings->update_scoring($scoringId, $result);
            return $result;

        } catch (\Throwable $e) {
            // в ошибке лучше сбросить плашку, чтобы не висела старая
            $this->setUserTerroristUserData($userId, $scoringId, false);

            $result = [
                'status'        => $this->scorings::STATUS_ERROR,
                'success'       => 0,
                'string_result' => 'Ошибка проверки террористических списков',
                'body'          => mb_substr($e->getMessage(), 0, 2000),
                'end_date'      => date('Y-m-d H:i:s'),
            ];

            $this->scorings->update_scoring($scoringId, $result);
            return $result;
        }
    }

    /**
     * Записываем только 2 ключа, как ты просил:
     * - terrorist_status: 0|1
     * - terrorist_scoring_id: scoring_id или 0
     */
    private function setUserTerroristUserData(int $userId, int $scoringId, bool $found): void
    {
        $this->user_data->set($userId, self::USERDATA_TERRORIST_STATUS, $found ? 1 : 0);
        $this->user_data->set($userId, self::USERDATA_TERRORIST_SCORING_ID, $found ? $scoringId : 0);
    }

    /**
     * Формируем payload для s_scorings.body:
     * - client (нормализованные данные)
     * - matches (до 20)
     * - matched_by (inn|snils|fio_dob)
     */
    private function buildMatchesPayload(array $clientData, array $matches): array
    {
        $out = [];
        $matches = array_slice($matches, 0, self::MATCHES_LIMIT_FOR_BODY);

        foreach ($matches as $m) {
            $row = (array)$m;

            $out[] = [
                'matched_by'     => $this->detectMatchedBy($clientData, $row),

                'source_code'    => (string)($row['source_code'] ?? ''),
                'source_name'    => (string)($row['source_name'] ?? ''),

                'external_id'    => (string)($row['external_id'] ?? ''),
                'full_name'      => (string)($row['full_name'] ?? ''),
                'date_of_birth'  => (string)($row['date_of_birth'] ?? ''),
                'year_of_birth'  => isset($row['year_of_birth']) ? (int)$row['year_of_birth'] : null,

                'inn'            => (string)($row['inn'] ?? ''),
                'snils'          => (string)($row['snils'] ?? ''),

                'first_seen_date'=> (string)($row['first_seen_date'] ?? ''),
                'last_seen_date' => (string)($row['last_seen_date'] ?? ''),

                'list_date'      => (string)($row['list_date'] ?? ''),
                'import_file_id' => isset($row['import_file_id']) ? (int)$row['import_file_id'] : 0,
            ];
        }

        return [
            'client' => [
                'full_name'     => (string)($clientData['full_name'] ?? ''),
                'date_of_birth' => (string)($clientData['date_of_birth'] ?? ''),
                'inn'           => (string)($clientData['inn'] ?? ''),
                'snils'         => (string)($clientData['snils'] ?? ''),
            ],
            'matches' => $out,
            'count'   => count($out),
        ];
    }

    /**
     * Определяет, по каким полям совпало.
     *
     * @return array<int,string> ['inn','snils','fio_dob']
     */
    private function detectMatchedBy(array $clientData, array $matchRow): array
    {
        $matchedBy = [];

        $clientInn   = (string)($clientData['inn'] ?? '');
        $clientSnils = (string)($clientData['snils'] ?? '');
        $clientFio   = (string)($clientData['full_name'] ?? '');
        $clientDob   = (string)($clientData['date_of_birth'] ?? '');

        // matchRow пришёл из модели, там full_name может быть в любом регистре
        // поэтому приведём к UPPER перед сравнением (как в запросе UPPER(subj.full_name)=?)
        $mInn   = (string)($matchRow['inn'] ?? '');
        $mSnils = (string)($matchRow['snils'] ?? '');
        $mFio   = (string)($matchRow['full_name'] ?? '');
        $mDob   = (string)($matchRow['date_of_birth'] ?? '');

        $mFioUpper = $mFio !== '' ? mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $mFio)), 'UTF-8') : '';

        if ($clientInn !== '' && $mInn !== '' && $clientInn === $mInn) {
            $matchedBy[] = 'inn';
        }

        if ($clientSnils !== '' && $mSnils !== '' && $clientSnils === $mSnils) {
            $matchedBy[] = 'snils';
        }

        if ($clientFio !== '' && $clientDob !== '' && $mFioUpper !== '' && $mDob !== ''
            && $clientFio === $mFioUpper && $clientDob === $mDob
        ) {
            $matchedBy[] = 'fio_dob';
        }

        return $matchedBy;
    }
}
