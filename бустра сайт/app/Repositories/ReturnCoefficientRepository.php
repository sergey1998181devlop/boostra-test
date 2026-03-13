<?php

namespace App\Repositories;

use App\Core\Models\BaseModel;
use App\Dto\ReturnCoefficientDto;
use Soap1c;
use Throwable;

class ReturnCoefficientRepository
{
    private BaseModel $model;
    private Soap1c $soap;

    public function __construct()
    {
        $this->model = new BaseModel();
        $this->soap  = new Soap1c();
    }

    /**
     * Получить метрики возвратов и конверсий из 1С через SOAP.
     */
    public function fetchMetricsFrom1C(): ?ReturnCoefficientDto
    {
        $service = 'WebSignal';
        $method  = 'GetMonthlyConversionAndRefundMetrics';

        try {
            $inn_arr = $this->soap->organizations->get_site_inns();

            if (count($inn_arr) < 1) {
                log_error('SRKV: no INNs found, cannot fetch metrics from 1C');
                return null;
            }

            $request = $this->soap->generateObject([
                'ArrayINN' => json_encode($inn_arr, false),
            ]);
            $result = $this->soap->requestSoap($request, $service, $method, 'srkv_metrics.txt');

            if (!empty($result['errors'])) {
                log_error('SRKV: SOAP error fetching metrics from 1C', [
                    'error' => $result['errors'],
                ]);
                return null;
            }

            $data = $result['response'] ?? null;

            if (empty($data) || !is_array($data)) {
                return null;
            }

            return new ReturnCoefficientDto($this->mapFrom1C($data));
        } catch (Throwable $e) {
            log_error('SRKV: failed to fetch metrics from 1C', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Преобразует плоский ответ 1С с русскими ключами в формат ReturnCoefficientDto.
     */
    private function mapFrom1C(array $raw): array
    {
        return [
            'conversion_on_issuance' => (float)($raw['КонверсияНаВыдаче']         ?? 0),
            'conversion_on_payment'  => (float)($raw['КонверсияНаОплате']          ?? 0),
            'overall_return_pct'     => (float)($raw['ОбщийПроцентВозврата']       ?? 0),

            'client_type' => [
                'nk' => (float)($raw['ПроцентВозвратаНК'] ?? 0),
                'pk' => (float)($raw['ПроцентВозвратаПК'] ?? 0),
            ],
            'loan_type' => [
                'pdl' => (float)($raw['ПроцентВозвратаПДЛ'] ?? 0),
                'il'  => (float)($raw['ПроцентВозвратаИЛ']  ?? 0),
            ],
            'platform' => [
                'site'    => (float)($raw['ПроцентВозвратаСайт']    ?? 0),
                'android' => (float)($raw['ПроцентВозвратаAndroid']  ?? 0),
                'ios'     => (float)($raw['ПроцентВозвратаIOS']      ?? 0),
            ],
            'gender' => [
                'male'   => (float)($raw['ПроцентВозвратаМужчины']  ?? 0),
                'female' => (float)($raw['ПроцентВозвратаЖенщины']  ?? 0),
            ],
            'score' => [
                'lt600'  => (float)($raw['ПроцентВозвратаСкорбаллМенее600'] ?? 0),
                'gte600' => (float)($raw['ПроцентВозвратаСкорбаллОт600']    ?? 0),
            ],
            'source' => [
                'organic'      => (float)($raw['ПроцентВозвратаОрганика']         ?? 0),
                'auto_approve' => (float)($raw['ПроцентВозвратаАвтодоборов']      ?? 0),
                'cross_order'  => (float)($raw['ПроцентВозвратаКроссПродаж']      ?? 0),
                'other'        => (float)($raw['ПроцентВозвратаДругиеИсточники']  ?? 0),
            ],
        ];
    }

    /**
     * Проверяет, возвращал ли клиент ФД когда-либо.
     */
    public function hasEverReturnedDoctor(int $userId): bool
    {
        return $this->countReturns('s_credit_doctor_to_user', $userId) > 0;
    }

    /**
     * Проверяет, возвращал ли клиент Вита-мед когда-либо.
     */
    public function hasEverReturnedTvMedical(int $userId): bool
    {
        return $this->countReturns('s_tv_medical_payments', $userId) > 0;
    }

    /**
     * Проверяет, возвращал ли клиент КС (мультиполис) когда-либо.
     */
    public function hasEverReturnedConcierge(int $userId): bool
    {
        return $this->countReturns('s_multipolis', $userId) > 0;
    }

    /**
     * Подсчёт возвратов в таблице доп. услуги за всё время.
     */
    private function countReturns(string $table, int $userId): int
    {
        $this->model->table = $table;

        $this->model
            ->query(
                "SELECT COUNT(*) AS cnt
                   FROM {$this->model->table}
                  WHERE user_id      = ?
                    AND status       = 'SUCCESS'
                    AND return_date IS NOT NULL",
                $userId
            )
            ->result();

        $row = $this->model->getData();

        return $row && isset($row->cnt) ? (int)$row->cnt : 0;
    }
}
