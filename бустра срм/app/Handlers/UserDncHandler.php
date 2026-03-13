<?php

namespace App\Handlers;

use App\Contracts\UserDncHandlerContract;
use App\Core\Application\Response\Response;
use App\Models\Manager;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\UserDnc;
use App\Models\UserPhone;
use App\Service\ChangelogService;
use App\Service\VoximplantService;
use Carbon\Carbon;
use Exception;

class UserDncHandler implements UserDncHandlerContract
{
    private const VOXIMPLANT_MANAGER_NAME = "Voximplant";
    private VoximplantService $voximplantService;
    private ChangelogService $changelogService;

    public function __construct()
    {
        $this->voximplantService = new VoximplantService();
        $this->changelogService = new ChangelogService();
    }

    /**
     * Обработка создания DNC-записи
     * 
     * @param string $phone Номер телефона
     * @param int $days Количество дней блокировки
     * @param int|null $managerId ID менеджера (опционально)
     * @return array Результат операции
     */
    public function handle(string $phone, int $days, ?int $managerId = null): array
    {
        try {
            // Валидация входных данных
            $validationResult = $this->validateInput($phone);
            if (!$validationResult['success']) {
                return $validationResult;
            }

            $formattedPhone = formatPhoneNumber($phone);
            if (!$formattedPhone) {
                return [
                    'success' => false,
                    'message' => 'Неверный формат номера телефона',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
            }

            // Поиск пользователя
            $userId = $this->findUserIdByPhone($formattedPhone);
            if (empty($userId)) {
                return [
                    'success' => false,
                    'message' => 'Пользователь с указанным номером телефона не найден',
                    'status' => Response::HTTP_NOT_FOUND
                ];
            }

            // Проверка наличия активной блокировки
            $existingDncResult = $this->checkExistingDnc($userId);
            if (!$existingDncResult['success']) {
                return $existingDncResult;
            }

            // Получение всех телефонов пользователя
            $phones = $this->getUserPhones($userId, $formattedPhone);
            if (empty($phones)) {
                return [
                    'success' => false,
                    'message' => 'У пользователя не указаны телефоны',
                    'status' => Response::HTTP_BAD_REQUEST
                ];
            }

            // Форматирование и валидация телефонов
            $contacts = $this->formatPhonesForVoximplant($phones);

            // Получение ID менеджера, если не передан
            if (is_null($managerId)) {
                $managerId = $this->getVoximplantManagerId();
            }
            
            // Добавление номеров в DNC-лист Voximplant
            $comment = "Отключение исходящих звонков по запросу менеджера ID: $managerId на $days дней";
            $result = $this->voximplantService->addDncContacts($contacts, VoximplantService::OUTGOING_CALLS_DNC_LIST_ID, $comment);

            if (!isset($result['success']) || !$result['success']) {
                return [
                    'success' => false,
                    'message' => 'Ошибка при добавлении в DNC-лист: ' . ($result['error'] ?? 'Неизвестная ошибка'),
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR
                ];
            }

            // Получение ID контактов в DNC-листе
            $dncContactIds = $this->getDncContactIds($contacts);

            // Сохранение информации в базе данных
            $dateStart = Carbon::now()->format('Y-m-d H:i:s');
            $dateEnd = Carbon::now()->addDays($days)->format('Y-m-d H:i:s');

            $userDnc = new UserDnc();
            $userDnc->insert([
                'user_id' => $userId,
                'phones' => json_encode($contacts),
                'days' => $days,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'manager_id' => $managerId,
                'dnc_contact_ids' => json_encode($dncContactIds)
            ]);

            // Добавление записи в changelog
            $this->changelogService->addLog(
                $managerId,
                'disable_outgoing_calls',
                '',
                "Отключение исходящих звонков на $days дней",
                null,
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
     * Обработка создания DNC-записи до даты следующего платежа
     * 
     * @param string $phone Номер телефона
     * @param int|null $managerId ID менеджера
     * @return array Результат операции
     */
    public function handleByPaymentDate(string $phone, ?int $managerId = null): array
    {
        try {
            // Валидация входных данных
            $validationResult = $this->validateInput($phone);
            if (!$validationResult['success']) {
                return $validationResult;
            }

            $formattedPhone = formatPhoneNumber($phone);
            if (!$formattedPhone) {
                return [
                    'success' => false,
                    'message' => 'Неверный формат номера телефона',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
            }

            // Поиск пользователя
            $userId = $this->findUserIdByPhone($formattedPhone);
            if (empty($userId)) {
                return [
                    'success' => false,
                    'message' => 'Пользователь с указанным номером телефона не найден',
                    'status' => Response::HTTP_NOT_FOUND
                ];
            }

            // Получение даты следующего платежа
            $paymentDate = (new UserBalance())->get(['payment_date'], [
                'user_id' => $userId,
                'payment_date[>]' => date('Y-m-d H:i:s')
            ])->getData()['payment_date'] ?? null;

            if (empty($paymentDate)) {
                return [
                    'success' => false,
                    'message' => 'Не найдена дата следующего платежа для пользователя',
                    'status' => Response::HTTP_NOT_FOUND
                ];
            }

            // Расчет количества дней между текущей датой и датой платежа
            $currentDate = Carbon::now();
            $paymentDateCarbon = Carbon::parse($paymentDate);
            $days = $currentDate->diffInDays($paymentDateCarbon);

            // Вызов основного метода для создания DNC-записи
            return $this->handle($phone, $days, $managerId);

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * Валидация входных данных
     *
     * @param string $phone Номер телефона
     * @return array Результат валидации
     */
    private function validateInput(string $phone): array
    {
        if (empty($phone)) {
            return [
                'success' => false,
                'message' => 'Не указан номер телефона',
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY
            ];
        }

        return ['success' => true];
    }

    /**
     * Поиск ID пользователя по номеру телефона
     *
     * @param string $formattedPhone Отформатированный номер телефона
     * @return int|null ID пользователя или null, если пользователь не найден
     */
    private function findUserIdByPhone(string $formattedPhone): ?int
    {
        return (new User())->get(['id'], ['phone_mobile' => $formattedPhone])->getData()['id'] ?? null;
    }

    /**
     * Проверка наличия активной блокировки
     *
     * @param int $userId ID пользователя
     * @return array Результат проверки
     */
    private function checkExistingDnc(int $userId): array
    {
        $existingDnc = (new UserDnc())->get(
            ['id', 'date_end'],
            [
                'user_id' => $userId,
                'date_end[>]' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            ['ORDER' => ['id' => 'DESC'], 'LIMIT' => 1]
        )->getData();

        if (!empty($existingDnc)) {
            return [
                'success' => false,
                'message' => 'Исходящие звонки уже отключены до ' . Carbon::parse($existingDnc[0]['date_end'])->format('d.m.Y H:i'),
                'status' => Response::HTTP_BAD_REQUEST
            ];
        }

        return ['success' => true];
    }

    /**
     * Получение всех телефонов пользователя
     *
     * @param int $userId ID пользователя
     * @param string $mainPhone Основной номер телефона
     * @return array Массив телефонов
     */
    private function getUserPhones(int $userId, string $mainPhone): array
    {
        $additionalPhones = [];
        $userPhones = (new UserPhone())->get(['phone'], [
            'user_id' => $userId,
            'is_active' => 1
        ])->getData();

        if (!empty($userPhones)) {
            foreach ($userPhones as $phone) {
                $additionalPhones[] = $phone['phone'];
            }
        }

        $allPhones = [];
        if (!empty($mainPhone)) {
            $allPhones[] = $mainPhone;
        }
        
        return array_merge($allPhones, $additionalPhones);
    }

    /**
     * Форматирование телефонов для Voximplant
     *
     * @param array $phones Массив телефонов
     * @return array Отформатированные телефоны (валидные, без дубликатов)
     */
    private function formatPhonesForVoximplant(array $phones): array
    {
        return formatPhonesForDnc($phones);
    }


    /**
     * Получение ID контактов в DNC-листе
     *
     * @param array $contacts Массив контактов
     * @return array Массив ID контактов
     */
    private function getDncContactIds(array $contacts): array
    {
        $dncContactIds = [];
        foreach ($contacts as $contact) {
            $response = $this->voximplantService->searchDncContacts($contact, VoximplantService::OUTGOING_CALLS_DNC_LIST_ID);
            
            if (isset($response['success']) && $response['success']) {
                foreach ($response['result'] as $item) {
                    $dncContactIds[] = $item['id'];
                }
            }
        }
        
        return $dncContactIds;
    }

    /**
     * Получение ID менеджера Voximplant из базы данных
     * 
     * @return int|null ID менеджера или null, если менеджер не найден
     */
    private function getVoximplantManagerId(): ?int
    {
        $manager = (new Manager())->get(['id'], [
            'name' => self::VOXIMPLANT_MANAGER_NAME,
            'blocked' => 0
        ])->getData();
        
        if (!empty($manager)) {
            return $manager['id'];
        }
        
        return null;
    }
}
