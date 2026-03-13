<?php

declare(strict_types=1);

namespace api\terrorist;

use api\terrorist\models\TerroristSubjects;
use Helpers;

/**
 * TerroristMatchService
 *
 * - На вход принимает уже загруженного user (из скоринга).
 * - Нормализует данные (inn/snils/fio/dob) здесь, а НЕ в модели.
 */
class TerroristMatchService
{
    private const LIMIT = 50;

    private $db;

    private TerroristSubjects $subjectsModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->subjectsModel = new TerroristSubjects($db);
    }

    /**
     * Получить нормализованный payload клиента из user.
     * Удобно, чтобы скоринг мог положить эти данные в body (client block) и/или использовать для matched_by.
     */
    public function getClientData(object $user): array
    {
        return $this->buildClientDataFromUser($user);
    }

    /**
     * Основной метод: user уже получен в скоринге, просто передаем сюда.
     *
     * @return array<int,object>
     */
    public function findUserMatches(object $user): array
    {
        if (empty($user) || empty($user->id)) {
            return [];
        }

        $clientData = $this->buildClientDataFromUser($user);

        // Если нечем матчить — сразу пусто (не нагружаем БД)
        if (
            empty($clientData['inn'])
            && empty($clientData['snils'])
            && (empty($clientData['full_name']) || empty($clientData['date_of_birth']))
        ) {
            return [];
        }

        return $this->subjectsModel->findMatchesForClient($clientData, self::LIMIT);
    }

    /**
     * Совместимость: если нужно матчить по уже подготовленным данным.
     *
     * @return array<int,object>
     */
    public function findMatchesForClient(array $clientData): array
    {
        if (
            empty($clientData['inn'])
            && empty($clientData['snils'])
            && (empty($clientData['full_name']) || empty($clientData['date_of_birth']))
        ) {
            return [];
        }

        return $this->subjectsModel->findMatchesForClient($clientData, self::LIMIT);
    }

    private function buildClientDataFromUser(object $user): array
    {
        $fio = Helpers::getFIO($user);

        $data = [
            'full_name'     => $this->normalizeFullName($fio),
            'date_of_birth' => $this->normalizeDate((string)($user->birth ?? '')),
        ];

        $inn = $this->normalizeInn((string)($user->inn ?? ''));
        if ($inn !== '') {
            $data['inn'] = $inn;
        }

        $snils = $this->normalizeSnils((string)($user->Snils ?? ''));
        if ($snils !== '') {
            $data['snils'] = $snils;
        }

        if ($data['full_name'] === '') {
            unset($data['full_name']);
        }
        if ($data['date_of_birth'] === '') {
            unset($data['date_of_birth']);
        }

        return $data;
    }

    private function normalizeInn(string $inn): string
    {
        $inn = preg_replace('/\D+/', '', $inn);
        return $inn ? (string)$inn : '';
    }

    private function normalizeSnils(string $snils): string
    {
        $snils = preg_replace('/\D+/', '', $snils);
        return $snils ? (string)$snils : '';
    }

    private function normalizeFullName(string $fio): string
    {
        $fio = trim((string)preg_replace('/\s+/u', ' ', $fio));
        return $fio !== '' ? mb_strtoupper($fio, 'UTF-8') : '';
    }


    private function normalizeDate(string $date): string
    {
        $date = trim($date);
        if ($date === '') {
            return '';
        }

        $ts = strtotime($date);
        if ($ts <= 0) {
            return '';
        }

        return date('Y-m-d', $ts);
    }
}
