<?php

date_default_timezone_set('Europe/Moscow');

header('Content-type: application/json; charset=UTF-8');
header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');

define('ROOT', dirname(__DIR__));
session_start();
chdir('..');

require 'api/Simpla.php';

class TerroristMatchesAjax extends Simpla
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        $resp = ['success' => false];

        $scoringId = (int)($_GET['scoring_id'] ?? 0);
        if ($scoringId <= 0) {
            $resp['message'] = 'Некорректный scoring_id';
            echo json_encode($resp, JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $scoring = $this->scorings->get_scoring($scoringId);
            if (empty($scoring)) {
                $resp['message'] = 'Скоринг не найден';
                echo json_encode($resp, JSON_UNESCAPED_UNICODE);
                exit;
            }

            $payload = [];
            if (!empty($scoring->body)) {
                $payload = @unserialize($scoring->body);
            }
            if (!is_array($payload)) {
                $payload = [];
            }

            $client  = (isset($payload['client']) && is_array($payload['client'])) ? $payload['client'] : [];
            $matches = (isset($payload['matches']) && is_array($payload['matches'])) ? $payload['matches'] : [];

            // нормализуем под фронт (чтобы фронт не падал)
            $clientOut = [
                'full_name'     => (string)($client['full_name'] ?? ''),
                'date_of_birth' => (string)($client['date_of_birth'] ?? ''),
                'inn'           => (string)($client['inn'] ?? ''),
                'snils'         => (string)($client['snils'] ?? ''),
            ];

            $matchesOut = [];
            foreach ($matches as $m) {
                if (!is_array($m)) continue;

                $matchesOut[] = [
                    'matched_by'     => array_values((array)($m['matched_by'] ?? [])), // ['inn','fio_dob'...]
                    'source_code'    => (string)($m['source_code'] ?? ''),
                    'source_name'    => (string)($m['source_name'] ?? ''),
                    'import_file_id' => (int)($m['import_file_id'] ?? 0),
                    'list_date'      => (string)($m['list_date'] ?? ''),

                    'external_id'     => (string)($m['external_id'] ?? ''),
                    'full_name'       => (string)($m['full_name'] ?? ''),
                    'date_of_birth'   => (string)($m['date_of_birth'] ?? ''),
                    'year_of_birth'   => isset($m['year_of_birth']) ? (int)$m['year_of_birth'] : null,
                    'inn'             => (string)($m['inn'] ?? ''),
                    'snils'           => (string)($m['snils'] ?? ''),
                    'first_seen_date' => (string)($m['first_seen_date'] ?? ''),
                    'last_seen_date'  => (string)($m['last_seen_date'] ?? ''),
                ];
            }

            $resp['success'] = true;
            $resp['data'] = [
                'scoring_id' => $scoringId,
                'user_id'    => (int)($scoring->user_id ?? 0),
                'order_id'   => (int)($scoring->order_id ?? 0),
                'client'     => $clientOut,
                'matches'    => $matchesOut,
            ];

            echo json_encode($resp, JSON_UNESCAPED_UNICODE);
            exit;

        } catch (\Throwable $e) {
            $resp['message'] = 'Ошибка загрузки совпадений';
            $this->logging(__METHOD__, 'terrorist_matches_ajax', ['scoring_id' => $scoringId], ['error' => $e->getMessage()], 'terrorist_scoring.txt');

            echo json_encode($resp, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

$doc = new TerroristMatchesAjax();
$doc->run();
