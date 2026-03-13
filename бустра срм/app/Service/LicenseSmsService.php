<?php

namespace App\Service;

use App\Dto\ExtraServicesInformDto;
use App\Dto\SendLicenseSmsDto;
use App\Dto\SendSmsDto;
use App\Enums\LicenseServiceType;
use App\Repositories\CommentRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ExtraServicesInformRepository;
use App\Repositories\ExtraServicePurchaseRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserBalanceRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Сервис для отправки SMS с лицензионными ключами
 */
class LicenseSmsService
{
    private SmsService $smsService;
    private UserRepository $userRepository;
    private UserBalanceRepository $userBalanceRepository;
    private DocumentRepository $documentRepository;
    private CommentRepository $commentRepository;
    private ExtraServicesInformRepository $extraServicesInformRepository;
    private ExtraServicePurchaseRepository $extraServicePurchaseRepository;

    public function __construct(
        SmsService $smsService,
        UserRepository $userRepository,
        UserBalanceRepository $userBalanceRepository,
        DocumentRepository $documentRepository,
        CommentRepository $commentRepository,
        ExtraServicesInformRepository $extraServicesInformRepository,
        ExtraServicePurchaseRepository $extraServicePurchaseRepository
    ) {
        $this->smsService = $smsService;
        $this->userRepository = $userRepository;
        $this->userBalanceRepository = $userBalanceRepository;
        $this->documentRepository = $documentRepository;
        $this->commentRepository = $commentRepository;
        $this->extraServicesInformRepository = $extraServicesInformRepository;
        $this->extraServicePurchaseRepository = $extraServicePurchaseRepository;
    }

    /**
     * Отправка SMS с лицензионным ключом
     *
     * @param SendLicenseSmsDto $dto
     * @return array
     * @throws GuzzleException
     */
    public function sendLicenseSms(SendLicenseSmsDto $dto): array
    {
        try {
            $user = $this->userRepository->getByOrderId($dto->order_id);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Пользователь не найден для заказа ' . $dto->order_id
                ];
            }

            $zaim = $this->userBalanceRepository->getByUserId($user->id);
            if (empty($zaim->zaim_number) || $zaim->zaim_summ == 0) {
                return [
                    'success' => false,
                    'message' => 'Займ не найден или сумма равна нулю'
                ];
            }

            $policyId = $this->getPolicyIdByType($dto->order_id, $dto->type);
            if (!$policyId) {
                $serviceName = LicenseServiceType::getName($dto->type);
                return [
                    'success' => false,
                    'message' => 'Полис для услуги ' . $serviceName . ' не найден'
                ];
            }

            $licenseKey = $this->getLicenseKey($policyId);
            if (empty($licenseKey)) {
                $serviceName = LicenseServiceType::getName($dto->type);
                return [
                    'success' => false,
                    'message' => 'Лицензионный ключ для услуги ' . $serviceName . ' отсутствует'
                ];
            }

            $serviceName = LicenseServiceType::getName($dto->type);
            $smsText = 'Ваш лицензионный ключ ' . $serviceName . ': ' . $licenseKey;

            $smsDto = new SendSmsDto();
            $smsDto->phone = $dto->phone;
            $smsDto->message = $smsText;

            $result = $this->smsService->send($smsDto);

            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => 'Ошибка отправки SMS: ' . $result['message']
                ];
            }

            $this->logSmsSent($dto, $user->id, $zaim->zaim_number, $licenseKey);

            $this->addComment($dto, $user->id, $serviceName);

            logger()->info('SMS с лицензионным ключом отправлена', [
                'order_id' => $dto->order_id,
                'type' => $dto->type,
                'phone' => $dto->phone,
                'service_name' => $serviceName,
                'contract' => $zaim->zaim_number,
                'sms_id' => $result['data']['id'] ?? null
            ]);

            return [
                'success' => true,
                'message' => 'SMS с лицензионным ключом отправлена',
                'data' => [
                    'phone' => $dto->phone,
                    'service' => $serviceName,
                    'contract' => $zaim->zaim_number,
                    'sms_id' => $result['data']['id'] ?? null
                ]
            ];

        } catch (Exception $e) {
            logger('error')->error('Ошибка отправки SMS с лицензионным ключом', [
                'exception' => $e->getMessage(),
                'order_id' => $dto->order_id,
                'type' => $dto->type,
                'phone' => $dto->phone
            ]);
            return [
                'success' => false,
                'message' => 'Ошибка отправки SMS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Получение лицензионного ключа из полиса
     *
     * @param int $policyId
     * @return string|null
     */
    private function getLicenseKey(int $policyId): ?string
    {
        $policy = $this->documentRepository->getById($policyId);
        
        if (empty($policy)) {
            return null;
        }

        if (!empty($policy->params)) {
            $params = is_string($policy->params) ? unserialize($policy->params) : $policy->params;
            
            if (is_array($params)) {
                return $params['license_key'] ?? null;
            } elseif (is_object($params)) {
                return $params->license_key ?? null;
            }
        }

        return null;
    }

    /**
     * Получение policy_id по типу услуги
     *
     * @param int $orderId
     * @param string $type
     * @return int|null
     */
    private function getPolicyIdByType(int $orderId, string $type): ?int
    {
        $policyType = LicenseServiceType::getPolicyType($type);
        if (!$policyType) {
            return null;
        }

        $purchase = $this->extraServicePurchaseRepository->getLastNotFullyReturnedByOrderAndType($orderId, $type);
        if (!$purchase) {
            return null;
        }

        $docs = $this->documentRepository->getByOrderAndType($orderId, $policyType);
        if (empty($docs)) {
            return null;
        }

        $purchaseTs = strtotime($purchase->date_added);
        $bestDoc = null;
        $bestDelta = PHP_INT_MAX;
        foreach ($docs as $doc) {
            $docTs = strtotime($doc->created_at ?? '');
            if (!$docTs) {
                continue;
            }
            $delta = abs($purchaseTs - $docTs);
            if ($delta < $bestDelta) {
                $bestDelta = $delta;
                $bestDoc = $doc;
            }
        }

        return $bestDoc ? $bestDoc->id : null;
    }

    /**
     * Логирование отправки SMS
     *
     * @param SendLicenseSmsDto $dto
     * @param int $userId
     * @param string $contract
     * @param string $licenseKey
     */
    private function logSmsSent(SendLicenseSmsDto $dto, int $userId, string $contract, string $licenseKey): void
    {
        $serviceName = LicenseServiceType::getName($dto->type);

        $smsInform = ExtraServicesInformDto::fromArray([
            'user_id' => $userId,
            'contract' => $contract,
            'order_id' => $dto->order_id,
            'manager_id' => $dto->manager_id,
            'service_name' => $serviceName,
            'sms_phone' => $dto->phone,
            'sms_template_id' => null,
            'sms_type' => 'Key',
            'license_key' => $licenseKey,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        try {
            $this->extraServicesInformRepository->insert($smsInform);
        } catch (Exception $e) {
            logger('error')->error('Ошибка сохранения SMS в s_extra_services_informs', [
                'exception' => $e->getMessage(),
                'user_id' => $userId,
                'order_id' => $dto->order_id
            ]);
        }
    }

    /**
     * Добавление комментария
     *
     * @param SendLicenseSmsDto $dto
     * @param int $userId
     * @param string $serviceName
     */
    private function addComment(SendLicenseSmsDto $dto, int $userId, string $serviceName): void
    {
        $text = 'Отправлено SMS с лицензионным ключом по доп.услуге ' . $serviceName . ' на номер ' . $dto->phone;

        try {
            $this->commentRepository->insert([
                'manager_id' => $dto->manager_id,
                'user_id' => $userId,
                'block' => 'services',
                'text' => $text,
                'created' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            logger('error')->error('Ошибка добавления комментария', [
                'exception' => $e->getMessage(),
                'user_id' => $userId,
                'order_id' => $dto->order_id
            ]);
        }
    }
}
