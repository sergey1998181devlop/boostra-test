<?php

namespace App\Modules\Clients\Application\Service;

use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\Soap1cAdapter;
use App\Modules\Clients\Application\DTO\BalanceDTO;
use App\Modules\Clients\Application\DTO\ClientInfoRequest;
use App\Modules\Clients\Application\DTO\ClientInfoResponse;
use App\Modules\Clients\Application\DTO\ContractDTO;
use App\Modules\Clients\Application\DTO\IlDetailsDTO;
use App\Modules\Clients\Application\DTO\OrderDTO;
use App\Modules\Clients\Domain\Entity\ActiveLoan;
use App\Modules\Clients\Domain\Repository\ClientRepositoryInterface;
use App\Modules\Clients\Domain\Repository\LoanRepositoryInterface;
use App\Modules\Clients\Domain\Service\BalanceCalculator;
use App\Modules\Clients\Domain\Service\OverdueCalculator;
use Carbon\Carbon;
use Exception;

require_once __DIR__ . "/../../../../../api/Soap1c.php";

/**
 * Сервис для получения информации о клиенте, его заявках, договорах и балансе.
 *
 * Основной сервис модуля Clients, который orchestrates получение данных клиента
 * через различные репозитории и доменные сервисы. Реализует бизнес-логику
 * с приоритетами: s_user_balance -> loan_history -> default values.
 *
 * @package App\Modules\Clients\Application\Service
 */
class ClientInfoService
{
    private ClientRepositoryInterface $clientRepository;
    private LoanRepositoryInterface $loanRepository;
    private BalanceCalculator $balanceCalculator;
    private OverdueCalculator $overdueCalculator;
    private Soap1cAdapter $soap1cAdapter;
    private UserBalanceService $userBalanceService;

    /**
     * @param ClientRepositoryInterface $clientRepository Репозиторий для работы с клиентами
     * @param LoanRepositoryInterface $loanRepository Репозиторий для работы с займами
     * @param BalanceCalculator $balanceCalculator Сервис расчета баланса
     * @param OverdueCalculator $overdueCalculator Сервис расчета просрочки
     * @param Soap1cAdapter $soap1cAdapter Адаптер для работы с 1С (для графиков платежей)
     * @param UserBalanceService $userBalanceService Сервис получения балансов с кэшированием
     */
    public function __construct(
        ClientRepositoryInterface $clientRepository,
        LoanRepositoryInterface   $loanRepository,
        BalanceCalculator         $balanceCalculator,
        OverdueCalculator         $overdueCalculator,
        Soap1cAdapter             $soap1cAdapter,
        UserBalanceService        $userBalanceService
    )
    {
        $this->clientRepository = $clientRepository;
        $this->loanRepository = $loanRepository;
        $this->balanceCalculator = $balanceCalculator;
        $this->overdueCalculator = $overdueCalculator;
        $this->soap1cAdapter = $soap1cAdapter;
        $this->userBalanceService = $userBalanceService;
    }

    /**
     * Получает полную информацию о клиенте по номеру телефона.
     *
     * Ищет клиента, его активный займ и возвращает структурированную информацию
     * включая данные пользователя, заявку, договор и баланс с учетом приоритетов
     * источников данных.
     *
     * @param ClientInfoRequest $request Запрос с номером телефона
     * @return ClientInfoResponse|null Информация о клиенте или null если не найден
     * @throws \Exception
     */
    public function getClientInfo(ClientInfoRequest $request): ?ClientInfoResponse
    {
        $organizationIds = $request->getOrganizationIds();
        if (!empty($organizationIds)) {
            $client = count($organizationIds) === 1
                ? $this->clientRepository->findByPhoneAndOrganizationId($request->getFormattedPhone(), $organizationIds[0])
                : $this->clientRepository->findByPhoneAndOrganizationIds($request->getFormattedPhone(), $organizationIds);
        } else {
            $client = $this->clientRepository->findByPhone($request->getFormattedPhone());
        }

        if (!$client) {
            return null;
        }

        // обновление баланса
        $this->userBalanceService->ensureFreshBalances(
            $client->getUid(),
            $client->getId()
        );

        // Базовая информация о пользователе
        $response = $this->buildUserResponse($client);

        // Получаем займы в зависимости от параметра all_orders
        $loans = $request->getAllOrders()
            ? $this->loanRepository->findAllByUserId($client->getId(), $organizationIds)
            : $this->loanRepository->findActiveByUserId($client->getId(), $organizationIds);

        if (!$loans) {
            return $response; // Возвращаем только данные пользователя, если нет займов
        }

        $loansForResponse = [];

        // Перебираем все займы
        foreach ($loans->getLoans() as $loanArr) {
            // Создаём временный ActiveLoan для совместимости с баланс-калькулятором / buildContractResponse
            $singleLoan = ActiveLoan::createFromArrays($loanArr);

            // Order DTO
            $orderData = $singleLoan->getOrderData();
            $orderDTO = OrderDTO::fromArray($orderData);

            // Contract / Balance data для логики принятия решений
            $balanceData = $singleLoan->getBalanceData();
            $contractData = $singleLoan->getContractData();

            $isDbBalanceRelevant = $this->balanceCalculator->isBalanceRelevantForContract($contractData, $balanceData);
            $loanHistoryEntry = $this->balanceCalculator->findRelevantLoanHistoryEntry($client, $singleLoan);

            if ($isDbBalanceRelevant) {
                // приоритет 1 — использовать s_user_balance
                $balanceDTO = $this->balanceCalculator->calculateFromDatabase($balanceData, $singleLoan);
                $contractDTO = $this->buildContractResponse($singleLoan, $client, $balanceData, $loanHistoryEntry);
            } elseif ($loanHistoryEntry) {
                // приоритет 2 — использовать loan_history
                $balanceDTO = $this->balanceCalculator->calculateFromLoanHistory($loanHistoryEntry);
                $contractDTO = $this->buildContractResponse($singleLoan, $client, null, $loanHistoryEntry);
            } else {
                // приоритет 3 — нет данных
                $balanceDTO = BalanceDTO::createEmpty();
                $contractDTO = $this->buildContractResponse($singleLoan, $client, null, null);
            }

            $loansForResponse[] = [
                'order' => $orderDTO,
                'contract' => $contractDTO,
                'balance' => $balanceDTO,
            ];
        }

        // Находим активный займ для topLevel contract и balance
        $activeLoanData = null;
        foreach ($loansForResponse as $loan) {
            if ($loan['contract']->isActive()) {
                $activeLoanData = $loan;
                break;
            }
        }

        // Если активного займа нет, используем первый из списка
        $topLevelLoan = $activeLoanData ?? ($loansForResponse[0] ?? null);
        $topLevelContract = $topLevelLoan['contract'] ?? null;
        $topLevelBalance = $topLevelLoan['balance'] ?? null;

        return new ClientInfoResponse(
            (string)$client->getId(),
            $client->getFirstname(),
            $client->getLastname(),
            $client->getPatronymic(),
            $client->getPhone(),
            $client->isBlocked(),
            $client->isAutoInformerEnabled(),
            $client->areRecurrentsDisabled(),
            $loansForResponse,
            $topLevelContract,
            $topLevelBalance
        );
    }

    /**
     * Собирает базовую часть ответа с информацией о пользователе.
     *
     * @param mixed $client Объект клиента из репозитория
     * @return ClientInfoResponse Базовый ответ только с данными пользователя
     */
    private function buildUserResponse($client): ClientInfoResponse
    {
        return new ClientInfoResponse(
            (string)$client->getId(),
            $client->getFirstname(),
            $client->getLastname(),
            $client->getPatronymic(),
            $client->getPhone(),
            $client->isBlocked(),
            $client->isAutoInformerEnabled(),
            $client->areRecurrentsDisabled()
        );
    }

    /**
     * Собирает блок 'contract' для ответа.
     *
     * Создает DTO контракта на основе данных займа, включая расчет просрочки,
     * определение даты платежа и логику продления
     *
     * @param mixed $activeLoan Активный займ с данными заказа и контракта
     * @param mixed $client Клиент для расчета просрочки
     * @param array|null $dbBalance Данные баланса из s_user_balance
     * @param array|null $historyEntry Данные из loan_history
     * @return ContractDTO|null DTO контракта
     * @throws Exception
     */
    private function buildContractResponse($activeLoan, $client, ?array $dbBalance, ?array $historyEntry): ?ContractDTO
    {
        $contractData = $activeLoan->getContractData();
        $orderData = $activeLoan->getOrderData();

        // Проверяем наличие контракта - если нет contract_id, создаем базовый контракт
        if (empty($orderData['contract_id']) && !$contractData) {
            $contractData = $this->createFallbackContractData($orderData);
        }

        // Рассчитываем информацию о просрочке
        $overdueInfo = $this->overdueCalculator->calculateOverdueInfo($activeLoan, $client, $historyEntry);

        // Определяем эффективную дату платежа
        $effectivePaymentDate = $this->overdueCalculator->getEffectivePaymentDate($dbBalance, $historyEntry);
        $paymentDateString = $effectivePaymentDate ? $effectivePaymentDate->format('Y-m-d') : null;

        // Логика can_prolongation как в оригинале
        $isBalanceRelevant = $this->balanceCalculator->isBalanceRelevantForContract($contractData, $dbBalance);
        $canProlongation = false;
        if ($dbBalance && $isBalanceRelevant) {
            $canProlongation = $dbBalance['loan_type'] != 'IL' && ($dbBalance['prolongation_count'] ?? 0) < 5;
        }

        // Форматируем дату выдачи
        $issuedAt = '';
        if (!empty($contractData['issuance_date'])) {
            $issuedAt = Carbon::parse($contractData['issuance_date'])->format('Y-m-d');
        }

        // График платежей по займам-инстолментам
        $schedulePayments = (($dbBalance['loan_type'] ?? null) === 'IL' && !empty($contractData['contract_number']))
            ? $this->soap1cAdapter->getSchedulePayments($dbBalance['loan_type'], $contractData['contract_number'])
            : [];

        // Детальная информация по займам-инстолментам
        $ilDetails = [];
        if (($dbBalance['loan_type'] ?? null) === 'IL' && !empty($contractData['contract_number'])) {
            $ilDetailsRaw = $this->soap1cAdapter->getIlDetails($contractData['contract_number']);
            if (!empty($ilDetailsRaw)) {
                $ilDetails = IlDetailsDTO::fromArray($ilDetailsRaw)->toArray();
            }
        }

        return new ContractDTO(
            $orderData['order_id'],
            $contractData['contract_number'] ?? '',
            $contractData['contract_amount'] ?? '',
            $contractData['period'] ?? '',
            $orderData['1c_status'],
            $issuedAt,
            $contractData['return_date'] ?? '',
            $paymentDateString,
            $canProlongation,
            $overdueInfo['is_overdue'],
            $overdueInfo['days_overdue'],
            (bool)($contractData['additional_service_repayment'] ?? false),
            (bool)($contractData['responsible_person_id'] ?? false),
            $contractData['percent'] ?? null,
            $client->getSaleInfo(),
            $client->getBuyer(),
            $client->getBuyerPhone(),
            $dbBalance['loan_type'] ?? $orderData['loan_type'] ?? null,
            $schedulePayments,
            $ilDetails,
            $orderData['is_active'] ?? false
        );
    }

    /**
     * Создает минимальные данные контракта на основе заявки для случаев без контракта.
     *
     * @param array $orderData Данные заявки
     * @return array Минимальные данные контракта
     */
    private function createFallbackContractData(array $orderData): array
    {
        return [
            'contract_number' => '',
            'contract_amount' => $orderData['approve_amount'] ?? $orderData['order_amount'] ?? '0',
            'period' => '30',
            'return_date' => '',
            'issuance_date' => $orderData['order_date'] ?? Carbon::today()->format('Y-m-d'),
            'close_date' => null,
            'responsible_person_id' => null,
            'additional_service_repayment' => false,
            'deleteKD' => true,
            'percent' => $orderData['percent'] ?? null
        ];
    }
}
