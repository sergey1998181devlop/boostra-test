<?php
/**
 * @author Jewish Programmer
 */

use Traits\BKIHelperTrait;
use Traits\HasScoringsForAxiTrait;

require_once __DIR__ . '/../scorings/BoostraPTI.php';
require_once __DIR__ . '/Traits/HasScoringsForAxiTrait.php';
require_once __DIR__ . '/Traits/BKIHelperTrait.php';
require_once __DIR__ . '/Services/AxiTokenStorage.php';

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}

class DbrainAxi extends Simpla
{
    use HasScoringsForAxiTrait;
    use BKIHelperTrait;

    public const CURL_TIMEOUT = 15;

    /**
     * Ключ объекта суммы с ПДН в ответе скоринга
     */
    public const KEY_AMOUNT_WITH_PDN = 'sum';
    public const MAX_WAIT_TIME = 600; //in seconds
    /**
     * Refinance Axi category
     * @var string PRODUCT_CATEGORY_REFINANCE
     */
    private const PRODUCT_CATEGORY_REFINANCE = 'boostra2_REFIN';
    /**
     * Refinance Axi Code
     * @var string PRODUCT_CODE_REFINANCE
     */
    private const PRODUCT_CODE_REFINANCE = 'ps_boostra2_REFIN';
    /**
     * Log file for refinance strategy
     * @var string LOG_FILE_REFINANCE
     */
    private const LOG_FILE_REFINANCE = 'axi_refinance.txt';
    private $service_url;

    /** @var object|null Конфиг Axi из БД */
    private $axiConfig = null;

    /**
     * @var string DECISION
     */
    private const DECISION = 'GetApplicationDecision';

    /**
     * @var string CREATE
     */
    private const CREATE = 'CreateApplication';
    private const DSS_FICO_4_10 = 'FICO_4_10';

    private $create_action;

    /**
     * @var string RESPONSE
     */
    private const RESPONSE = 'GetApplication';

    /**
     * Robot browser data
     * @var string BROWSER
     */
    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36';

    private const DEFAULT_CH_FILES_TYPE_TO_SAVE = [
        Axi::SSP_REPORT,
        Axi::CH_REPORT,
    ];


    /**
     * @var ?CurlHandle $ch
     */
    private $ch = null;

    /**
     * @var ?string $error
     */
    private $error = null;

    /**
     * @var ?string $xml
     */
    private $xml = null;

    /**
     * Type of request [short, default]
     * @var string $type
     */
    private $type = 'short';

    /**
     * @var array $data
     */
    protected $data;

    /**
     * CALL type of AXI [SCORISTA, START, FSSP, FNS]
     * @var string $command
     */
    protected $command = 'START';

    /**
     * minimum time for valida passport date - 01.01.1997
     * @var int PASSPORT_VALID_TIME
     */
    private const PASSPORT_VALID_TIME = 852076800;

    /**
     * Версия текущей стратегии
     * @var string
     */
    private $version = '';

    /**
     * card number of user
     * @var string $cardNumber
     */
    private $cardNumber = '';

    /**
     * @var string $createResponse
     */
    private $createResponse = '';

    /**
     * @var string $infoResponse
     */
    public $infoResponse = '';

    private $axilogin;
    private $axipassword;

    public const STATUS_RESPONSE = 'EXTERNAL_RESPONSE';
    public const STATUS_REQUEST = 'EXTERNAL_REQUEST';
    public const AXICREDIT_RESPONSE = 'AXICREDIT_RESPONSE';
    private const AXI_CREDENTIONS_KEY = 'axi_credentions';

    /**
     * Причины отказа по стоп-фактору.
     *
     * **Должны быть синхронизированы с сайтом**
     * `api/Scorings.php::AXI_REJECT_REASONS`
     */
    public const REJECT_REASONS = [
        'SSP_SELFDEC'       => Reasons::REASON_SELF_DEC, // Самозапрет
        'FNS_NOTFOUND'      => Reasons::REASON_INN_NOT_FOUND, // Акси не смог найти ИНН
        'IDX_SCOR'          => Reasons::REASON_AXI_IDX,
        'FSSP_SUM'          => Reasons::REASON_AXI_FSSP,
        'BAD_DEVICE'        => Reasons::REASON_AXI_BAD_DEVICE,
        'CNT_ACT_CH'        => Reasons::REASON_AXI_CNT_ACT_CH,
        'SCORE_CUTOFF'      => Reasons::REASON_AXI_SCORE,
        'YORISTO_BANKRUPCY' => Reasons::REASON_AXI_BANKRUPT,
        'ASOI_DEC'          => Reasons::REASON_AXI_ASOI,
        'SCORISTA_DECLINE'  => Reasons::REASON_SCORISTA,
        'BL_DEC'            => Reasons::REASON_BLACK_LIST,
        'PRE_NOTREGS'       => Reasons::REASON_LOCATION,
        'PRE_EXP_PASS'      => Reasons::REASON_PASSPORT,
        'PRE_AGE'           => Reasons::REASON_AGE,
        'FMS_HIT'           => Reasons::REASON_PASSPORT,
    ];

    private array $ltv_data;

    /**
     * Ответ скористы проведённой акси. Может отсутствовать, это нормально
     * @var null|object
     */
    private $scoristaResponse;

    private AxiTokenStorage $axiTokenStorage;

    public const MIN_NBKI_SCORE = 400;

    public function __construct()
    {
        parent::__construct();
        $this->axiTokenStorage = new AxiTokenStorage();
    }

    /**
     * Run scoring Axilink
     * @param string $scoringId
     * @return void
     * @throws Exception
     */
    public function run_scoring(string $scoringId): void
    {
        $inactiveOrganization = false;
        if ($scoring = $this->scorings->get_scoring($scoringId)) {
            if ($order = $this->orders->get_order((int)$scoring->order_id)) {

                $site_id = $this->organizations->get_site_organization($order->organization_id);
                if (!empty($site_id)) {
                    $this->settings->setSiteId($site_id);
                }

                $update = $this->validatePassport($order);

                if (empty($update)) {
                    if (!$this->organizations->isActiveOrganization((int)$order->organization_id)) {
                        $update = [
                            'status'        => $this->scorings::STATUS_ERROR,
                            'string_result' => 'Организация отключена для выдачи',
                            'end_date' => date('Y-m-d H:i:s'),
                        ];
                        $inactiveOrganization = true;
                    }
                    elseif($scoring->status != $this->scorings::STATUS_WAIT) {
                        $update = $this->processScoring($order);
                    }
                }
            } else {
                $update = [
                    'status'        => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найдена заявка'
                ];
            }

            if (!empty($update)) {
                $this->scorings->update_scoring($scoringId, $update);
            }

            // TODO отключено, не нужно отправлять
            // Если есть недавно выполненная скориста за последние 3 дня (см. getRecentCompletedScorista) то, чтобы ее результат отправился в акси
//            $recentScorista = $this->scorings->getRecentCompletedScorista($scoring->user_id);
//
//            if (!empty($recentScorista) && !$inactiveOrganization) {
//                if ($order = $this->orders->get_order((int)$recentScorista->order_id)) {
//                    $update = $this->processScoring($order, true, $recentScorista);
//                    $this->scorings->update_scoring($recentScorista->id, $update);
//                }
//            }
        }
    }

    /**
     * Обновление истории займов пользователя на актуальную версию из 1С
     * @param stdClass $order Заявка, в ней обновится loan_history
     * @return void
     */
    private function loadLoanHistory(stdClass $order)
    {
        $user = $this->users->get_user($order->user_id);
        $credits_history = $this->soap->get_user_credits($user->UID ?? $user->uid, $user->site_id);
        $loan_history = $this->users->save_loan_history($order->user_id, $credits_history);
        $order->loan_history = $loan_history;
    }

    /**
     * Validate passport rules
     */
    private function validatePassport($order): ?array
    {
        $timePassport = strtotime($order->passport_date);
        $birthYear    = date('Y', strtotime($order->birth));
        $passportYear = date('Y', $timePassport);

        if ($timePassport < self::PASSPORT_VALID_TIME) {
            return [
                'status'        => $this->scorings::STATUS_ERROR,
                'string_result' => 'Старая дата выдачи паспорта!',
                'end_date'      => date('Y-m-d H:i:s'),
            ];
        }
        if ($passportYear == $birthYear) {
            return [
                'status'        => $this->scorings::STATUS_ERROR,
                'string_result' => 'Не должны совпадать дата рождения с датой выдачи паспорта!',
                'end_date'      => date('Y-m-d H:i:s'),
            ];
        }
        if (($passportYear - $birthYear) < 14) {
            return [
                'status'        => $this->scorings::STATUS_ERROR,
                'string_result' => 'Паспорт РФ выдается в возрасте 14 лет!',
                'end_date'      => date('Y-m-d H:i:s'),
            ];
        }
        if ($timePassport > time()) {
            return [
                'status'        => $this->scorings::STATUS_ERROR,
                'string_result' => 'Дата выдачи паспорта не может быть в будущем!',
                'end_date'      => date('Y-m-d H:i:s'),
            ];
        }
        return null;
    }

    /**
     * Core processing logic
     * @throws Exception
     */
    private function processScoring($order, bool $isResend = false, $scoring = null): array
    {
        if ($order->have_close_credits == 1) {
            // Получаем актуальный $order->loan_history для ПК клиентов
            $this->loadLoanHistory($order);
        }

        $this->set_organization_params($order);

        $id = $this->createNewAppId($order->order_id);

        try {
            if ($this->orders->isRefinanceOrder($order)) {
                $this->createResponse = $this->createRefinance($order, $id);
            } else {
                // TODO вообще должно быть так: $this->createResponse = $this->create($order, $id, $isResend, $scoring);
                // Логика такая: если есть недавно выполненная скориста за последние 3 дня (см. getRecentCompletedScorista) то, чтобы ее результат отправился в акси
                $this->createResponse = $this->create($order, $id);
            }

            if ($this->createResponse && (is_numeric($this->createResponse)
                    || $this->createResponse === 'APPLICATION_EXISTS')
                || strpos($this->createResponse, 'SSP_') !== false) {
                $this->saveInDb($id, $order->order_id, $this->xml);

                if ($isResend) {
                    $update = [
                        'is_resend' => 1
                    ];
                } else {
                    $update = [
                        'status'        => $this->scorings::STATUS_WAIT,
                        'string_result' => $id
                    ];
                }
            } else {
                $update = [
                    'status'        => $this->scorings::STATUS_ERROR,
                    'string_result' => ((string)$this->createResponse) . $this->error
                ];
            }
        } catch (Throwable $t) {
            $this->logging(__METHOD__, '', $order->order_id, $t, 'dbrainAxi.log');

            $update = [
                'status'        => $this->scorings::STATUS_ERROR,
                'string_result' => $t->getMessage()
            ];
        }

        return $update;
    }


    /**
     * Get response for scoring
     * @param object $scoring
     * @return void
     */
    public function getInfo(object $scoring): void
    {
        if ($order = $this->orders->get_order($scoring->order_id)) {
            $this->set_organization_params($order);
        }

        $site_id = $this->organizations->get_site_organization($order->organization_id);
        if (!empty($site_id)) {
            $this->settings->setSiteId($site_id);
        }

        // Логирование для отладки (только для organization_id = 17)
        if ($order->organization_id == 17) {
            $this->logDisablePdnCheck('getInfo - setSiteId', [
                'order_id' => $scoring->order_id,
                'organization_id' => $order->organization_id,
                'site_id' => $site_id,
                'settings->site_id' => $this->settings->site_id ?? 'not set',
            ]);
        }

        $this->infoResponse = $this->getDecision($scoring->string_result);
        if($this->infoResponse !== false) {
            if(strpos($this->infoResponse, 'ERROR') !== false) {
                $this->handleError($scoring);
            } elseif($this->infoResponse != 'UNDEFINED') {
                $this->handleDecision($scoring);
            } else {
                $this->checkOvertime($scoring);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function createRefinance(object $order, string $applicationId)
    {
        $applicationId = 'SSP_' . $applicationId;

        $dss_name = $this->getDssName();
        $date = ($dss_name === self::DSS_FICO_4_10)
            ? date('Y-m-d\TH:i:s')     // FICO_4_10_V2...: без таймзоны
            : date('Y-m-d\TH:i:s.vP');     // FICO_4_10: с таймзоной

        $endDate = date('Y-m-d\TH:i:s.vP', strtotime('+5days'));
        $phone_mobile = substr($order->phone_mobile, -10);
        $order->birth = date('Y-m-d', strtotime($order->birth));
        $passportFullNumber = $this->preparePassportFullNumber($order->passport_serial);
        $passNumber = $this->getPassportNumberFromPassportFullNumber($passportFullNumber);
        $passSeria = $this->getPassportSerialFromPassportFullNumber($passportFullNumber);
        $subdivisionCode = $this->prepareSubdivisionCode($order->subdivision_code);
        $passportIssued = $this->preparePassportIssued($order->passport_issued);
        $gender = $this->getGenderCode($order->gender);

        $xml = '<Application DeliveryOptionCode="boostra2" ProcessingRequestType="DM">
            <CreditRequest ProductCategory="' . self::PRODUCT_CATEGORY_REFINANCE . '" ProductCode="' . self::PRODUCT_CODE_REFINANCE . '"></CreditRequest>
            <AXI>
                <application_e
                    dss_name="' . $dss_name . '"
                    ApplicationDate="' . $date . '"
                    ApplicationId="' . $applicationId . '"
                    call_name="START"
                    pass_seria="' . $passSeria . '"
                    pass_number="' . $passNumber . '"
                    pass_date_issue="' . date('Y-m-d', strtotime($order->passport_date)) . '"
                    pass_issued="' . $passportIssued . '"
                    pass_code="' . $subdivisionCode . '"
                    pass_region_code=""
                    client_birthplace="' . Helpers::getSafeStringForXml($order->birth_place) . '"
                    client_birthdate="' . $order->birth . '"
                    client_middlename="' . $order->patronymic . '"
                    client_name="' . $order->firstname . '"
                    client_surname="' . $order->lastname . '"
                    person_INN="' . $order->inn . '"
                    gender="' . $gender . '"
                    consentDate="' . $date . '" 
                    consentEndDate="' . $endDate . '" 
                    consentFlag="Y" 
                    income_amount="' . intval($order->income_base) . '"
                    initial_limit="' . $order->amount . '"
                    initial_maturity="' . $order->period . '"
                    mob_phone_num="' . $phone_mobile . '"
                    >
                </application_e>
            </AXI></Application>';

        $response = $this->send($xml, self::CREATE, 'application/xml');

        $this->logging(__METHOD__, 'getRefinance', ['request' => $xml], ['response' => $response], self::LOG_FILE_REFINANCE);

        return $response;
    }

    /**
     * Обработка подвисшего скоринга
     * @param $scoring
     * @return void
     */
    private function checkOvertime($scoring)
    {
        if((new DateTime($scoring->start_date))->add(new DateInterval('PT' . self::MAX_WAIT_TIME . 'S')) < (new DateTime('now'))) {
            $this->scorings->update_scoring($scoring->id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'OVERTIME_ERROR',
            ]);

            // Добавляем скоринг hyper_c
            $this->scorings->tryAddHyperC((int)$scoring->order_id);
            $this->scorings->tryAddPdn((int)$scoring->order_id);
        }
    }

    /**
     * Обработка ошибки
     * @param $scoring
     * @return void
     */
    private function handleError($scoring)
    {
        $this->scorings->update_scoring($scoring->id, [
            'status' => $this->scorings::STATUS_ERROR,
            'string_result' => $this->infoResponse,
        ]);

        // Добавляем скоринг hyper_c
        $this->scorings->tryAddHyperC((int)$scoring->order_id);
        $this->scorings->tryAddPdn((int)$scoring->order_id);
    }

    /**
     * Обработка полученного решения
     * @param $scoring
     * @return void
     */
    private function handleDecision($scoring)
    {
        $update = [
            'status' => $this->scorings::STATUS_COMPLETED,
            'success' => 0
        ];

        // Добавляем скоринг hyper_c
        $this->scorings->tryAddHyperC((int)$scoring->order_id);
        $this->scorings->tryAddPdn((int)$scoring->order_id);

        if ($this->infoResponse === 'APPROVE') {
            $update['success'] = 1;
            $update['string_result'] = 'заявка одобрена';
        } elseif ($this->infoResponse === 'DECLINE') {
            $update['string_result'] = 'заявка отклонена';
        } else {
            $update['status'] = $this->scorings::STATUS_ERROR;
        }

        $this->getFullData($scoring->string_result);
        $this->saveChData($scoring->string_result, $scoring->order_id);

        $order = $this->orders->get_order($scoring->order_id);
        $user = $this->users->get_user($scoring->user_id);

        if ($this->orders->isRefinanceOrder($order)) {
            $this->runRefinanceScoring($scoring);
            return;
        }

        $isCrossOrder = $this->orders->isCrossOrder($order);

        // Скориста пришла из Акси
        if (!empty($this->scoristaResponse)) {
            $this->order_data->set(
                $scoring->order_id,
                $this->order_data::SCORISTA_SOURCE,
                'aksi'
            );

            // Импортируем скористу из акси в срм
            $currentDateStr = date('Y-m-d H:i:s');
            $scoristaScoringId = $this->scorings->add_scoring([
                'type' => $this->scorings::TYPE_SCORISTA,
                'status' => $this->scorings::STATUS_IMPORT, // Скоринг подхватит крон скористы и закончит его обработку
                'user_id' => $scoring->user_id,
                'order_id' => $scoring->order_id,
                'scorista_id' => $this->scoristaResponse->agrid,
                'body' => json_encode($this->scoristaResponse),
                'created' => $currentDateStr,
                'start_date' => $scoring->start_date ?? $currentDateStr // Время запуска акси ИЛИ текущее
            ]);

            $this->logging(
                __METHOD__, 'Scorista scoring id: ' . $scoristaScoringId, [],
                $this->scoristaResponse, 'dbrainAxi_scoristaScoring.txt'
            );

            // Если скориста отказная
            if (!$isCrossOrder && $this->scoristaResponse->data->decision->decisionName != $this->scorings::SCORISTA_STATUS_RESULT_SUCCESS) {
                // Но есть PDL оффер
                $pdlOffer = null;
                foreach ($this->data['offers'] as $offer) {
                    if ($offer['credit_type'] == 'PDL ON' && $offer['agreed_sum'] > 0) {
                        $pdlOffer = $offer;
                        break;
                    }
                }

                // Тогда проставляем эту сумму в заявке и не делаем отказ по скористе
                if (!empty($pdlOffer)) {
                    $this->orders->update_order($scoring->order_id, [
                        'approve_amount' => $pdlOffer['agreed_sum'],
                        'amount' => $pdlOffer['agreed_sum']
                    ]);
                    $this->order_data->set(
                        $scoring->order_id,
                        $this->order_data::FAKE_SCORISTA_AMOUNT,
                        $pdlOffer['agreed_sum']
                    );
                }
            }
        }
        // Заявка созданная от ручейка, пробуем скопировать скористу у заявки-родителя
        elseif ($parent_id = $this->order_data->read($scoring->order_id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID)) {
            $isScoristaExists = $this->scorings->get_last_scorista_for_order($scoring->order_id, true);
            // Копируем заявку родителя только если у текущей заявки ещё нет завершённой скористы
            if (!$isScoristaExists) {
                $scorista = $this->scorings->get_last_scorista_for_order($parent_id, true);
                if (!empty($scorista)) {
                    $this->order_data->set(
                        $scoring->order_id,
                        $this->order_data::SCORISTA_SOURCE,
                        'org_switch_parent'
                    );

                    unset($scorista->id);
                    $scorista->order_id = $scoring->order_id;
                    $this->scorings->add_scoring($scorista);
                }
            }
        }
        // Скористы нет, проводим в СРМ (только если не отключены проверки ПДН и запросы КИ)
        else {
            $disablePdnCheck = (bool)$this->settings->disable_pdn_check;

            // Логирование для отладки (только для organization_id = 17)
            if ($order->organization_id == 17) {
                $this->logDisablePdnCheck('handleDecision - scorista check', [
                    'order_id' => $scoring->order_id,
                    'organization_id' => $order->organization_id,
                    'site_id' => $this->settings->site_id ?? 'not set',
                    'disable_pdn_check_raw' => $this->settings->disable_pdn_check,
                    'disable_pdn_check_bool' => $disablePdnCheck,
                    'will_set_scorista_source_crm' => !$disablePdnCheck
                ]);
            }

            if (!$disablePdnCheck) {
                $this->order_data->set(
                    $scoring->order_id,
                    $this->order_data::SCORISTA_SOURCE,
                    'crm'
                );
            }
        }

        if (!empty($update['success']) && !$isCrossOrder) {
            // добавляет проверку на купленный рейтинг и пустое значение в документе
            $document_credit_rating = $this->documents->getLastDocumentCreditRating((int)$scoring->user_id);
            if (!empty($document_credit_rating) && !isset($document_credit_rating->params->score)) {
                $document_credit_rating->params['score'] = $this->data['score'];
                $this->documents->update_document(
                    $document_credit_rating->id,
                    ['params' => $document_credit_rating->params]
                );
            }

            // Обновляем сумму в заявке, если решение принимает акси
            if (!$this->scorings->isScoristaAllowed($order) && !empty($this->data['final_limit'])) {
                $amount_update = [
                    'approve_amount' => $this->data['final_limit'],
                    'amount' => $this->data['final_limit']
                ];

                // Это, возможно, не нужно - перенёс код из скористы. Можем забыть перенести в будущем
                $update_installment = $this->installments->check_installment($scoring->id);
                if (empty($update_installment)) {
                    $amount_update = $this->installments->update_empty_installment($amount_update);
                }
                $amount_update = array_merge($amount_update, $update_installment);

                $this->orders->update_order($scoring->order_id, $amount_update);

                $this->order_data->set($scoring->order_id, 'amount_before_axi', $order->amount);
                $this->order_data->set($scoring->order_id, 'amount_after_axi', $this->data['final_limit']);
            }
        }

        if (!empty($this->data['comments'])) {
            $this->comments->add_comment([
                'manager_id' => 0,
                'user_id' => $user->id,
                'order_id' => $order->order_id,
                'block' => 'axilink',
                'text' => $this->data['comments'],
                'created' => date('Y-m-d H:i:s'),
            ]);
            unset($this->data['comments']);
        }

        $this->spr_versions->markOrderAxiVersion($order->order_id, $this->data['strategy_version']);

        $update['body'] = json_encode($this->data, JSON_UNESCAPED_UNICODE);
        $update['scorista_ball'] = $this->data['score'] ?: 0;
        $update['scorista_status'] = $this->data['name'];
        $update['scorista_id'] = $this->data['agrid'];
        $update['end_date'] = date('Y-m-d H:i:s');

        $reject_reason = null;
        $idx_result = null;
        $inn_found = true;
        if (!empty($this->data['message']) && !$isCrossOrder) {
            if (!$update['success']) {
                $reject_reason = $this->getRejectReason($scoring->user_id, $this->data['message']);
                $inn_found = $this->user_data->read($scoring->user_id, 'inn_not_found') != 1;
            }
            // Результат проверки IDX
            if (str_contains($this->data['message'], 'IDX_SCOR')) {
                // Телефон не принадлежит клиенту
                $idx_result = 'fail';
            }
        }

        // Результат проверки IDX
        if (!isset($idx_result)) {
            if (isset($this->data['idx_check']) && $this->data['idx_check'] != '') {
                // Телефон принадлежит клиенту
                $idx_result = 'success';
            }
            else {
                // Проверка не проводилась
                $idx_result = 'unchecked';
            }
        }
        $this->order_data->set($order->order_id, 'idx_check', $idx_result);

        if (!$reject_reason && !$inn_found) {
            if ($update['status'] == $this->scorings::STATUS_COMPLETED && $update['success'] == 0) {
                $update['status'] = $this->scorings::STATUS_ERROR;
                $update['string_result'] = 'ИНН не найден, нужно ввести руками';
            }
        }

        /** Пропуск отказа по заявке */
        $skipReject = false;
        if (empty($reject_reason) || $reject_reason == $this->reasons::REASON_SCORISTA) {
            if (
                $this->scorings->isHyperEnabledForOrder($order) ||
                $this->order_data->read($order->order_id, $this->order_data::FAKE_SCORISTA_AMOUNT)
            ) {
                $skipReject = true;
            }
        }

        if (!$isCrossOrder) {
            $disablePdnCheck = (bool)$this->settings->disable_pdn_check;

            // Логирование для отладки (только для organization_id = 17)
            if ($order->organization_id == 17) {
                $this->logDisablePdnCheck('handleDecision - before checkNbkiScore', [
                    'order_id' => $scoring->order_id,
                    'organization_id' => $order->organization_id,
                    'site_id' => $this->settings->site_id ?? 'not set',
                    'disable_pdn_check_raw' => $this->settings->disable_pdn_check,
                    'disable_pdn_check_bool' => $disablePdnCheck,
                ]);
            }

            // Проверка NBKI score только при APPROVE
            // если АКСи одобрил, но балл НБКИ низкий (Однорукий бандит)
            if ($disablePdnCheck && $update['success'] == 1) {
                $isNbkiPassed = $this->checkNbkiScore($scoring, $user);
                if (!$isNbkiPassed) {
                    $update['success'] = 0;
                    $update['scorista_status'] = 'Decline';
                    $update['string_result'] = 'Низкий NBKI score: ' . ($this->data['nbki_score'] ?? 'N/A');
                    $reject_reason = $this->reasons::REASON_LOW_NBKI_SCORE;
                }
            }
        }

        $this->scorings->update_scoring($scoring->id, $update);
        if (
            !in_array($order->status, [9, 10, 12])
            && $update['scorista_status'] === 'Decline'
            && $inn_found
            && !$skipReject
            && !$isCrossOrder
        ) {
            $tech_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);

            $update_order = [
                'status' => 3,
                'manager_id' => $tech_manager->id,
                'reason_id' => $reject_reason ?: $this->reasons::REASON_UNKNOWN_AXI,
                'reject_date' => date('Y-m-d H:i:s'),
            ];
            $this->orders->update_order($scoring->order_id, $update_order);

            $this->virtualCard->forUser($order->user_id)->delete();

            $this->leadgid->reject_actions($scoring->order_id);

            $changeLogs = Helpers::getChangeLogs($update_order, $order);
            $this->changelogs->add_changelog(array(
                'manager_id' => $tech_manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'status',
                'old_values' => serialize($changeLogs['old']),
                'new_values' => serialize($changeLogs['new']),
                'order_id' => $order->order_id,
                'user_id' => $order->user_id,
            ));

            $reason = $this->reasons->get_reason($update_order['reason_id']);
            $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $tech_manager->name_1c, 0, 1, $reason->admin_name);
        }

        $this->orders->update_order($scoring->order_id, ['scorista_ball' => $update['scorista_ball']]);

        $this->soap->send_aksi([
            'ball' => empty($update['scorista_ball']) ? 0 : (float)$update['scorista_ball'],
            'result' => $this->infoResponse,
            'limit' => $this->data['sum'],
            'order_id' => $order->id_1c,
            'version' => $this->version,
            'final_limit' => $this->ltv_data['final_limit'] ?? 0,
            'sc_new01' => $this->ltv_data['sc_new01'] ?? 0,
            'sc_new02' => $this->ltv_data['sc_new02'] ?? 0,
            'sc_new03' => $this->ltv_data['sc_new03'] ?? 0,
            'sc_rpt01' => $this->ltv_data['sc_rpt01'] ?? 0,
            'sc_rpt02' => $this->ltv_data['sc_rpt02'] ?? 0,
            'sc_rpt03' => $this->ltv_data['sc_rpt03'] ?? 0
        ]);
    }

    private function runRefinanceScoring($scoring): void
    {
        $update['body'] = json_encode($this->data, JSON_UNESCAPED_UNICODE);
        $update['scorista_ball'] = $this->data['score'] ?: 0;
        $update['scorista_status'] = $this->data['name'];
        $update['scorista_id'] = $this->data['agrid'];
        $update['end_date'] = date('Y-m-d H:i:s');

        $this->scorings->update_scoring($scoring->id, $update);
        $this->orders->update_order($scoring->order_id, ['scorista_ball' => $update['scorista_ball']]);
    }

    /**
     * Set call mode
     * @param string $command
     * @return void
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * Get createResponse data
     * @return string
     */
    public function getCreateResponse(): string
    {
        return $this->createResponse;
    }

    /**
     * Set short type of request
     * @return void
     */
    public function setShortType(): void
    {
        $this->type = 'short';
    }

    /**
     * Get infoResponse data
     * @return string
     */
    public function getInfoResponse(): string
    {
        return $this->infoResponse;
    }

    /**
     * Get Full data in response
     * @param string $appId
     * @return void
     */
    public function getFullData(string $appId): void
    {
        $this->ltv_data = [];
        $this->scoristaResponse = null;

        $score = $pdn = $sum = $sumNoPti = $period = 0;
        $message = $name = $equiFax = $agrid = $comments = '';
        $strategy_version = '';
        $final_limit = $juicy_json = '';
        $idx_check = null;
        $found_inn = null;
        $nbki_score = null;
        $offers = [];

        $order_id = $this->get_app_order_id($appId);
        if (!empty($order_id)) {
            $order = $this->orders->get_order((int)$order_id);
            $this->set_organization_params($order);
        }

        $this->data = json_decode(
            $this->send(['applicationId' => $appId], self::RESPONSE, 'multipart/form-data')
        );

        if ($this->data) {
            $name = trim(
                $this->data->Application->AXI->application_e->decision_e->{'@final_decision'} ?? ''
            );
            $sum = trim(
                $this->data->Application->AXI->application_e->calc->{'@limit_final'} ?? ''
            );
            $score = trim(
                $this->data->Application->AXI->application_e->score->{'@appl_ball_value'}  ?? ''
            );
            $message = trim(
                $this->data->Application->AXI->application_e->policyRules->{'@stop_factors'} ?? ''
            );
            $equiFax = trim(
                $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->equifaxCH->_value ?? ''
            );
            $agrid = trim(
                $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->{'@agrid'} ?? ''
            );
            $strategy_version = trim($this->data->Application->{'@StrategyVersion_FICO'} ?? '');

            if (!empty($this->data->Application->AXI->application_e->NBKI_SCORE)) {
                $nbki_score = (int)(
                    $this->data->Application->AXI->application_e->NBKI_SCORE->productScore->{'@score'} ?? 0
                );
            }

            // Получаем оффера
            if (!empty($this->data->Application->AXI->application_e->Offers_list)) {
                $offers_list = $this->data->Application->AXI->application_e->Offers_list;
                foreach ($offers_list as $offer) {
                    $offers[] = [
                        'offer_id' => (int)$offer->{'@offer_id'},
                        'agreed_sum' => (int)$offer->{'@agreedSum'},
                        'credit_type' => $offer->{'@CreditType'},
                    ];
                }
            }

            // Получаем проведённый на стороне акси джуси
            if (!empty($this->data->Application->AXI->application_e->JuicyScore)) {
                $juicy = $this->data->Application->AXI->application_e->JuicyScore;
                $juicy = $this->stripAtKeys($juicy); // Убираем @ из ключей
                $juicy = $this->formatJuiceBody($juicy); // Приводим к формату ответа оригинального скоринга джуси
                $juicy_json = json_encode($juicy); // Преобразуем в JSON
            }

            // Получаем проведённую на стороне акси скористу
            if (!empty($this->data->Application->AXI->application_e->SCORISTA->Response_SCR)) {
                // Вытягиваем данные из ответа акси
                $scorista = $this->data->Application->AXI->application_e->SCORISTA->Response_SCR;

                // Преобразуем полученную скористу в нужный формат
                $scorista = $this->formatScoristaResponse($scorista);
                if (isset($scorista->data->equifaxCH)) {
                    // Сохраняем КИ сразу чтобы не тащить её в БД
                    $this->scorista->saveCreditHistory($scorista->agrid, $scorista->data->equifaxCH);
                    unset($scorista->data->equifaxCH);
                }

                $this->scoristaResponse = $scorista;
            }

            if (!empty($this->data->Application->AXI->application_e->IDX_VerifyPhone)) {
                $idx_check = trim($this->data->Application->AXI->application_e->IDX_VerifyPhone->{'@validationScorePhone'} ?? '');
            }

            // Акси хаотично присылает ИНН в одном из этих двух полей, проверяем оба (или person_INN или @person_INN)
            if (!empty($this->data->Application->AXI->application_e->person_INN)) {
                $found_inn = trim($this->data->Application->AXI->application_e->person_INN);
            }
            elseif (!empty($this->data->Application->AXI->application_e->{'@person_INN'})) {
                $found_inn = trim($this->data->Application->AXI->application_e->{'@person_INN'});
            }
            // Сохраняем найденный ИНН в профиль пользователя, если его там ещё нет
            if ($found_inn && !empty($order) && empty($order->inn)) {
                $this->users->update_user($order->user_id, ['inn' => $found_inn]);
                $order->inn = $found_inn;
            }

            $this->version = $this->data->Application->{'@StrategyVersion'} ?? '';

            $final_limit = call_user_func(function ($final_limit_raw) {
                if (empty($final_limit_raw)) {
                    return null;
                }

                if (is_string($final_limit_raw)) {
                    $final_limit_raw = trim($final_limit_raw);
                }

                if (!is_numeric($final_limit_raw)) {
                    return null;
                }

                return number_format($final_limit_raw, 2, '.', '');
            }, $this->data->Application->AXI->application_e->{'@final_limit'} ?? '');

            if (!empty($order_id)) {
                $ltv_data = [
                    'order_id' => $order_id,
                    'final_decision' => $this->data->Application->AXI->application_e->decision_e->{'@final_decision'} ?? '',
                    'stop_factors' => $this->data->Application->AXI->application_e->policyRules->{'@stop_factors'} ?? '',
                    'countClosedLoans' => $this->data->Application->AXI->application_e->{'@countClosedLoans'} ?? '',
                    'initial_limit' => $this->data->Application->AXI->application_e->{'@initial_limit'} ?? '',
                    'age' => $this->data->Application->AXI->application_e->{'@age'} ?? '',
                    'axi_comment' => $this->data->Application->AXI->application_e->{'@axi_comment'} ?? '',
                    'ProductCategory' => $this->data->Application->CreditRequest->{'@ProductCategory'} ?? '',
                    'DeliveryOptionCode' => $this->data->Application->{'@DeliveryOptionCode'} ?? '',
                    'ApplicationDate' => $this->data->Application->AXI->application_e->{'@ApplicationDate'} ?? '',
                    'sc_new01' => $this->data->Application->AXI->application_e->score->{'@sc_new01'} ?? null,
                    'sc_new02' => $this->data->Application->AXI->application_e->score->{'@sc_new02'} ?? null,
                    'sc_new03' => $this->data->Application->AXI->application_e->score->{'@sc_new03'} ?? null,
                    'sc_rpt01' => $this->data->Application->AXI->application_e->score->{'@sc_rpt01'} ?? null,
                    'sc_rpt02' => $this->data->Application->AXI->application_e->score->{'@sc_rpt02'} ?? null,
                    'sc_rpt03' => $this->data->Application->AXI->application_e->score->{'@sc_rpt03'} ?? null,
                    'final_limit' => $final_limit,
                    'final_maturity' => $this->data->Application->AXI->application_e->{'@final_maturity'} ?? null,
                    'initial_maturity' => $this->data->Application->AXI->application_e->{'@initial_maturity'} ?? null,
                ];
                $this->ltv_data = $ltv_data;

                if ($this->axi_ltv->get($order_id)) {
                    $this->axi_ltv->update($order_id, $ltv_data);
                }
                else {
                    $this->axi_ltv->add($ltv_data);
                }
            }
        }

        $this->data = [
            'limit_period' => $period,
//            'pdn' => $pdn,
            'sum' => $sum,
//            'sum_no_pti' => $sumNoPti,
            'name' => $name,
            'message' => $message,
            'score' => $score,
            'appId' => $appId,
            'agrid' => $agrid,
            'equifax' => $equiFax,
            'strategy_version' => $strategy_version,
            'idx_check' => $idx_check,
//            'comments' => $comments,
            'final_limit' => $final_limit,
            'juicy' => $juicy_json,
            'offers' => $offers,
            'inn' => $found_inn,
            'nbki_score' => $nbki_score,
        ];
    }

    /**
     * Save saveCH data
     * @param $appId
     * @param $orderId
     * @param array $statusList
     * @param array $reportsTypeToSave
     * @return void
     */
    public function saveChData($appId, $orderId, array $statusList = [self::STATUS_RESPONSE, self::STATUS_REQUEST], array $reportsTypeToSave = self::DEFAULT_CH_FILES_TYPE_TO_SAVE): void
    {
        if (empty($appId)) {
            return;
        }

        $order = $this->orders->get_order($orderId);
        $response = $this->getHistory($appId, $order, $statusList);

        foreach ($response->content as $content) {
            if (empty($content)) {
                continue;
            }

            $reportType = $content->call->name;

            if (!empty($reportType) && in_array($reportType, $reportsTypeToSave)) {
                if ($content->status === self::STATUS_RESPONSE) {
                    if (!empty($content->xml)) {
                        $this->createXML($content->xml, $orderId, $order->user_id, $reportType);
                    }

                    continue;
                }

                if ($content->status === self::STATUS_REQUEST) {
                    $s3_name = '';

                    if ($reportType === Axi::SSP_REPORT) {
                        $s3_name = $this->ssp_nbki_request_log->saveInS3($content->xml, $orderId, $reportType);
                    }

                    $this->ssp_nbki_request_log->saveNewLog([
                        'request_type' => $reportType,
                        'data' => $content->xml,
                        'created_at' => date('Y-m-d H:i:s'),
                        'order_id' => $orderId,
                        'app_id' => $appId,
                        's3_name' => $s3_name,
                    ]);
                }
            }
        }

    }

    public function getHistory(string $appId, object $order, array $statusList, string $callName = 'NBKI')
    {
        $this->set_organization_params($order);

        $data = [
            'callName' => $callName,
            'applicationNumberList' => ["$appId"],
            'statusList' => $statusList,
            'cutXML' => false
        ];

        $resp = $this->historyRequest($data, $order->organization_id);

        return json_decode($resp['response']);
    }

    private function historyRequest(array $data, string $organizationId, array $params = ['page' => 1, 'size' => 10])
    {
        $token = $this->getAxiToken($organizationId);
        $resp = $this->sendHistoryRequest($data, $params, $token);

        // повторный запрос, на случай если токен просрочился
        if ($resp['code'] == 401 || $resp['code'] == 403) {
            $this->logging(__METHOD__, 'sendHistoryRequest', $data, $resp, 'axiLogin.log');

            $token = $this->getAxiToken($organizationId);
            $resp = $this->sendHistoryRequest($data, $params, $token);
        }

        return $resp;
    }

    private function sendHistoryRequest(array $data, array $params, string $token)
    {
        $jsonData = json_encode($data);
        $paramsStr = http_build_query($params, '', '&');

        $headers = [
            'Content-Type: application/json',
            'Authorization: ' . $token,
        ];

        return $this->send($jsonData, 'history/page?' . $paramsStr, '', 'api/', $headers);
    }

    private function getAxiToken(string $organizationId): string
    {
        $token = $this->axiTokenStorage->get($organizationId);

        if (empty($token)) {
            $token = $this->loginAxi();

            if (!empty($token)) {
                $saved = $this->axiTokenStorage->set($organizationId, $token);
                if (empty($saved)) {
                    $this->logging(__METHOD__, 'axiTokenStorage::set', $token, 'не удалось сохранить токен в хранилище', 'axiLogin.log');
                }
            }
        }

        return $token;
    }

    private function loginAxi(): ?string
    {
        if (empty($this->axilogin)) {
            $this->axilogin = $this->config->axi_login;
            $this->axipassword = $this->config->axi_password;
        }

        $data = json_encode([
            'name' => $this->axilogin,
            'password' => $this->axipassword
        ]);

        $resp = $this->send($data, 'login', '', 'api/', ['Content-Type: application/json']);
        $response = json_decode($resp['response']);

        if (empty($response->token)) {
            $this->logging(__METHOD__, 'login', ['login' => $this->axilogin, 'url' => $this->service_url], $resp, 'axiLogin.log');

            return null;
        }

        return $response->token;
    }

    public function createXML($xmlString, $orderId, $userId, $reportType): bool
    {
        $filename = $orderId . '.xml';

        if ($reportType === $this->axi::SSP_REPORT) {
            $file_local_path = ROOT . '/files/CCP/' . $filename;
            $s3_name = $this->config->s3['amp_report_url'] . date('Ymd') . '/' . $filename;
        } else if ($reportType === $this->axi::CH_REPORT) {
            $file_local_path = ROOT . '/files/credit_history/' . $filename;
            $s3_name = $this->config->s3['report_url'] . date('Ymd') . '/' . $filename;
        } else {
            $this->logging(__METHOD__, '', ['order_id' => $orderId], 'Некорректный тип отчета', 'dbrainAxi.txt');

            return false;
        }

        if (stripos($xmlString, 'encoding="windows-1251"') !== false) {
            $xmlString = iconv('UTF-8', 'windows-1251', $xmlString);
        }

        try {
            $this->s3_api_client->putFileBody($xmlString, $s3_name);

            $file_id = $this->credit_history->insertRow([
                'user_id' => $userId,
                'order_id' => $orderId,
                'type' => $reportType,
                'file_name' => $filename,
                's3_name' => $s3_name,
                'date_create' => date('Y-m-d H:i:s'),
            ]);

            if (!$file_id) {
                $this->logging(
                    __METHOD__,
                    '',
                    ['order_id' => $orderId],
                    'Не удалось сохранить файл в s_credit_histories',
                    'dbrainAxi.txt'
                );

                return false;
            }
        } catch (Throwable $e) {
            $error = [
                'Ошибка: ' . $e->getMessage(),
                'Файл: ' . $e->getFile(),
                'Строка: ' . $e->getLine(),
                'Подробности: ' . $e->getTraceAsString()
            ];

            $this->logging(__METHOD__, '', ['order_id' => $orderId], $error, 'dbrainAxi.txt');

            $file_uploaded = file_put_contents($file_local_path, $xmlString);

            if (!$file_uploaded) {
                $this->logging(
                    __METHOD__,
                    '',
                    ['order_id' => $orderId, 'content' => $xmlString, 'error' => error_get_last()],
                    'Не удалось сохранить файл отчета',
                    'dbrainAxi.txt'
                );
            }

            return false;
        }

        return true;
    }

    /**
     * @param object $order
     * @param string $id
     * @param bool $isResend
     * @param object|null $scoring
     * @return bool|string
     * @throws Exception
     */
    protected function create(object $order, string $id, bool $isResend = false, object $scoring = null)
    {
        $dss_name = $this->getDssName();
        $date = ($dss_name === self::DSS_FICO_4_10)
            ? date('Y-m-d\TH:i:s')     // FICO_4_10_V2...: без таймзоны
            : date('Y-m-d\TH:i:s.vP');     // FICO_4_10: с таймзоной

        $timeUtc3 = date('d.m.Y H:i:s');
        $sessionId = $order->juicescore_session_id;
        $endDate = date('Y-m-d\TH:i:s.vP', strtotime('+5days'));
        $order->birth = date('Y-m-d', strtotime($order->birth));
        $phone_mobile = substr($order->phone_mobile, -10);

        $regIndex = trim($order->Regindex);
        if (strlen($regIndex) !== 6 || $regIndex === 'undefi') {
            $regIndex = '000000';
        }

        $faktIndex = trim($order->Faktindex);
        if (strlen($faktIndex) !== 6 || $faktIndex === 'undefi') {
            $faktIndex = '000000';
        }

        $scorings = $this->scorings($order->user_id, $order->order_id);
        $internalCR = $this->internalCR($order->user_id);
        $passportFullNumber = $this->preparePassportFullNumber($order->passport_serial);
        $passNumber = $this->getPassportNumberFromPassportFullNumber($passportFullNumber);
        $passSeria = $this->getPassportSerialFromPassportFullNumber($passportFullNumber);
        $subdivisionCode = $this->prepareSubdivisionCode($order->subdivision_code);
        $passportIssued = $this->preparePassportIssued($order->passport_issued);
        $flat = $order->Faktroom ?: $order->Regroom;
        $house = $order->Fakthousing ?: $order->Reghousing;
        $repaymentDate = date('Y-m-d', strtotime("+{$order->period}day"));
        $repaymentAmount = $order->amount + ($order->period * $order->amount / 100);
        $gender = $this->getGenderCode($order->gender);
        $cardHolder = Helpers::translit($order->firstname . ' ' . $order->lastname);
        $useragent = $this->order_data->read($order->order_id, $this->order_data::USERAGENT) ?? '';

        $visitorId = $this->order_data->read(
            $order->order_id,
            $this->order_data::VISITOR_ID
        );

        $visitor = null;

        if (!empty($visitorId)) {
            $visitor = $this->visitors->get_visitor((int) $visitorId);
        }

        $pixel_user_fp = $visitor ? $visitor->pixel_user_fp : '';
        $pixel_sess_id = $visitor ? $visitor->pixel_sess_id : '';
        $pixel_user_dt = $visitor ? $visitor->pixel_user_dt : '';

        /** @var array $additionalApplicationParams Опциональные параметры для акси в формате [параметр] => значение */
        $additionalApplicationParams = [];

        $axiWithoutCreditReports = !empty($this->order_data->read($order->order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS));
        $additionalApplicationParams['allow_simplified_flow'] = $axiWithoutCreditReports ? 'true' : 'false';

        $additionalApplicationParamsStr = '';
        foreach ($additionalApplicationParams as $key => $value) {
            $additionalApplicationParamsStr .= $key . '="' . $value . '"' . "\n";
        }

        // Generate different XML structure for completed/resend scorings
        if ($isResend) {
            $timestamp = date('Y-m-d\TH:i:s.vP');
            $transactionId = $order->order_id . ':' . substr(md5($order->order_uid), 0, 8) . ':' . substr(md5($id), 0, 8) . ':' . substr(md5($timestamp), 0, 4);

            // Get real scoring data from database
            $scoringData = null;
            if ($scoring && !empty($scoring->body)) {
                $scoringData = [
                    'body' => json_decode($scoring->body, true),
                    'success' => $scoring->success
                ];
            }

            $this->xml = '<Application DeliveryOptionCode="boostra2" ProcessingRequestType="DM" StrategySelectionRandomNumber="8" StrategyVersion="1.0.0" StrategyVersion_FICO="2_1_20" Timestamp="' . $timestamp . '" TransactionId="' . $transactionId . '">
                <CreditRequest ProductCategory="' . $this->getProductCategory($order) . '" ProductCode="' . $this->getProductCode($order) . '"></CreditRequest>
                <AXI>
                    <application_e 
                        ApplicationDate="' . $date . '" 
                        ApplicationId="' . $id . '" 
                        CallFailed="false" 
                        IP="' . $order->ip . '" 
                        activ_dopi="' . intval($order->is_user_credit_doctor) . '" 
                        age="' . $this->calculateAge($order->birth) . '" 
                        agreementSignatureMethod="2" 
                        app_channel="1" 
                        applicationSourceType="1" 
                        cacheAnswer="true" 
                        cacheOnly="false" 
                        call_name="' . $this->command . '"  
                        card_holder_name="' . $cardHolder . '" 
                        card_number="' . $this->cardNumber . '" 
                        cellularMethod="2" 
                        cellularState="2" 
                        clean_stream_flg="N" 
                        client_birthdate="' . $order->birth . '" 
                        client_birthplace="' . Helpers::getSafeStringForXml($order->birth_place) . '" 
                        client_middlename="' . $order->patronymic . '" 
                        client_name="' . $order->firstname . '" 
                        client_surname="' . $order->lastname . '" 
                        region_code="' . $this->rosstatRegion->resolveCodeFromString($order->Regregion) . '"
                        consentDate="' . $date . '" 
                        consentEndDate="' . $endDate . '" 
                        consentFlag="Y" 
                        consent_flg="Y" 
                        consentPurpose="1" 
                        ' . $this->getLoanInfo($order) . ' 
                        dss_name="' . $dss_name . '" 
                        education="' . intval($order->education) . '" 
                        email="' . $order->email . '" 
                        final_limit="' . $order->amount . '" 
                        gender="' . $gender . '" 
                        home_phone="' . $order->identified_phone . '" 
                        id_cpa_net="' . $order->utm_source . '" 
                        id_webmaster="' . $order->webmaster_id . '" 
                        income_amount="' . intval($order->income_base) . '" 
                        initial_limit="' . $order->amount . '" 
                        initial_maturity="' . $order->period . '" 
                        isDummy="false" 
                        liv_address_city="' . $this->clearGarbage($order->Faktcity_shorttype, $order->Faktcity) . '" 
                        liv_address_flat="' . $this->clearGarbage('', $flat) . '" 
                        liv_address_house="' . $house . '" 
                        liv_address_index="' . $faktIndex . '" 
                        liv_address_region="' . $this->clearGarbage($order->Faktregion_shorttype, $order->Faktregion) . '" 
                        liv_address_street="' . $this->clearGarbage($order->Faktstreet_shorttype, $order->Faktstreet) . '" 
                        loanRepaymentMethod="1" 
                        loan_type_IL="' . $this->dbrainAxi->getOrderLoanType($order) . '" 
                        match_cookie="" 
                        mob_phone_num="' . $phone_mobile . '" 
                        pass_code="' . $subdivisionCode . '" 
                        pass_date_issue="' . date('Y-m-d', strtotime($order->passport_date)) . '" 
                        pass_issued="' . $passportIssued . '" 
                        pass_number="' . $passNumber . '" 
                        pass_region_code="' . substr($subdivisionCode, 0, 2) . '" 
                        pass_seria="' . $passSeria . '" 
                        person_INN="' . $order->inn . '" 
                        productType="' . $this->getProductType($order) . '" 
                        reg_address_city="' . $this->clearGarbage($order->Regcity_shorttype, $order->Regcity) . '" 
                        reg_address_flat="' . $this->clearGarbage('', $flat) . '" 
                        reg_address_house="' . $house . '" 
                        reg_address_index="' . $regIndex . '" 
                        reg_address_region="' . $this->clearGarbage($order->Regregion_shorttype, $order->Regregion) . '" 
                        reg_address_street="' . $this->clearGarbage($order->Regstreet_shorttype, $order->Regstreet) . '" 
                        remove_source_data="EQUIFAX_SOURCE" 
                        repaymentAmount="' . $repaymentAmount . '" 
                        repaymentDate="' . $repaymentDate . '" 
                        scnd_24="' . $this->getMaxExpiredPeriod($order) . '" 
                        siteApplicationId="' . $order->order_id . '" 
                        siteClientId="' . $order->user_id . '" 
                        snils="' . $order->Snils . '" 
                        source_link_cpa="" 
                        staffMember="CRM" 
                        user_agent="' . $useragent . '" 
                        pixel_user_fp="' . $pixel_user_fp . '" 
                        pixel_sess_id="' . $pixel_sess_id . '" 
                        pixel_user_dt="' . $pixel_user_dt . '" 
                        uuid="' . $order->order_uid . '"
                        ' . $additionalApplicationParamsStr . '
                        webmasterBlocked="' . ((int) $this->webmasterBlocked($order)) . '">
                    <innerCH TotalPayment="' . $order->amount . '"></innerCH>
                    <FMS list_invalid_pass_flg="N" update_date="' . date('Y-m-d', strtotime('-1 year')) . '"></FMS>
                    <RFM list_rfm_fio_flg="N" list_rfm_flg="N" list_rfm_info="" list_rfm_inn_flg="N" list_rfm_pasp_flg="N"></RFM>
                    <application_innerHistory initial_maturity="' . $order->period . '" initial_rate="' . $order->percent . '"></application_innerHistory>
                    <decision_e final_decision="Approve"></decision_e>
                    <policyRules></policyRules>
                    <SCORISTA>
                        ' . $this->generateResponseSCR($scoringData, $order) . '
                    </SCORISTA>
                </application_e>
            </AXI>
        </Application>';
        } else {
            $this->xml = '<Application DeliveryOptionCode="boostra2" ProcessingRequestType="DM">
                <CreditRequest ProductCategory="' . $this->getProductCategory($order) . '" ProductCode="' . $this->getProductCode($order) . '"></CreditRequest>
                <AXI>
                    <application_e 
                        dss_name="' . $dss_name . '" 
                        ApplicationDate="' . $date . '" 
                        ApplicationId="' . $id . '" 
                        uuid="' . $order->order_uid . '"                     
                        call_name="' . $this->command . '" 
                        pass_seria="' . $passSeria . '" 
                        pass_number="' . $passNumber . '" 
                        pass_date_issue="' . date('Y-m-d', strtotime($order->passport_date)) . '" 
                        pass_issued="' . $passportIssued . '" 
                        pass_code="' . $subdivisionCode . '" 
                        pass_region_code=""
                        snils="' . $order->Snils . '" 
                        email="' . $order->email . '"  
                        IP="' . $order->ip . '"
                        app_channel="1"
                        match_cookie=""
                        user_agent="' . $useragent . '"
                        pixel_user_fp="' . $pixel_user_fp . '" 
                        pixel_sess_id="' . $pixel_sess_id . '" 
                        pixel_user_dt="' . $pixel_user_dt . '" 
                        income_amount="' . intval($order->income_base) . '"
                        initial_limit="' . $order->amount . '"
                        initial_maturity="' . $order->period . '"
                        mob_phone_num="' . $phone_mobile . '"
                        home_phone="' . $order->identified_phone . '"
                        client_birthplace="' . Helpers::getSafeStringForXml($order->birth_place). '"
                        client_birthdate="' . $order->birth . '"
                        client_middlename="' . $order->patronymic . '"
                        client_name="' . $order->firstname . '"
                        client_surname="' . $order->lastname . '"
                        region_code="' . $this->rosstatRegion->resolveCodeFromString($order->Regregion)  . '"
                        reg_address_index="' . $regIndex . '" 
                        reg_address_region="' . $this->clearGarbage($order->Regregion_shorttype, $order->Regregion) . '" 
                        reg_address_city="' . $this->clearGarbage($order->Regcity_shorttype, $order->Regcity) . '" 
                        reg_address_street="' . $this->clearGarbage($order->Regstreet_shorttype, $order->Regstreet) . '"
                        reg_address_house = "' . $house . '"
                        reg_address_flat = "' . $this->clearGarbage('', $flat) . '"
                        liv_address_index="' . $faktIndex . '" 
                        liv_address_region="' . $this->clearGarbage($order->Faktregion_shorttype, $order->Faktregion) . '" 
                        liv_address_city="' . $this->clearGarbage($order->Faktcity_shorttype, $order->Faktcity) . '" 
                        liv_address_street="' . $this->clearGarbage($order->Faktstreet_shorttype, $order->Faktstreet) . '"
                        liv_address_house="' . $house . '"
                        liv_address_flat="' . $this->clearGarbage('', $flat) . '"
                        person_INN="' . $order->inn . '"
                        gender="' . $gender . '" 
                        education="' . intval($order->education) . '"
                        activ_dopi="' . intval($order->is_user_credit_doctor) . '"
                        productType="' . $this->getProductType($order) . '" 
                        repaymentDate="' . $repaymentDate . '"  
                        repaymentAmount="' . $repaymentAmount . '" 
                        agreementSignatureMethod="2" 
                        applicationSourceType="1" 
                        staffMember="CRM" 
                        cellularState="2" 
                        cellularMethod="2" 
                        loanRepaymentMethod="1" 
                        ' . $this->getLoanInfo($order) . ' 
                        card_number="' . $this->cardNumber . '" 
                        card_holder_name="' . $cardHolder . '"
                        consentDate="' . $date . '" 
                        consentEndDate="' . $endDate . '" 
                        consentFlag="Y" 
                        webmasterBlocked="' . ((int) $this->webmasterBlocked($order)) . '" 
                        source_link_cpa=""
                        siteApplicationId="' . $order->order_id . '"
                        siteClientId="' . $order->user_id . '"
                        id_webmaster="' . $order->webmaster_id . '"
                        id_cpa_net="' . $order->utm_source . '"
                        consent_flg="Y"
                        scnd_24="' . $this->getMaxExpiredPeriod($order) . '"
                        loan_type_IL="' . $this->dbrainAxi->getOrderLoanType($order) . '"
                        ' . $additionalApplicationParamsStr . '
                        consentPurpose="1">
                    <loanReceivingMethod 
                        value="11"
                        cash="0"
                    ></loanReceivingMethod>
                    <juicy_scoring
                        session_id="' . $sessionId . '"
                        channel="' . $order->utm_source . '"
                        client_id="' . $order->user_id . '"
                        time_utc3="' . $timeUtc3 . '"
                        tenor="' . $order->period . '"
                    ></juicy_scoring>
                    <innerCH
                        TotalPayment="' . $order->amount . '"
                    ></innerCH>
                    <application_innerHistory
                        initial_rate="' . $order->percent . '"
                        initial_maturity="' . $order->period . '"
                    ></application_innerHistory>
                    <scorings>' . $scorings . '</scorings>
                    <internal_CR>' . $internalCR . '</internal_CR>
                </application_e>
            </AXI></Application>';
        }
        return $this->send($this->xml, $this->create_action, 'application/xml');
    }

    /**
     * Clear not need garbage string
     * @param string $garbage
     * @param string $str
     * @return string
     */
    protected function clearGarbage(string $garbage, string $str): string
    {

        $replaced = ['*', '«', '»','&'];
        if (!empty($garbage)) {
            $replaced[] = ' ' . $garbage;
        }
        $cleanedString = trim(str_replace($replaced, '', $str)) ?: 'НЕТ';
        return str_replace('"', '&quot;', str_replace("'", "\\'", $cleanedString));
    }

    /**
     * Get property product_category
     * @param stdClass $order Обрабатываемая заявка
     * @return string
     */
    protected function getProductCategory(stdClass $order): string
    {
        return $this->axiConfig->ProductCategory;
    }

    /**
     * Get property product_code
     * @param stdClass $order Обрабатываемая заявка
     * @return string
     */
    protected function getProductCode(stdClass $order): string
    {
        return $this->axiConfig->ProductCode;
    }

    /**
     * Get dss_name from config
     * @return string
     */
    protected function getDssName(): string
    {
        return $this->axiConfig->dss_name;
    }

    /**
     * Get product type
     * @param object $order
     * @return string
     */
    protected function getProductType(object $order): string
    {
        $str = 'Boostra';
        if (!empty($order->utm_source)) {
            $str = $order->utm_source;
            if (!empty($order->webmaster_id)) {
                $str .= '_' . $order->webmaster_id;
            }
        }
        return $str;
    }

    /**
     * Get loan Info for SPR
     * @param object $order
     * @return string
     */
    protected function getLoanInfo(object $order): string
    {
        if (empty($order->loan_history)) {
            $str = 'numberLoansRepaid = "0"' . "\n";
            $str .= 'countClosedLoans = "0"' . "\n";
            $str .= 'countLoans = "0"' . "\n";
            return $str;
        }

        $loansAmount =  count($order->loan_history);
        $closedLoansAmount = count(array_filter($order->loan_history, function ($loan) {
            return !empty($loan->close_date);
        }));

        $lastLoan = end($order->loan_history);

        $str = 'numberLoansRepaid = "' . $loansAmount . '"' . "\n";
        $str .= 'previousLoanDate = "' . date('Y-m-d', strtotime($lastLoan->date)) . '"' . "\n";
        $str .= 'previousLoanPlanRepaidDate = "' . date('Y-m-d', strtotime($lastLoan->{empty($lastLoan->plan_close_date) ? 'close_date' : 'plan_close_date'})) . '"' . "\n";
        $str .= 'previousLoanFactRepaidDate = "' . date('Y-m-d', strtotime($lastLoan->close_date)) . '"' . "\n";
        $str .= 'previousLoanAmount = "' . (int)$lastLoan->amount . '"' . "\n";
        $str .= 'previousLoanRepaymentAmount = "' . round($lastLoan->amount + $lastLoan->paid_percents) . '"' . "\n";
        $str .= 'previousLoanProlongationNumber = "' . (empty($lastLoan->prolongation_count) ? 0 : $lastLoan->prolongation_count) . '"' . "\n";
        $str .= 'countClosedLoans = "' . $closedLoansAmount . '"' . "\n";
        $str .= 'countLoans = "' . $loansAmount . '"' . "\n";

        return $str;
    }

    /**
     * Get Card number from b2p or tinkoff
     * @param object $order
     * @return string
     */
    private function getCardNumber(object $order): string
    {
        $cardNumber = '';

        $cards = $this->best2pay->get_cards(array('user_id' => $order->user_id));
        $arg = 'pan';

        if ($cards && !empty($cards[0])) {
            $cardNumber = $cards[0]->{$arg};
        }
        return $cardNumber;
    }

    /**
     * @param object $order
     * @return bool
     */
    private function webmasterBlocked(object $order): bool
    {
        $this->webmaster->setOrder($order);
        if ($blocked = $this->webmaster->isBlocked()) {
            try {
                $this->webmaster->runDeclineProcess();
            } catch (Exception $e) {
                $this->logging(__METHOD__, '', $order, $blocked, 'webmaster.txt');
            }
        }
        return $blocked;
    }

    /**
     * @param string $appID
     * @return bool|string
     */
    public function getDecision(string $appID)
    {
        return $this->send(['applicationId' => $appID], self::DECISION, 'multipart/form-data');
    }

    /**
     * @param array|string $data
     * @param string $method
     * @param string $type
     * @param string $url
     * @param array $headers
     * @return bool|string|array
     */
    private function send($data, string $method, string $type, $url = 'rpc/',$headers = [])
    {
        if (is_null($this->ch)) {
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Content-Type: {$type}","charset:'UTF-8"));

        if (!empty($headers)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT);
        curl_setopt($this->ch, CURLOPT_URL, $this->service_url . $url .$method);

        $response = curl_exec($this->ch);
        $curl_errno = curl_errno($this->ch);
        $curl_error = curl_error($this->ch);

        if ($response === false) {
            $this->error = "$curl_error ($curl_errno)";
            $this->logging(__METHOD__,
                'METHOD: ' . $this->service_url . $url .$method . ' TYPE: ' . $type,
                $data,
                "$curl_error ($curl_errno)",
                'dbrainAxi2errors.txt');
        } elseif (!empty($headers)) {
            return [
                'response' => $response,
                'code' => curl_getinfo($this->ch, CURLINFO_HTTP_CODE)
            ];
        }

        $this->logging('after_send', $this->service_url . $url .$method . ' TYPE: ' . $type, $data, $response, 'axinbki17.txt');

        return $response;
    }

    /**
     * Get correct App id for axilink table
     * @param int $order_id
     * @return string
     */
    public function getLastAppId(int $order_id)
    {
        $query = $this->db->placehold("SELECT app_id FROM __axilink WHERE order_id = ? ORDER BY id DESC, created_date DESC LIMIT 1", $order_id);
        $this->db->query($query);
        return $this->db->result('app_id');
    }

    private function createNewAppId(int $orderId): string
    {
        $lastAppId = $this->getLastAppId($orderId);
        $prefix = '0';

        if (!empty($lastAppId)) {
            $searchPreviousPrefix = preg_match("#^(\d+)$orderId$#", $lastAppId, $matches);

            if ($searchPreviousPrefix !== false && isset($matches[1])) {
                $prefix = 1 + (int)$matches[1];
            }
        }

        return $prefix . $orderId;
    }

    /**
     * @param string $app_id
     * @param int $order_id
     * @param string $xml
     * @return void
     */
    private function saveInDb(string $app_id, int $order_id, string $xml): void
    {
        $query = $this->db->placehold("INSERT INTO __axilink SET ?%", compact('app_id', 'order_id', 'xml'));
        $this->db->query($query);
    }

    /**
     * https://tracker.yandex.ru/BOOSTRARU-2243
     * Получаем из 1с максимальное кол-во дней просрочки по предыдущим займам. То есть нужно считаем кол-во дней
     * между статусами просрочен - закрыт, либо просрочен - продлён по всем предыдущим займам клиента и выбираем
     * максимальное значение.
     */
    public function getMaxExpiredPeriod(object $order): int
    {
        if ((int) $order->have_close_credits === 0) {
            return 0;
        }

        $user = $this->users->get_user($order->user_id);
        $data = $this->soap->generateObject(['UID' => $user->UID]);

        return (int) $this->soap->requestSoap($data, 'WebSignal', 'MaxOverdueByClient')['response'] ?? 0;
    }

    public function getOrderLoanType(object $order): int
    {
        if ((int) $order->have_close_credits === 0) {
            return 0;
        }

        return $order->loan_type === $this->orders::LOAN_TYPE_IL ? 1 : 0;
    }

    /**
     * Calculate age from birthdate
     * @param string $birthDate
     * @return int
     * @throws Exception
     */
    private function calculateAge(string $birthDate): int
    {
        $birth = new DateTime($birthDate);
        $today = new DateTime();
        return $today->diff($birth)->y;
    }


    /**
     * Generate Response_SCR XML section using real scoring data
     * @param array|null $scoringData
     * @param object $order
     * @return string
     */
    private function generateResponseSCR(?array $scoringData, object $order): string
    {
        $data = $scoringData['body'];
        $success = $scoringData['success'];
        $agrid = 'agrid' . substr(md5($order->order_uid), 0, 8);

        // Extract data from the scoring response
        $trustRating = $data['trustRating'] ?? [];
        $creditHistory = $data['creditHistory'] ?? [];
        $stopFactors = $data['stopFactors'] ?? [];
        $additional = $data['additional'] ?? [];
        $decision = $data['decision'] ?? [];
        $summary = $data['summary'] ?? [];

        // Generate stop factors XML
        $stopFactorsXml = '';
        foreach ($stopFactors as $factorName => $factorData) {
            $result = $factorData['result'] ?? '0';
            $description = htmlspecialchars($factorData['description'] ?? '');
            $stopFactorsXml .= '<' . $factorName . '_SCR description="' . $description . '" result="' . $result . '">';

            // Handle nested elements like loanReceivingMethodDanger
            if (isset($factorData['loanReceivingMethodDanger'])) {
                $dangerResult = $factorData['loanReceivingMethodDanger']['result'] ?? '';
                $dangerDesc = htmlspecialchars($factorData['loanReceivingMethodDanger']['description'] ?? '');
                $stopFactorsXml .= '<loanReceivingMethodDanger_SCR description="' . $dangerDesc . '" result="' . $dangerResult . '"></loanReceivingMethodDanger_SCR>';
            }

            $stopFactorsXml .= '</' . $factorName . '_SCR>';
        }

        // Generate credit history XML
        $creditHistoryXml = '';
        if (!empty($creditHistory)) {
            $cutoff = $creditHistory['cutoff'] ?? '530';
            $score = $creditHistory['score'] ?? '840';
            $result = $creditHistory['result'] ?? '0';

            $goodCreditHistory = $creditHistory['goodCreditHistory'] ?? [];
            $unknownCreditHistory = $creditHistory['unknownCreditHistory'] ?? [];
            $negativeCreditHistory = $creditHistory['negativeCreditHistory'] ?? [];

            $creditHistoryXml = '<creditHistory_SCR cutoff="' . $cutoff . '" result="' . $result . '" score="' . $score . '">';
            $creditHistoryXml .= '<goodCreditHistory_SCR description="' . htmlspecialchars($goodCreditHistory['description'] ?? 'Хорошая кредитная история') . '" result="' . ($goodCreditHistory['result'] ?? '83') . '"></goodCreditHistory_SCR>';
            $creditHistoryXml .= '<unknownCreditHistory_SCR description="' . htmlspecialchars($unknownCreditHistory['description'] ?? 'Неопределенная кредитная история') . '" result="' . ($unknownCreditHistory['result'] ?? '17') . '"></unknownCreditHistory_SCR>';
            $creditHistoryXml .= '<negativeCreditHistory_SCR description="' . htmlspecialchars($negativeCreditHistory['description'] ?? 'Негативная кредитная история') . '" result="' . ($negativeCreditHistory['result'] ?? '0') . '"></negativeCreditHistory_SCR>';
            $creditHistoryXml .= '</creditHistory_SCR>';
        }

        // Generate trust rating XML
        $trustRatingXml = '';
        if (!empty($trustRating)) {
            $trustResult = $trustRating['result'] ?? '1';
            $trustDesc = htmlspecialchars($trustRating['description'] ?? 'Оценка благонадежности');
            $trustValue = $trustRating['trustRating']['result'] ?? '0.8251';

            $trustRatingXml = '<trustRating_SCR description="' . $trustDesc . '" result="' . $trustResult . '">';
            $trustRatingXml .= '<trustRating_TR_SCR description="Числовое значение оценки благонадежности" result="' . $trustValue . '"></trustRating_TR_SCR>';
            $trustRatingXml .= '</trustRating_SCR>';
        }

        // Generate decision XML
        $decisionXml = '';
        if (!empty($decision)) {
            $decisionBinnar = $decision['decisionBinnar'] ?? ($success ? '1' : '0');
            $decisionName = htmlspecialchars($decision['decisionName'] ?? ($success ? 'Одобрено' : 'Отказ'));
            $decisionXml = '<decision_SCR decisionBinnar="' . $decisionBinnar . '" decisionName="' . $decisionName . '"></decision_SCR>';
        }

        // Generate additional data XML
        $additionalXml = '';
        if (!empty($additional)) {
            $inn = $order->inn ?? '';
            $snils = $order->Snils ?? '';
            $decisionMessage = $additional['decisionMessage'] ?? '';
            $decisionSum = $additional['decisionSum'] ?? $order->amount;
            $decisionType = $additional['decisionType'] ?? 'PDL ON';
            $creditRating4Sale = $additional['creditRating4Sale'] ?? '789';
            $daemonId = $additional['daemonId'] ?? '60';

            $additionalXml = '<additional_SCR INN="' . $inn . '" SNILS="' . $snils . '" ads="0" bankrot="" creditRating4Sale="' . $creditRating4Sale . '" daemonId="' . $daemonId . '" decisionMessage="' . $decisionMessage . '" decisionSum="' . $decisionSum . '" decisionType="' . $decisionType . '" no_need_for_underwriter="' . ($success ? '1' : '') . '" pti_RosStat="" pti_ch="">';

            // Add credit history additional data
            if (isset($additional['creditHistory_ADD'])) {
                $chAdd = $additional['creditHistory_ADD'];
                $additionalXml .= '<creditHistory_ADD_SCR addrFactCnt="' . ($chAdd['addrFactCnt'] ?? '0') . '" addrFactDateLife="' . ($chAdd['addrFactDateLife'] ?? '0') . '" addrFactText="" addrRegCnt="' . ($chAdd['addrRegCnt'] ?? '0') . '" addrRegDateLife="' . ($chAdd['addrRegDateLife'] ?? '0') . '" addrRegText="" score="' . ($chAdd['score'] ?? '840') . '" score2="" score3="">';

                // Add various credit history elements
                $elements = [
                    'shareOfOverdueLoans30', 'shareOfOverdueLoans', 'opened30', 'opened30Limit',
                    'overdue30', 'overdue', 'outstanding', 'closed30', 'closedSum30',
                    'countCredits', 'chAge', 'closedCnt', 'closedSum', 'activeCnt', 'activeSum',
                    'goodCreditHistory_creditLimitResult', 'unknownCreditHistory_creditLimitResult',
                    'negativeCreditHistory_creditLimitResult', 'overdueLoans', 'overdueLoans30',
                    'sumCredits', 'creditLimits30'
                ];

                foreach ($elements as $element) {
                    $result = $chAdd[$element]['result'] ?? '0';
                    $description = htmlspecialchars($chAdd[$element]['description'] ?? '');
                    $additionalXml .= '<' . $element . '_SCR description="' . $description . '" result="' . $result . '"></' . $element . '_SCR>';
                }

                $additionalXml .= '<phones_CH_ADD_SCR description="Телефоны из кредитной истории"></phones_CH_ADD_SCR>';
                $additionalXml .= '<addrRegUnic_SCR description="Адреса регистрации"></addrRegUnic_SCR>';
                $additionalXml .= '<addrFactUnic_SCR description="Адреса проживания"></addrFactUnic_SCR>';
                $additionalXml .= '<addrs_SCR description="Адреса из кредитной истории"></addrs_SCR>';
                $additionalXml .= '</creditHistory_ADD_SCR>';
            }

            // Add trust rating additional data
            if (isset($additional['trustRating_ADD'])) {
                $trAdd = $additional['trustRating_ADD'];
                $additionalXml .= '<trustRating_ADD_SCR score="' . ($trAdd['score'] ?? '634') . '">';

                $trElements = ['timeCellular', 'cellularInCH', 'homePhoneInCH', 'workPhoneInCH', 'inquire7', 'inquire30'];
                foreach ($trElements as $element) {
                    $result = $trAdd[$element]['result'] ?? '0';
                    $description = htmlspecialchars($trAdd[$element]['description'] ?? '');
                    $additionalXml .= '<' . $element . '_SCR description="' . $description . '" result="' . $result . '"></' . $element . '_SCR>';
                }

                $additionalXml .= '</trustRating_ADD_SCR>';
            }

            // Add other additional elements
            $additionalXml .= '<loanInfo_SCR closedLoans="0" numberLoansRepaid="0"></loanInfo_SCR>';

            if (isset($additional['fssp'])) {
                $fssp = $additional['fssp'];
                $fsspResult = $fssp['result'] ?? '0';
                $fsspDesc = htmlspecialchars($fssp['description'] ?? 'Исполнительное производство:');
                $fsspText = htmlspecialchars($fssp['textResult'] ?? 'Исполнительное производство не найдено');
                $fsspSum = $fssp['sum_FSSP']['result'] ?? '0';

                $additionalXml .= '<fssp_SCR description="' . $fsspDesc . '" result="' . $fsspResult . '" textResult="' . $fsspText . '">';
                $additionalXml .= '<sum_FSSP_SCR description="Сумма долгов по ФССП" result="' . $fsspSum . '"></sum_FSSP_SCR>';
                $additionalXml .= '</fssp_SCR>';
            }

            $additionalXml .= '<paySystem_SCR description="Балл по платежным сервисам" result="" score=""></paySystem_SCR>';

            if (isset($additional['recFactors'])) {
                $additionalXml .= '<recFactors_SCR>';
                if (isset($additional['recFactors']['recFactor'])) {
                    $factorName = $additional['recFactors']['recFactor']['factor_name'] ?? 'passportExpiredFms';
                    $additionalXml .= '<recFactor_SCR factor_name="' . $factorName . '"></recFactor_SCR>';
                }
                $additionalXml .= '</recFactors_SCR>';
            }

            // Add calculation data
            if (isset($additional['calculationData_for_PTI'])) {
                $calcData = $additional['calculationData_for_PTI'];
                $additionalXml .= '<calculationData_for_PTI_SCR';
                for ($i = 23; $i <= 47; $i++) {
                    $value = $calcData['f_' . $i] ?? '';
                    $additionalXml .= ' f_' . $i . '="' . $value . '"';
                }
                $additionalXml .= '></calculationData_for_PTI_SCR>';
            }

            // Add summary
            if (isset($additional['summary'])) {
                $summaryData = $additional['summary'];
                $chQty = $summaryData['chQty'] ?? '1';
                $cutoff = $summaryData['cutoff'] ?? '530';
                $score = $summaryData['score'] ?? '840';

                $additionalXml .= '<summary_SCR chQty="' . $chQty . '" cutoff="' . $cutoff . '" score="' . $score . '">';
                $additionalXml .= '<distribution_SCR>';
                if (isset($summaryData['distribution']['distribution_num'])) {
                    $num = $summaryData['distribution']['distribution_num']['num'] ?? '12';
                    $additionalXml .= '<distribution_num_SCR num="' . $num . '"></distribution_num_SCR>';
                }
                $additionalXml .= '</distribution_SCR>';
                $additionalXml .= '</summary_SCR>';
            }

            $additionalXml .= '<prohibitions_SCR NBKI="" SB="" ssp=""></prohibitions_SCR>';
            $additionalXml .= '</additional_SCR>';
        }

        // Build the complete Response_SCR XML
        $responseSCR = '<Response_SCR agrid="' . $agrid . '" status="DONE">';
        $responseSCR .= '<data_SCR fraudFactors="">';
        $responseSCR .= $decisionXml;
        $responseSCR .= '<stopFactors_SCR>' . $stopFactorsXml . '</stopFactors_SCR>';
        $responseSCR .= '<fraudFactors_SCR></fraudFactors_SCR>';
        $responseSCR .= $creditHistoryXml;
        $responseSCR .= $trustRatingXml;
        $responseSCR .= $additionalXml;
        $responseSCR .= '</data_SCR>';
        $responseSCR .= '</Response_SCR>';

        return $responseSCR;
    }

    /**
     * Определить, является ли заявка кросс-заявкой
     * @param object $order
     * @return bool
     */
    private function isCrossOrder(object $order): bool
    {
        return $order->utm_source === $this->orders::UTM_SOURCE_CROSS_ORDER;
    }

    public function set_organization_params($order)
    {
        $this->axilogin = $this->config->axi_login;
        $this->axipassword = $this->config->axi_password;

        $this->axiConfig = $this->organizations_data->getAxiConfig((int) $order->organization_id, $this->isCrossOrder($order));

        if ($this->axiConfig) {
            $this->service_url = 'http://' . $this->axiConfig->service_ip . ':8080/' . $this->axiConfig->version . '/';
            $this->create_action = $this->axiConfig->create_action;
            $this->setCommand($this->axiConfig->command);
        }

        $credentions  = $this->organizations_data->get_data($order->organization_id, self::AXI_CREDENTIONS_KEY);

        if (!empty($credentions->login) && !empty($credentions->password)) {
            $this->axilogin = $credentions->login;
            $this->axipassword = $credentions->password;
        }
    }

    private function get_app_order_id($app_id)
    {
        $this->db->query("
            SELECT order_id FROM s_axilink
            WHERE app_id = ?
        ", (string)$app_id);
        return $this->db->result('order_id');
    }

    /**
     * Получение причины отказа по строке стоп-факторов
     * @param $user_id
     * @param string $factors_string Строка стоп-факторов из ответа акси
     * @return false|int
     */
    public function getRejectReason($user_id, string $factors_string)
    {
        if (empty($factors_string))
            return false;

        // Первый стоп-фактор в списке
        $reason_key = explode(";", $factors_string, 2)[0];
        $reason_id = (int)(self::REJECT_REASONS[$reason_key] ?? $this->reasons::REASON_UNKNOWN_AXI);

        // Отдельная логика для не найденного ИНН
        if ($reason_id == $this->reasons::REASON_INN_NOT_FOUND) {
            if ($this->user_data->read($user_id, 'inn_not_found') == 1) {
                // ИНН не найден во второй раз, отправляем в автоотказ
                return $reason_id;
            }

            // ИНН не найден в первый раз, автоотказа нет
            $this->user_data->set($user_id, 'inn_not_found', 1);
            return false;
        }

        // ИНН найден, убираем плашку с предупреждением
        $this->user_data->set($user_id, 'inn_not_found', 0);

        // Если причина отказа по возрасту или региону И
        // для заявки включен скоринг hyper-c И
        // скоринг hyper_c НЕ в тестовом режиме,
        // то НЕ автоотказываем по акси
        if (in_array($reason_id, [$this->reasons::REASON_AGE, $this->reasons::REASON_LOCATION])) {
            $order = $this->orders->get_user_last_order($user_id);
            if ($this->scorings->isHyperEnabledForOrder($order) && !$this->scorings->isHyperCInTestMode()) {
                return false;
            }
        }

        return $reason_id;
    }

    /**
     * Убирает @ из названий ключей полученных из XML
     * @param $data
     * @return array
     */
    private function stripAtKeys($data) {
        if (is_array($data)) {
            $out = [];
            foreach ($data as $key => $value) {
                $newKey = is_string($key) ? ltrim($key, '@') : $key;
                $out[$newKey] = $this->stripAtKeys($value);
            }
            return $out;
        } elseif (is_object($data)) {
            return $this->stripAtKeys((array)$data);
        } else {
            return $data;
        }
    }

    /**
     * Массив вида
     * ```
     * {
     *  "AntiFraud_score": { "value": "0.35", "_value": null },
     * }
     * ```
     * Преобразует в
     * ```
     * {
     *  "AntiFraud score": 0.55,
     * }
     * ```
     * @param array $raw
     * @return array
     */
    private function formatJuiceBody(array $raw): array {
        $result = [];

        // Карта переименований верхнего уровня
        $map = [
            'AntiFraud_score' => 'AntiFraud score',
            'Additional_info' => 'Additional Info',
            'Device_id' => 'Device id',
            'Exact_Device_id' => 'Exact Device id',
            'Browser_hash' => 'Browser hash',
            'User_id' => 'User id',
            'Success' => 'Success',
            'Time' => 'Time',
        ];

        foreach ($map as $source => $target) {
            if (!isset($raw[$source])) continue;

            $value = $raw[$source]['value'] ?? $raw[$source];

            if ($source === 'Success') {
                $result[$target] = $value === '1';
            } elseif (is_numeric($value)) {
                $result[$target] = $value + 0;
            } else {
                $result[$target] = $value;
            }
        }

        // Обработка predictors
        if (!empty($raw['Predictors_JS']) && is_array($raw['Predictors_JS'])) {
            $predictors = [];

            foreach ($raw['Predictors_JS'] as $k => $v) {
                // Удаление "_" и капитализация
                $formattedKey = preg_replace_callback('/(^|_)([a-z0-9]+)/i', function($m) {
                    return ($m[1] ? ' ' : '') . ucfirst($m[2]);
                }, $k);

                $predictors[$formattedKey] = $v;
            }

            $result['Predictors'] = $predictors;
        }

        return $result;
    }

    /**
     * Рекурсивная функция для изменения структуры ответа скористы
     * @param object $raw "Сырой" ответ скористы в том виде, в котором его отдаёт Акси
     * @return object Отформатированный ответ имитирующий оригинальные ответы скоринга
     */
    private function formatScoristaResponse($raw)
    {
        if (is_object($raw)) {
            return $this->formatScoristaResponse((array)$raw);
        }

        if (!is_array($raw)) {
            return $raw;
        }

        $out = [];
        foreach ($raw as $key => $value) {
            if (is_string($key)) {
                $key = ltrim($key, '@');
                $key = rtrim($key, '_SCR');
                $key = rtrim($key, '_TR'); // _TR_SCR в одном из ключей
            }

            if (!empty($value) && is_numeric($value)) {
                // Приводим к числу, если это возможно и значение не пустое
                $value = $value + 0;
            } elseif ($key === 'result' && $value === '') {
                // Пустые значения как в оригинальных ответах скористы
                $value = null;
            }

            if ($key === '_value' && $value === null) {
                continue;
            }

            $out[$key] = $this->formatScoristaResponse($value);
        }

        return (object)$out;
    }

    public function getServiceUrl(): string
    {
        return $this->service_url;
    }

    /**
     * Проверка NBKI score (используется при отключенной проверке ПДН)
     * 
     * Бизнес-логика:
     * - Если NBKI score > MIN_NBKI_SCORE - проходит
     * - Если NBKI score <= MIN_NBKI_SCORE - проверяем наличие одобренных Скорист в Boostra
     * - Ищем заявки того же клиента по UID
     * - Учитываем только Скористы за последние 10 дней
     * - Если найдена одобренная Скориста - проходит (оставляем в статусе "Новая")
     * 
     * @param object $scoring
     * @param object $user
     * @return bool true если NBKI score прошел проверку, false если отклонен
     */
    private function checkNbkiScore(object $scoring, object $user): bool
    {
        $nbkiScore = $this->data['nbki_score'] ?? null;

        if ($nbkiScore === null) {
            $this->logging(__METHOD__, 'NBKI score check', [
                'order_id' => $scoring->order_id,
                'message' => 'NBKI score не найден при отключенном ПДН'
            ], [], 'dbrainAxi.log');
            return false;
        }

        if ($nbkiScore > self::MIN_NBKI_SCORE) {
            return true;
        }

        $boostraUsers = $this->users->get_users([
            'uid' => $user->UID,
            'site_id' => $this->organizations::SITE_BOOSTRA
        ]);

        if (empty($boostraUsers)) {
            $this->logging(__METHOD__, 'NBKI score check - Boostra users not found', [
                'order_id' => $scoring->order_id,
                'user_id' => $user->id,
                'uid' => $user->UID,
                'nbki_score' => $nbkiScore,
                'message' => 'Пользователи в Boostra не найдены'
            ], [], 'dbrainAxi.log');
            return false;
        }

        $tenDaysAgo = date('Y-m-d H:i:s', strtotime('-10 days'));

        foreach ($boostraUsers as $boostraUser) {
            $approvedScoristas = $this->scorings->get_scorings([
                'user_id' => $boostraUser->id,
                'type' => $this->scorings::TYPE_SCORISTA,
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => 1,
                'start_date_from' => $tenDaysAgo,
                'sort' => 'date_desc',
            ]);

            if (!empty($approvedScoristas)) {
                $linkedScorista = reset($approvedScoristas);

                $this->order_data->set(
                    $scoring->order_id,
                    $this->order_data::LINK_ORDER_SCORISTA,
                    "{$this->config->back_url}/order/{$linkedScorista->order_id}"
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Логирование настройки disable_pdn_check для отладки
     * @param string $url Место вызова логирования
     * @param array $data Массив данных для логирования
     * @return void
     */
    private function logDisablePdnCheck(string $url, array $data): void
    {
        $this->logging(
            __METHOD__,
            $url,
            $data,
            '',
            'test_disable_pdn_check.txt'
        );
    }

}
