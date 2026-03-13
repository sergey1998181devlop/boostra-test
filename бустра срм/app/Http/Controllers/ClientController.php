<?php

namespace App\Http\Controllers;

use App\Contracts\FromtechIncomingCallServiceInterface;
use App\Core\Application\Application;
use App\Core\Application\Facades\DB;
use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Dto\FromtechIncomingCallDto;
use App\Enums\CommentBlocks;
use App\Enums\ReferenceType;
use App\Handlers\IncomingCallCommentHandler;
use App\Handlers\OutgoingCallCommentHandler;
use App\Models\BlockSms;
use App\Models\OrderData;
use App\Models\Setting;
use App\Models\User;
use App\Modules\Clients\Application\DTO\ClientInfoRequest;
use App\Modules\Clients\Application\Service\ClientInfoService;
use App\Modules\Shared\AdditionalServices\Enum\AdditionalServiceKey;
use App\Service\ChangeLogs\ClientControllerLogService;
use App\Service\ChangelogService;
use App\Service\ClientAccountService;
use Carbon\Carbon;

class ClientController
{
    private IncomingCallCommentHandler $incomingCallHandler;
    private OutgoingCallCommentHandler $outgoingCallHandler;
    private ClientControllerLogService $logService;

    public function __construct(
        IncomingCallCommentHandler $incomingCallHandler,
        OutgoingCallCommentHandler $outgoingCallHandler,
        ClientControllerLogService $logService
    )
    {
        $this->incomingCallHandler = $incomingCallHandler;
        $this->outgoingCallHandler = $outgoingCallHandler;
        $this->logService = $logService;
    }

    public function index(Request $request): Response
    {
        $app = Application::getInstance();
        /** @var ClientInfoService $clientInfoService */
        $clientInfoService = $app->make(ClientInfoService::class);

        $phone = $request->input('phone') ?? $request->json('phone');
        $formattedPhoneNumber = formatPhoneNumber($phone);
        if (!$formattedPhoneNumber) {
            return response()->json([
                'message' => 'Неверный формат номера телефона'
            ], 422);
        }

        $organizationIds = $request->input('organizationIds') ?? $request->json('organizationIds');
        // backward compatibility: support single organizationId
        if (empty($organizationIds)) {
            $organizationId = $request->input('organizationId') ?? $request->json('organizationId');
            $organizationIds = $organizationId !== null ? $organizationId : null;
        }
        $allOrders = (bool)$request->input('all_orders', false);

        $clientInfoRequest = new ClientInfoRequest($formattedPhoneNumber, $organizationIds, $allOrders);
        $clientInfo = $clientInfoService->getClientInfo($clientInfoRequest);

        if (!$clientInfo) {
            return response()->json([
                'message' => 'Пользователь не найден'
            ], 404);
        }

        return response()->json($clientInfo->toArray());
    }

    public function incomingCall(Request $request): Response
    {
        $userData = ['id' => $request->getParam('id')];

        $callData = [
            'call_id' => $request->input('call_id'),
            'tag' => $request->input('tag'),
            'stage' => $request->input('stage'),
            'handled_by' => $request->input('handled_by'),
            'operator_name' => $request->input('operator_name'),
            'assessment' => $request->input('assessment'),
            'record_url' => $request->input('record_url'),
            'blacklisted' => $request->input('blacklisted'),
            'is_sent_analysis' => false,
            'is_sent_complaint_notification' => false,
        ];

        return $this->incomingCallHandler->handle(
            $userData,
            $callData,
            CommentBlocks::INCOMING_CALL
        );
    }

    public function fromtechIncomingCall(Request $request): Response
    {
        $dto = FromtechIncomingCallDto::fromRequest($request);

        $app = Application::getInstance();
        /** @var FromtechIncomingCallServiceInterface $svc */
        $svc = $app->make(FromtechIncomingCallServiceInterface::class);

        return $svc->handle($dto);
    }

    public function outgoingCall(Request $request): Response
    {
        $callbacks = $request->json('callbacks');
        $calls = $callbacks[0]['new_calls']['calls'] ?? [];

        return $this->outgoingCallHandler->handle($calls);
    }

    public function unblockAccount(Request $request): Response
    {
        $managerId = (int)($request->input('manager_id') ?? 50);

        $app = Application::getInstance();
        /** @var ClientAccountService $svc */
        $svc = $app->make(ClientAccountService::class);
        $err = $svc->tryUnblock((int)$request->getParam('id'));
        if ($err !== null) {
            $map = [
                ClientAccountService::ERR_USER_NOT_FOUND => Response::HTTP_NOT_FOUND,
                ClientAccountService::ERR_ALREADY_UNBLOCKED => Response::HTTP_OK,
            ];
            $code = $map[$err] ?? Response::HTTP_BAD_REQUEST;
            return response()->json(['message' => $err], $code);
        }

        $this->logService->unblockAccount((int)$request->getParam('id'), $managerId);

        return response()->json(['message' => 'Личный кабинет успешно разблокирован'], Response::HTTP_OK);
    }

    public function blockAccount(Request $request): Response
    {
        $managerId = (int)($request->input('manager_id') ?? 50);

        $app = Application::getInstance();
        /** @var ClientAccountService $svc */
        $svc = $app->make(ClientAccountService::class);
        $err = $svc->tryBlock((int)$request->getParam('id'));
        if ($err !== null) {
            $map = [
                ClientAccountService::ERR_USER_NOT_FOUND => Response::HTTP_NOT_FOUND,
                ClientAccountService::ERR_ALREADY_BLOCKED => Response::HTTP_OK,
                ClientAccountService::ERR_HAS_ACTIVE_CONTRACTS => Response::HTTP_CONFLICT,
            ];
            $code = $map[$err] ?? Response::HTTP_BAD_REQUEST;
            return response()->json(['message' => $err], $code);
        }

        $this->logService->blockAccount((int)$request->getParam('id'), $managerId);

        return response()->json(['message' => 'Личный кабинет успешно заблокирован'], Response::HTTP_OK);
    }

    public function temporaryUnsubscribeSms(Request $request): Response
    {
        $userId = $request->getParam('id');
        $managerId = (int)($request->input('manager_id') ?? 360);

        if (!is_numeric($userId)) {
            return response()->json(['message' => 'Неверный идентификатор пользователя'], 400);
        }

        $user = (new User())->get(['id', 'phone_mobile'], ['id' => $userId])->getData();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $blockSmsModel = new BlockSms();
        $isBlocked = $blockSmsModel->has(['user_id' => $user['id']])->getData();

        if ($isBlocked) {
            return response()->json(['message' => 'СМС уведомления уже отключены'], 400);
        }

        // Получаем количество дней для временной отписки
        $settingModel = new Setting();
        $temporarySmsUnsubscribeSetting = $settingModel->get(['value'], ['name' => 'temporary_sms_unsubscribe_days'])->getData();

        if (!$temporarySmsUnsubscribeSetting) {
            return response()->json(['message' => 'Настройка временной блокировки СМС не найдена'], 500);
        }

        $temporarySmsUnsubscribeDays = (int)$temporarySmsUnsubscribeSetting['value'];

        $blockedUntil = Carbon::now()->addDays($temporarySmsUnsubscribeDays)->toDateString();

        try {
            $blockSmsModel->insert([
                'user_id' => $user['id'],
                'sms_type' => 'adv',
                'phone' => $user['phone_mobile'],
                'created_at' => Carbon::now()->toDateTimeString(),
                'blocked_until' => Carbon::now()->addDays($temporarySmsUnsubscribeDays)->toDateString(),
            ]);

            $changelogService = new ChangelogService();
            $changelogService->addLog(
                $managerId,
                'pause_sms_subscription',
                '',
                "Остановлен до $blockedUntil",
                null,
                $user['id']
            );
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка при отключении СМС уведомлений'], 500);
        }

        return response()->json(['message' => 'СМС уведомления отключены']);
    }

    public function getReferences(Request $request): Response
    {
        $userId = $request->getParam('id');
        $referenceType = $request->input('type');
        $availableReferenceTypes = ReferenceType::toArray();

        if (!in_array($referenceType, $availableReferenceTypes)) {
            return response()->json([
                'message' => 'Неверный тип справки'
            ], 422);
        }

        $userData = (new User)->get(['id', 'loan_history'], [
            'id' => $userId
        ])->getData();

        if (!empty($userData) && isset($userData['loan_history'])) {
            $loanHistory = json_decode($userData['loan_history'], true);
            if (!empty($loanHistory)) {
                $references = [];
                foreach ($loanHistory as $item) {
                    if ($referenceType == ReferenceType::ZAKRITIE && empty($item['close_date'])) {
                        continue;
                    }

                    $references[] = $item['number'];
                }
            }
        }

        $changelogService = new ChangelogService();
        $changelogService->addLog(
            360,
            'get_client_references',
            serialize(['user_id' => $userId, 'reference_type' => $referenceType]),
            serialize($references ?? []),
            null,
            $userId
        );

        if (!empty($references)) {
            return response()->json($references);
        }

        return response()->json(['message' => 'На данный момент у вас нет открытых договоров']);
    }

    public function getReference(Request $request): Response
    {
        $referenceType = $request->input('type');
        $availableReferenceTypes = ReferenceType::toArray();
        $changelogService = new ChangelogService();

        if (!in_array($referenceType, $availableReferenceTypes)) {
            $changelogService->addLog(
                360,
                'get_reference',
                '',
                serialize(['message' => 'Неверный тип справки']),
            );

            return response()->json([
                'message' => 'Неверный тип справки'
            ], 422);
        }

        $formattedReferenceNumber = formatReferenceNumber($request->input('number'));
        if (!$formattedReferenceNumber) {
            $changelogService->addLog(
                360,
                'get_reference',
                '',
                serialize(['message' => 'Неверный формат номера договора']),
            );

            return response()->json([
                'message' => 'Неверный формат номера договора'
            ], 422);
        }

        $url_1c = config('services.1c.url');
        $work_1c_db = config('services.1c.db');
        if (!empty($url_1c) && !empty($work_1c_db)) {
            try {
                $uid_client = new \SoapClient($url_1c . $work_1c_db . "/ws/WebSignal.1cws?wsdl");
                $response = $uid_client->__soapCall(
                    'ReferenceClose',
                    [
                        [
                            'НомерЗайма' => $formattedReferenceNumber,
                            'ВидСправки' => $referenceType,
                        ]
                    ]
                );

                $changelogService->addLog(
                    360,
                    'get_reference',
                    '',
                    serialize(['type' => $referenceType, 'number' => $formattedReferenceNumber]),
                );
            } catch (\Exception $exception) {
                error_log($exception->getMessage());
            }

            if (!empty($response) && !empty($response->return) && (strlen($response->return) > 10)) {
                $fileName = tempnam(sys_get_temp_dir(), 'TMP_');
                file_put_contents($fileName, base64_decode($response->return));

                $changelogService->addLog(
                    360,
                    'get_reference',
                    '',
                    serialize(['filename' => $fileName]),
                );

                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="Loan-' . $formattedReferenceNumber . '"');
                header('Content-Transfer-Encoding: binary');
                header('Accept-Ranges: bytes');

                readfile($fileName);

                return response()->json(['message' => 'Справка найдена']);
            }
        }

        return response()->json(['message' => 'Справка не найдена'], 404);
    }

    /**
     * Идентифицирует клиента по номеру контракта или телефону и возвращает информацию о клиенте.
     *
     * @param Request $request Объект запроса, содержащий параметры 'contract_number' или 'phone'.
     * @return Response JSON-ответ с данными клиента или сообщением об ошибке, если клиент не найден.
     *
     * @throws \Exception Если произошла ошибка при выполнении запроса к базе данных.
     */
    public function identification(Request $request): Response
    {
        $contractNumber = $request->input('contract_number') ?? $request->json('contract_number');
        $phone = $request->input('phone') ?? $request->json('phone');

        $organizationIds = $request->input('organization_ids') ?? $request->json('organization_ids');
        if (!empty($organizationIds)) {
            $organizationIds = array_map('intval', array_filter((array)$organizationIds, 'is_numeric'));
            $organizationIds = empty($organizationIds) ? null : array_values($organizationIds);
        } else {
            $organizationIds = null;
        }

        // Build dynamic organization filter
        $orgFilter = '';
        $orgParams = [];
        if (!empty($organizationIds)) {
            $orgPlaceholders = [];
            foreach ($organizationIds as $i => $orgId) {
                $key = ':org_id_' . $i;
                $orgPlaceholders[] = $key;
                $orgParams[$key] = $orgId;
            }
            $orgIn = implode(', ', $orgPlaceholders);
        }

        if ($contractNumber) {
            if (!empty($organizationIds)) {
                $orgCondition = "EXISTS (
                        SELECT 1 FROM s_orders so 
                        WHERE so.id = c.order_id 
                          AND so.organization_id IN ({$orgIn})
                      )";
            } else {
                $orgCondition = '1=1';
            }

            $sql = "SELECT 
                        c.user_id AS user_id,
                        c.user_uid AS user_uid,
                        u.phone_mobile,
                        c.number AS contract_number,
                        o.additional_service_repayment,
                        o.id AS order_id,
                        o.`1c_status` AS status,
                        ub.sale_info,
                        ub.buyer
                    FROM s_contracts c
                    LEFT JOIN s_users u ON c.user_id = u.id
                    LEFT JOIN s_user_balance ub ON ub.user_id = u.id
                    LEFT JOIN s_orders o ON o.id = c.order_id AND o.`1c_status` = :status
                    WHERE c.number = :number
                      AND ({$orgCondition})
                    ORDER BY o.date DESC
                    LIMIT 1";

            $params = array_merge(
                [':number' => $contractNumber, ':status' => '5.Выдан'],
                $orgParams
            );

            $client = DB::db()->query($sql, $params)->fetch(\PDO::FETCH_ASSOC);
        } elseif ($phone) {
            $phone = formatPhoneNumber($phone);

            if (!empty($organizationIds)) {
                $orgCondition = "EXISTS (
                        SELECT 1 FROM s_orders so 
                        WHERE so.user_id = u.id 
                          AND so.organization_id IN ({$orgIn})
                      )";
            } else {
                $orgCondition = '1=1';
            }

            $sql = "SELECT 
                        u.id AS user_id,
                        u.UID AS user_uid,
                        u.phone_mobile,
                        c.number AS contract_number,
                        o.additional_service_repayment,
                        o.id AS order_id,
                        o.`1c_status` AS status,
                        ub.sale_info,
                        ub.buyer
                    FROM s_users u
                    LEFT JOIN s_user_balance ub ON ub.user_id = u.id
                    LEFT JOIN s_orders o ON u.id = o.user_id AND o.`1c_status` = :status
                    LEFT JOIN s_contracts c ON o.id = c.order_id
                    WHERE u.phone_mobile = :phone
                      AND ({$orgCondition})
                    ORDER BY o.date DESC
                    LIMIT 1";

            $params = array_merge(
                [':phone' => $phone, ':status' => '5.Выдан'],
                $orgParams
            );

            $client = DB::db()->query($sql, $params)->fetch(\PDO::FETCH_ASSOC);
        } else {
            return response()->json('Client not found', 404);
        }

        if (empty($client)) {
            return response()->json('Client not found', 404);
        }

        if (!empty($client['user_id'])) {
            $autoInformerDisabled = (bool)DB::db()->query(
                "SELECT 1 FROM s_user_dnc WHERE user_id = :user_id AND date_start <= NOW() AND date_end >= NOW() LIMIT 1",
                [':user_id' => $client['user_id']]
            )->fetchColumn();

            $client['auto_informer_enabled'] = !$autoInformerDisabled;

            $recurrentsDisabled = (bool)DB::db()->query(
                "SELECT 1 FROM b2p_cards WHERE user_id = :user_id AND deleted = 0 AND deleted_by_client = 0 AND autodebit = 0 LIMIT 1",
                [':user_id' => $client['user_id']]
            )->fetchColumn();

            $client['recurrents_disabled'] = $recurrentsDisabled;
        }

        if (!empty($client['order_id'])) {
            $allServices = array_merge(
                AdditionalServiceKey::getAdditionalServiceList(),
                AdditionalServiceKey::getHalfAdditionalServiceList()
            );

            $disabledRows = (new OrderData())->select('key', [
                'order_id' => $client['order_id'],
                'value' => 1
            ])->getData();

            $enabledServices = array_values(array_diff($allServices, $disabledRows));

            $client['connected_services'] = $enabledServices;
        } else {
            $client['connected_services'] = [];
            if (!array_key_exists('auto_informer_enabled', $client)) {
                $client['auto_informer_enabled'] = true;
            }
            if (!array_key_exists('recurrents_disabled', $client)) {
                $client['recurrents_disabled'] = false;
            }
        }

        return response()->json($client);
    }
}
