<?php

namespace App\Service;

use App\Repositories\SmsMessagesRepository;
use App\Repositories\ChangelogRepository;

class BotActionDetailsService
{
    private const MANAGER_ID_AI_BOT = 612;
    private const TIME_WINDOW_SECONDS = 300;
    private const DISABLED_SERVICE_TYPES = [
        'additional_service_repayment',
        'additional_service_partial_repayment',
        'additional_service_multipolis',
        'additional_service_tv_med',
        'additional_service_so_repayment',
        'additional_service_so_partial_repayment',
        'half_additional_service_repayment',
        'half_additional_service_partial_repayment',
        'half_additional_service_so_repayment',
        'half_additional_service_so_partial_repayment'
    ];
    private const PROLONGATION_TYPES = ['prolongation'];

    private SmsMessagesRepository $smsRepository;
    private ChangelogRepository $changelogRepository;
    private TemplateMatcherService $templateMatcher;

    /**
     * @param SmsMessagesRepository $smsRepository
     * @param ChangelogRepository $changelogRepository
     * @param TemplateMatcherService $templateMatcher
     */
    public function __construct(
        SmsMessagesRepository $smsRepository,
        ChangelogRepository $changelogRepository,
        TemplateMatcherService $templateMatcher
    ) {
        $this->smsRepository = $smsRepository;
        $this->changelogRepository = $changelogRepository;
        $this->templateMatcher = $templateMatcher;
    }

    /**
     * Возвращает детализированное описание действия бота для одного звонка.
     *
     * @param string $url
     * @param int $userId
     * @param string $callTime
     * @return string
     */
    public function getActionDetails(string $url, int $userId, string $callTime): string
    {
        $timeFrom = date('Y-m-d H:i:s', strtotime($callTime) - self::TIME_WINDOW_SECONDS);
        $timeTo = date('Y-m-d H:i:s', strtotime($callTime) + self::TIME_WINDOW_SECONDS);

        if (preg_match('#/app/clients$#', $url)) {
            return 'Проверка клиента';
        }

        if (strpos($url, '/app/clients/') !== false && strpos($url, '/unblock') !== false) {
            return 'Разблокировка клиента';
        }

        if (strpos($url, '/app/clients/') !== false && strpos($url, '/block') !== false) {
            return 'Блокировка клиента';
        }

        if (strpos($url, '/app/sms/send') !== false) {
            return $this->getSmsDetails($userId, $timeFrom, $timeTo);
        }

        if (strpos($url, 'disable-additional-services-by-list') !== false) {
            return $this->getDisabledServicesDetails($userId, $timeFrom, $timeTo);
        }

        if (strpos($url, 'switch-prolongation') !== false) {
            return $this->getProlongationDetails($userId, $timeFrom, $timeTo);
        }

        if (strpos($url, 'fromtech-incoming-call') !== false) {
            return 'Регистрация входящего звонка';
        }

        return 'Неизвестное действие';
    }

    /**
     * Рассчитывает действия для пачки звонков.
     *
     * @param array $batchItems [['item' => object, 'call_data' => array]]
     * @param callable|null $labelResolver
     * @return array [call_id => actions]
     */
    public function buildActionsForBatch(array $batchItems, ?callable $labelResolver = null): array
    {
        $actionsMap = [];
        $userIds = [];
        $minTs = null;
        $maxTs = null;

        foreach ($batchItems as $entry) {
            $item = $entry['item'] ?? null;
            if (!$item) {
                continue;
            }
            $callData = $entry['call_data'] ?? [];
            $methodsList = $this->normalizeMethodsList($callData['methods_list'] ?? []);
            if (empty($methodsList)) {
                continue;
            }

            $userId = $item->user_id ?? 0;
            $callTime = $item->created ?? null;
            if ($userId > 0 && $callTime) {
                $callTs = strtotime($callTime);
                if ($callTs !== false) {
                    $userIds[$userId] = true;
                    $minTs = ($minTs === null) ? $callTs : min($minTs, $callTs);
                    $maxTs = ($maxTs === null) ? $callTs : max($maxTs, $callTs);
                }
            }
        }

        $smsByUser = [];
        $changesByUser = [];
        if (!empty($userIds) && $minTs !== null && $maxTs !== null) {
            $timeFrom = date('Y-m-d H:i:s', $minTs - self::TIME_WINDOW_SECONDS);
            $timeTo = date('Y-m-d H:i:s', $maxTs + self::TIME_WINDOW_SECONDS);

            $smsRows = $this->smsRepository->findByUsersTypeAndTime(
                array_keys($userIds),
                'from_tech',
                $timeFrom,
                $timeTo
            );
            foreach ($smsRows as $row) {
                $uid = (int)($row->user_id ?? 0);
                if ($uid <= 0) {
                    continue;
                }
                $smsByUser[$uid][] = [
                    'created_ts' => strtotime($row->created ?? ''),
                    'message' => $row->message ?? '',
                    'send_status' => $row->send_status ?? ''
                ];
            }

            $changeTypes = array_merge(self::DISABLED_SERVICE_TYPES, self::PROLONGATION_TYPES);
            $changes = $this->changelogRepository->findByUsersTypesAndTime(
                array_keys($userIds),
                $changeTypes,
                $timeFrom,
                $timeTo,
                self::MANAGER_ID_AI_BOT
            );
            foreach ($changes as $change) {
                $uid = (int)($change->user_id ?? 0);
                if ($uid <= 0) {
                    continue;
                }
                $changesByUser[$uid][] = [
                    'created_ts' => strtotime($change->created ?? ''),
                    'type' => $change->type ?? '',
                    'old_values' => $change->old_values ?? '',
                    'new_values' => $change->new_values ?? ''
                ];
            }
        }

        foreach ($batchItems as $entry) {
            $item = $entry['item'] ?? null;
            if (!$item) {
                continue;
            }
            $callData = $entry['call_data'] ?? [];
            $methodsList = $this->normalizeMethodsList($callData['methods_list'] ?? []);
            $callId = $item->id ?? null;
            if ($callId === null) {
                continue;
            }
            if (empty($methodsList)) {
                $actionsMap[$callId] = 'Неизвестно';
                continue;
            }

            $userId = $item->user_id ?? 0;
            $callTime = $item->created ?? null;

            if ($userId === 0 || !$callTime) {
                $actionsMap[$callId] = $this->buildActionsWithoutDetails($methodsList, $labelResolver);
                continue;
            }

            $callTs = strtotime($callTime);
            if ($callTs === false) {
                $actionsMap[$callId] = $this->buildActionsWithoutDetails($methodsList, $labelResolver);
                continue;
            }

            $out = [];
            foreach ($methodsList as $url) {
                if (!is_string($url) || $url === '') {
                    continue;
                }
                $detail = $this->getActionDetailsFromBatch(
                    $url,
                    $userId,
                    $callTs,
                    $smsByUser,
                    $changesByUser,
                    $labelResolver
                );
                if ($detail !== '') {
                    $out[] = $detail;
                }
            }

            $out = array_values(array_unique($out));
            $actionsMap[$callId] = empty($out) ? 'Неизвестно' : implode('; ', $out);
        }

        return $actionsMap;
    }

    /**
     * Возвращает детализацию по SMS в заданном временном окне.
     *
     * @param int $userId
     * @param string $timeFrom
     * @param string $timeTo
     * @return string
     */
    private function getSmsDetails(int $userId, string $timeFrom, string $timeTo): string
    {
        if ($userId === 0) {
            return 'Отправка SMS';
        }

        $successCount = $this->smsRepository->countByUserTypeStatusAndTime($userId, 'from_tech', 'success', $timeFrom, $timeTo);
        $errorCount = $this->smsRepository->countByUserTypeStatusAndTime($userId, 'from_tech', 'error', $timeFrom, $timeTo);

        if ($successCount > 0) {
            $sms = $this->smsRepository->findByUserTypeAndTime($userId, 'from_tech', $timeFrom, $timeTo);
            $template = $this->templateMatcher->findTemplateByMessage($sms->message);
            $templateInfo = $template ? " (шаблон #{$template->id}: {$template->name})" : '';
            
            if ($errorCount > 0) {
                return "Отправка SMS{$templateInfo}: {$successCount} успешно, {$errorCount} неуспешно";
            }
            
            if ($successCount > 1) {
                return "Отправка SMS{$templateInfo}: {$successCount} успешно";
            }
            
            return "Отправка SMS{$templateInfo}";
        }

        if ($errorCount > 0) {
            return "Попытка отправки SMS: {$errorCount} неуспешно";
        }

        return 'Отправка SMS';
    }

    /**
     * Возвращает детализацию по отключенным услугам в заданном окне.
     *
     * @param int $userId
     * @param string $timeFrom
     * @param string $timeTo
     * @return string
     */
    private function getDisabledServicesDetails(int $userId, string $timeFrom, string $timeTo): string
    {
        $changes = $this->changelogRepository->findByUserTypeAndTime(
            $userId,
            self::DISABLED_SERVICE_TYPES,
            $timeFrom,
            $timeTo,
            self::MANAGER_ID_AI_BOT
        );

        if (!empty($changes)) {
            $services = array_column($changes, 'type');
            $servicesList = implode(', ', $services);
            return "Отключение доп. услуг: {$servicesList}";
        }

        return 'Отключение доп. услуг';
    }

    /**
     * Возвращает детализацию по пролонгации в заданном окне.
     *
     * @param int $userId
     * @param string $timeFrom
     * @param string $timeTo
     * @return string
     */
    private function getProlongationDetails(int $userId, string $timeFrom, string $timeTo): string
    {
        $changes = $this->changelogRepository->findByUserTypeAndTime(
            $userId,
            self::PROLONGATION_TYPES,
            $timeFrom,
            $timeTo,
            self::MANAGER_ID_AI_BOT
        );

        if (!empty($changes) && isset($changes[0])) {
            $change = $changes[0];
            return $change->new_values == '1'
                ? 'Включение пролонгации'
                : 'Отключение пролонгации';
        }

        return 'Изменение пролонгации';
    }

    /**
     * Нормализует список методов к массиву строк.
     *
     * @param mixed $methodsList
     * @return array
     */
    private function normalizeMethodsList($methodsList): array
    {
        if (is_array($methodsList)) {
            return $methodsList;
        }
        if (is_string($methodsList) && $methodsList !== '') {
            return [$methodsList];
        }
        return [];
    }

    /**
     * Формирует список действий без детализации (fallback).
     *
     * @param array $methodsList
     * @param callable|null $labelResolver
     * @return string
     */
    private function buildActionsWithoutDetails(array $methodsList, ?callable $labelResolver): string
    {
        $out = [];
        foreach ($methodsList as $url) {
            if (!is_string($url) || $url === '') {
                continue;
            }
            $label = $labelResolver ? (string)call_user_func($labelResolver, $url) : $this->resolveActionLabelByType($this->resolveActionType($url));
            if ($label !== '') {
                $out[] = $label;
            }
        }
        $out = array_values(array_unique($out));
        return empty($out) ? 'Неизвестно' : implode(', ', $out);
    }

    /**
     * Определяет тип действия по URL.
     *
     * @param string $url
     * @return string
     */
    private function resolveActionType(string $url): string
    {
        if (preg_match('#/app/clients$#', $url)) {
            return 'client_check';
        }

        if (strpos($url, '/app/clients/') !== false && strpos($url, '/unblock') !== false) {
            return 'client_unblock';
        }

        if (strpos($url, '/app/clients/') !== false && strpos($url, '/block') !== false) {
            return 'client_block';
        }

        if (strpos($url, '/app/sms/send') !== false) {
            return 'sms_send';
        }

        if (strpos($url, 'disable-additional-services-by-list') !== false) {
            return 'disable_services';
        }

        if (strpos($url, 'switch-prolongation') !== false) {
            return 'prolongation';
        }

        if (strpos($url, 'fromtech-incoming-call') !== false) {
            return 'incoming_call';
        }

        return '';
    }

    /**
     * Возвращает человекочитаемую метку по типу действия.
     *
     * @param string $type
     * @return string
     */
    private function resolveActionLabelByType(string $type): string
    {
        switch ($type) {
            case 'client_check':
                return 'Проверка клиента';
            case 'client_unblock':
                return 'Разблокировка клиента';
            case 'client_block':
                return 'Блокировка клиента';
            case 'sms_send':
                return 'Отправка SMS';
            case 'disable_services':
                return 'Отключение доп. услуг';
            case 'prolongation':
                return 'Изменение пролонгации';
            case 'incoming_call':
                return 'Регистрация входящего звонка';
            default:
                return '';
        }
    }

    /**
     * Возвращает детальное описание действия.
     *
     * @param string $url
     * @param int $userId
     * @param int $callTs
     * @param array $smsByUser
     * @param array $changesByUser
     * @param callable|null $labelResolver
     * @return string
     */
    private function getActionDetailsFromBatch(
        string $url,
        int $userId,
        int $callTs,
        array $smsByUser,
        array $changesByUser,
        ?callable $labelResolver
    ): string {
        $actionType = $this->resolveActionType($url);
        switch ($actionType) {
            case 'sms_send':
                return $this->getSmsDetailsFromBatch($userId, $callTs, $smsByUser);
            case 'disable_services':
                return $this->getDisabledServicesDetailsFromBatch($userId, $callTs, $changesByUser);
            case 'prolongation':
                return $this->getProlongationDetailsFromBatch($userId, $callTs, $changesByUser);
            case 'client_check':
            case 'client_unblock':
            case 'client_block':
            case 'incoming_call':
                return $this->resolveActionLabelByType($actionType);
            default:
                if ($labelResolver) {
                    $label = (string)call_user_func($labelResolver, $url);
                    if ($label !== '') {
                        return $label;
                    }
                }
                return '';
        }
    }

    /**
     * Строит текст по SMS-данным.
     *
     * @param int $userId
     * @param int $callTs
     * @param array $smsByUser
     * @return string
     */
    private function getSmsDetailsFromBatch(int $userId, int $callTs, array $smsByUser): string
    {
        if ($userId === 0) {
            return 'Отправка SMS';
        }

        $records = $smsByUser[$userId] ?? [];
        if (empty($records)) {
            return 'Отправка SMS';
        }

        $timeFrom = $callTs - self::TIME_WINDOW_SECONDS;
        $timeTo = $callTs + self::TIME_WINDOW_SECONDS;

        $successCount = 0;
        $errorCount = 0;
        $latestSuccess = null;
        $latestTs = null;

        foreach ($records as $record) {
            $createdTs = $record['created_ts'] ?? null;
            if ($createdTs === null || $createdTs < $timeFrom || $createdTs > $timeTo) {
                continue;
            }
            $status = $record['send_status'] ?? '';
            if ($status === 'success') {
                $successCount++;
                if ($latestTs === null || $createdTs > $latestTs) {
                    $latestTs = $createdTs;
                    $latestSuccess = $record;
                }
            } elseif ($status === 'error') {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            $templateInfo = '';
            $message = $latestSuccess['message'] ?? '';
            if ($message !== '') {
                $template = $this->templateMatcher->findTemplateByMessage($message);
                if ($template) {
                    $templateInfo = " (шаблон #{$template->id}: {$template->name})";
                }
            }

            if ($errorCount > 0) {
                return "Отправка SMS{$templateInfo}: {$successCount} успешно, {$errorCount} неуспешно";
            }

            if ($successCount > 1) {
                return "Отправка SMS{$templateInfo}: {$successCount} успешно";
            }

            return "Отправка SMS{$templateInfo}";
        }

        if ($errorCount > 0) {
            return "Попытка отправки SMS: {$errorCount} неуспешно";
        }

        return 'Отправка SMS';
    }

    /**
     * Строит текст по отключенным услугам.
     *
     * @param int $userId
     * @param int $callTs
     * @param array $changesByUser
     * @return string
     */
    private function getDisabledServicesDetailsFromBatch(int $userId, int $callTs, array $changesByUser): string
    {
        $records = $changesByUser[$userId] ?? [];
        if (empty($records)) {
            return 'Отключение доп. услуг';
        }

        $timeFrom = $callTs - self::TIME_WINDOW_SECONDS;
        $timeTo = $callTs + self::TIME_WINDOW_SECONDS;
        $services = [];

        foreach ($records as $record) {
            $createdTs = $record['created_ts'] ?? null;
            if ($createdTs === null || $createdTs < $timeFrom || $createdTs > $timeTo) {
                continue;
            }
            $type = $record['type'] ?? '';
            if ($type !== '' && in_array($type, self::DISABLED_SERVICE_TYPES, true)) {
                $services[] = $type;
            }
        }

        $services = array_values(array_unique($services));
        if (!empty($services)) {
            return 'Отключение доп. услуг: ' . implode(', ', $services);
        }

        return 'Отключение доп. услуг';
    }

    /**
     * Строит текст по изменениям пролонгации.
     *
     * @param int $userId
     * @param int $callTs
     * @param array $changesByUser
     * @return string
     */
    private function getProlongationDetailsFromBatch(int $userId, int $callTs, array $changesByUser): string
    {
        $records = $changesByUser[$userId] ?? [];
        if (empty($records)) {
            return 'Изменение пролонгации';
        }

        $timeFrom = $callTs - self::TIME_WINDOW_SECONDS;
        $timeTo = $callTs + self::TIME_WINDOW_SECONDS;

        foreach ($records as $record) {
            $createdTs = $record['created_ts'] ?? null;
            if ($createdTs === null || $createdTs < $timeFrom || $createdTs > $timeTo) {
                continue;
            }
            if (($record['type'] ?? '') !== 'prolongation') {
                continue;
            }
            return ($record['new_values'] ?? '') == '1'
                ? 'Включение пролонгации'
                : 'Отключение пролонгации';
        }

        return 'Изменение пролонгации';
    }
}

