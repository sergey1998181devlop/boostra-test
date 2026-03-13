<?php

namespace App\Handlers;

use App\Core\Application\Response\Response;
use App\Core\Application\Session\Session;
use App\Repositories\OrdersRepository;
use App\Repositories\UserDncRepository;
use App\Repositories\UserPhoneRepository;
use App\Repositories\UserRepository;
use App\Repositories\VoxSiteDncRepository;
use App\Service\ChangelogService;
use App\Service\VoximplantService;
use Carbon\Carbon;
use Exception;

/**
 * DNC для исходящих звонков робота по пользователю.
 */
class UserDncCallsHandler
{
    private const DEFAULT_DAYS = 5;

    /** @var OrdersRepository */
    private OrdersRepository $ordersRepository;

    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var VoxSiteDncRepository */
    private VoxSiteDncRepository $voxSiteDncRepository;

    /** @var UserDncRepository */
    private UserDncRepository $userDncRepository;

    /** @var UserPhoneRepository */
    private UserPhoneRepository $userPhoneRepository;

    /** @var ChangelogService */
    private ChangelogService $changelogService;

    public function __construct(
        OrdersRepository     $ordersRepository,
        UserRepository       $userRepository,
        VoxSiteDncRepository $voxSiteDncRepository,
        UserDncRepository    $userDncRepository,
        UserPhoneRepository  $userPhoneRepository,
        ChangelogService     $changelogService
    )
    {
        $this->ordersRepository = $ordersRepository;
        $this->userRepository = $userRepository;
        $this->voxSiteDncRepository = $voxSiteDncRepository;
        $this->userDncRepository = $userDncRepository;
        $this->userPhoneRepository = $userPhoneRepository;
        $this->changelogService = $changelogService;
    }

    /**
     * Отключить звонки.
     *
     * @param int $orderId
     * @param int|null $days
     * @param int|null $managerId
     * @return array { success, message, status?, date_end? }
     */
    public function disable(int $orderId, ?int $days = null, ?int $managerId = null): array
    {
        try {
            $days = $days !== null && $days > 0 ? $days : self::DEFAULT_DAYS;

            $context = $this->resolveOrderContext($orderId);
            if (isset($context['success']) && $context['success'] === false) {
                return $context;
            }
            $order = $context['order'];
            $userId = $context['userId'];
            $siteId = $context['siteId'];

            if (empty($order->organization_id)) {
                return [
                    'success' => false,
                    'message' => 'У заявки не указана организация',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
            }

            $organizationId = (int)$order->organization_id;

            $voxRow = $this->voxSiteDncRepository->findBySiteAndOrganization($siteId, $organizationId);
            if ($voxRow === null) {
                return [
                    'success' => false,
                    'message' => 'Нет настроек Vox для этой пары (сайт, организация)',
                    'status' => Response::HTTP_NOT_FOUND
                ];
            }
            if (empty($voxRow->vox_domain) || empty($voxRow->vox_token) || empty($voxRow->outgoing_calls_dnc_list_id)) {
                return [
                    'success' => false,
                    'message' => 'В настройках Vox не заполнены креды или DNC-лист',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
            }

            $user = $this->userRepository->getById($userId);
            $mainPhone = $user && isset($user->phone_mobile) ? (string)$user->phone_mobile : '';
            if ($mainPhone === '') {
                return [
                    'success' => false,
                    'message' => 'У пользователя заявки не указан телефон',
                    'status' => Response::HTTP_BAD_REQUEST
                ];
            }

            $existingDnc = $this->userDncRepository->findActiveByUserIdAndSiteId($userId, $siteId);
            if ($existingDnc !== null) {
                return [
                    'success' => false,
                    'message' => 'Исходящие звонки уже отключены до ' . Carbon::parse($existingDnc->date_end)->format('d.m.Y H:i'),
                    'status' => Response::HTTP_BAD_REQUEST
                ];
            }

            $additionalPhones = $this->userPhoneRepository->getPhoneNumbersByUserId($userId);
            $phones = array_merge([$mainPhone], $additionalPhones);
            $phones = array_unique(array_filter($phones));
            if (empty($phones)) {
                return [
                    'success' => false,
                    'message' => 'У пользователя не указаны телефоны',
                    'status' => Response::HTTP_BAD_REQUEST
                ];
            }

            $contacts = formatPhonesForDnc($phones);
            if (empty($contacts)) {
                return [
                    'success' => false,
                    'message' => 'Нет валидных номеров для добавления в DNC',
                    'status' => Response::HTTP_BAD_REQUEST
                ];
            }

            if ($managerId === null || $managerId <= 0) {
                $managerId = (int)(Session::getInstance()->get('manager_id') ?? 0);
            }
            if ($managerId <= 0) {
                return [
                    'success' => false,
                    'message' => 'Не указан manager_id',
                    'status' => Response::HTTP_BAD_REQUEST
                ];
            }

            $voxService = VoximplantService::fromVoxSiteDncRow($voxRow);
            $dncListId = (int)$voxRow->outgoing_calls_dnc_list_id;
            $comment = "Отключение исходящих звонков по заявке #$orderId, менеджер ID: $managerId, на $days дней";

            $result = $voxService->addDncContacts($contacts, $dncListId, $comment);
            if (!isset($result['success']) || !$result['success']) {
                return [
                    'success' => false,
                    'message' => 'Ошибка при добавлении в DNC-лист: ' . ($result['error'] ?? 'Неизвестная ошибка'),
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR
                ];
            }

            $dncContactIds = $this->getDncContactIds($voxService, $contacts, $dncListId);
            $dateStart = Carbon::now()->format('Y-m-d H:i:s');
            $dateEnd = Carbon::now()->addDays($days)->format('Y-m-d H:i:s');

            $this->userDncRepository->create([
                'user_id' => $userId,
                'phones' => json_encode($contacts),
                'days' => $days,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'manager_id' => $managerId,
                'dnc_contact_ids' => json_encode($dncContactIds),
                'site_id' => $siteId
            ]);

            $this->changelogService->addLog(
                $managerId,
                'disable_outgoing_calls',
                '',
                "Отключение исходящих звонков по заявке #$orderId на $days дней",
                $orderId,
                $userId
            );

            return [
                'success' => true,
                'message' => "Исходящие звонки отключены на $days дней",
                'date_end' => $dateEnd,
                'status' => Response::HTTP_OK
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * Включить звонки.
     *
     * @param int $orderId
     * @param int|null $managerId
     * @return array { success, message, status? }
     */
    public function enable(int $orderId, ?int $managerId = null): array
    {
        try {
            $context = $this->resolveOrderContext($orderId);
            if (isset($context['success']) && $context['success'] === false) {
                return $context;
            }
            $order = $context['order'];
            $userId = $context['userId'];
            $siteId = $context['siteId'];

            $activeDnc = $this->userDncRepository->findActiveByUserIdAndSiteId($userId, $siteId);
            if ($activeDnc === null) {
                return [
                    'success' => false,
                    'message' => 'Нет активной блокировки звонков для этого пользователя и сайта',
                    'status' => Response::HTTP_BAD_REQUEST
                ];
            }

            $organizationId = (int)($order->organization_id ?? 0);
            $voxRow = $organizationId > 0
                ? $this->voxSiteDncRepository->findBySiteAndOrganization($siteId, $organizationId)
                : null;

            if ($voxRow !== null && !empty($voxRow->vox_domain) && !empty($voxRow->vox_token) && !empty($voxRow->outgoing_calls_dnc_list_id)) {
                $voxService = VoximplantService::fromVoxSiteDncRow($voxRow);
                $dncListId = (int)$voxRow->outgoing_calls_dnc_list_id;
                $dncContactIds = $this->parseJsonIds($activeDnc->dnc_contact_ids ?? '');
                if (empty($dncContactIds) && !empty($activeDnc->phones)) {
                    $contacts = $this->parseJsonContacts($activeDnc->phones);
                    $dncContactIds = $this->getDncContactIds($voxService, $contacts, $dncListId);
                }
                foreach ($dncContactIds as $contactId) {
                    $id = (int)$contactId;
                    if ($id > 0) {
                        $voxService->deleteDncContact($id);
                    }
                }
            }

            $deleted = $this->userDncRepository->deleteById((int)$activeDnc->id);
            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Не удалось снять блокировку',
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR
                ];
            }

            if ($managerId === null || $managerId <= 0) {
                $managerId = (int)(Session::getInstance()->get('manager_id') ?? 0);
            }
            if ($managerId <= 0) {
                return [
                    'success' => false,
                    'message' => 'Не указан manager_id',
                    'status' => Response::HTTP_BAD_REQUEST
                ];
            }
            $this->changelogService->addLog(
                $managerId,
                'enable_outgoing_calls',
                '',
                "Включение исходящих звонков по заявке #$orderId",
                $orderId,
                $userId
            );

            return [
                'success' => true,
                'message' => 'Исходящие звонки робота включены',
                'status' => Response::HTTP_OK
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * Разрешить заявку в контекст (order, userId, siteId). При ошибке возвращает массив с success:false.
     *
     * @param int $orderId
     * @return array либо { success:false, message, status }, либо { order, userId, siteId }
     */
    private function resolveOrderContext(int $orderId): array
    {
        $resolved = $this->resolveUserAndSiteByOrderId($orderId);
        if (isset($resolved['success']) && $resolved['success'] === false) {
            return $resolved;
        }
        return [
            'order' => $resolved['order'],
            'userId' => (int) $resolved['user_id'],
            'siteId' => (string) $resolved['site_id'],
        ];
    }

    /**
     * Общая часть: заявка -> user_id, site_id.
     *
     * @param int $orderId
     * @return array либо { success:false, message, status }, либо { order, user_id, site_id }
     */
    private function resolveUserAndSiteByOrderId(int $orderId): array
    {
        $order = $this->ordersRepository->findByIdForDisableRobotCalls($orderId);
        if ($order === null) {
            return [
                'success' => false,
                'message' => 'Заявка не найдена',
                'status' => Response::HTTP_NOT_FOUND
            ];
        }

        $userId = (int)$order->user_id;
        $siteId = $this->userRepository->getSiteIdByUserId($userId);
        if ($siteId === null || $siteId === '') {
            return [
                'success' => false,
                'message' => 'Не удалось определить сайт для заявки',
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY
            ];
        }

        return [
            'order' => $order,
            'user_id' => $userId,
            'site_id' => $siteId,
        ];
    }

    private function getDncContactIds(VoximplantService $voxService, array $contacts, int $dncListId): array
    {
        $ids = [];
        foreach ($contacts as $contact) {
            $response = $voxService->searchDncContacts($contact, $dncListId);
            if (isset($response['success']) && $response['success'] && !empty($response['result'])) {
                foreach ($response['result'] as $item) {
                    $ids[] = $item['id'];
                }
            }
        }
        return $ids;
    }

    /**
     * @param string $json
     * @return array<int|string>
     */
    private function parseJsonIds(string $json): array
    {
        if ($json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }
        return array_values($decoded);
    }

    /**
     * @param string $json
     * @return array<string>
     */
    private function parseJsonContacts(string $json): array
    {
        if ($json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }
        $result = [];
        foreach ($decoded as $item) {
            if (is_string($item)) {
                $result[] = $item;
            }
        }
        return $result;
    }
}
