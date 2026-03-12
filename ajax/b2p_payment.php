<?php

error_reporting(0);
ini_set('display_errors', 'Off');
date_default_timezone_set('Europe/Moscow');
session_start();

require_once('../api/Simpla.php');
require_once('../app/Core/Application/Application.php');

use App\Core\Application\Application;
use App\Modules\NewYearPromotion\Services\NewYearPromotionService;
use App\Modules\Payment\Services\AddCardService;

class Best2payAjax extends Simpla
{
    /**
     * Allowed domains
     * @var array|string[]
     */
    private $allowedOrigins = [
        'http://manager.boostra.ru',
        'http://51.250.29.162',
        'http://localhost:8000',
    ];

    protected $response = array();

    protected $user = null;
    
    private NewYearPromotionService $promoService;
    private AddCardService $addCardService;

    public function __construct()
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->promoService = $app->make(NewYearPromotionService::class);
        $this->addCardService = $app->make(AddCardService::class);

        if (!empty($_SESSION['user_id'])) {
            $this->user = $this->users->get_user((int)$_SESSION['user_id']);
        }
    }

    public function run()
    {
        $action = $this->request->get('action', 'string');

        switch($action):

            case 'attach_card':
                $this->attach_card();
                break;

            case 'attach_sbp':
                $this->attach_sbp();
                break;

            case 'get_sb_accounts':
                $this->get_sb_accounts();
                break;

            case 'get_payment_link':
                $this->get_payment_link();
                break;

            case 'recurrent':
                $this->recurrent_action();
                break;

            case 'attach_sbp_registration':
                $this->attach_sbp_registration();
                break;

            case 'get_state':
                // что бы не было ошибки
                break;

            default:
                $this->response['error'] = 'UNDEFINED_ACTION';

        endswitch;

        $this->output();
    }

    private function recurrent_action()
    {
        if (!empty($_SESSION['looker_mode']))
            return false;

        $card_id = $this->request->get('card_id', 'integer');
        $amount = (float)str_replace(',', '.', $this->request->get('amount'));
        $contract_id = $this->request->get('contract_id', 'integer');
        $prolongation = $this->request->get('prolongation', 'integer');

        if (empty($amount))
        {
            $this->response['error'] = 'EMPTY_AMOUNT';
        }
        elseif (empty($card_id))
        {
            $this->response['error'] = 'EMPTY_CARD';
        }
        elseif (!($card = $this->cards->get_card($card_id)))
        {
            $this->response['error'] = 'UNDEFINED_CARD';
        }
        else
        {
            $description = "Оплата по договору ".$contract_id;
            $amount = $amount * 100;
            $response = $this->best2pay->recurrent_pay($card->id, $amount, $description, $contract_id, $prolongation);
            if (empty($response))
                $this->response['error'] = 'Не удалось оплатить';
            else
                $this->response['success'] = 1;
        }
    }

    private function get_user_balance($user_id, $number)
    {
        try {
            // Сперва пытаемся получить баланс из БД по номеру договора
            if ($user_balance = $this->users->get_user_balance($user_id, ['zaim_number' => $number])) {
                return $user_balance;
            } elseif ($user_balance = $this->users->get_user_balance($user_id)) { // Если не нашли по номеру, то пробуем получить баланс по id пользователя
                return $user_balance;
            }

            $user = $this->users->get_user_by_id($user_id);
            $response_balances = $this->soap->get_user_balances_array_1c($user->uid);

            $order_balance = array_filter($response_balances, function ($item) use ($number) {
                return $item['НомерЗайма'] == $number;
            });
            $order_balance = array_shift($order_balance);

            $user_balance = (object) $order_balance ?: $this->users->get_user_balance_1c($user->uid, false);

            return $this->users->make_up_user_balance($user_id, $user_balance);
        } catch (\Throwable $e) {
            $error = [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
            $this->logging(__METHOD__, 'get_payment_link', $_REQUEST, $error, 'b2p_payment.txt');

            return null;
        }
    }

    private function get_payment_link()
    {
        $payment_method = $this->request->get('payment_method');
        $number = $this->request->get('number');
        $user_id = $this->request->get('user_id', 'integer') ?: $this->user->id;
        $order_id = $this->request->get('order_id', 'integer');
        $organization_id = $this->request->get('organization_id', 'integer');
        $amount = (float)str_replace(',', '.', $this->request->get('amount'));
        $sms = $this->request->get('code_sms', 'string');
        $insure = (float)$this->request->get('insure');
        $prolongation = $this->request->get('prolongation', 'integer');
        $prolongation_day = $this->request->get('prolongation_day', 'integer');
        $create_from = $this->request->get('from', 'string');
        $refinance = $this->request->get('refinance', 'integer');
        $isWeb = $this->request->get('web', 'int');
        $action_type = $this->request->get('action_type');
        $referralDiscountAmount = $this->request->get('referral_discount_amount', 'float') ?? 0;

        $user_balance = $this->get_user_balance($user_id, $number);

        $collection_promo = $this->request->get('collection_promo', 'integer');
        // По условиям задачи $amount не трогаем
        // Все скидки передаются отдельными полями, а $amount - отдельно
        if (!($collection_promo > 0 && $this->best2pay->checkDebtAndPromo($user_balance, $collection_promo, $amount, $prolongation))) {
            $collection_promo = 0;
        }
        
        // Обработка новогодней скидки
        // ВАЖНО: Скидка применяется ТОЛЬКО для полного погашения!
        if (!empty($order_id) && !empty((int)$this->settings->newyear_promotion_enabled)) {
            // Проверяем, что это полное погашение
            $is_full_payment = in_array($action_type, [
                $this->star_oracle::ACTION_TYPE_FULL_PAYMENT,
                $this->star_oracle::ACTION_TYPE_RECURRING_FULL_PAYMENT
            ]);
            
            if ($is_full_payment) {
                // Проверяем, что пользователь участвует в акции (единственный источник истины - баланс из 1С)
                if ($this->promoService->isUserInPromo($user_id, $order_id, $user_balance)) {
                    // Проверяем, что скидка активна
                    if ($this->promoService->isDiscountActive($user_balance)) {
                        // Рассчитываем скидку на сервере (не доверяем значению из запроса)
                        $newyear_discount = $this->promoService->getDiscountAmount($user_balance);

                        if ($newyear_discount > 0) {
                            // Добавляем новогоднюю скидку к общей скидке
                            $collection_promo += $newyear_discount;
                        }
                    }
                }
            }
        }

        $order = $this->orders->get_crm_order($order_id);

        if (!empty($prolongation)) {
            $multipolis_amount = ($_SESSION['prolongation_data']['multipolis_amount'] ?? $this->request->get('multipolis_amount', 'integer')) * (int)$order->additional_service_multipolis;
            $multipolis = $_SESSION['prolongation_data']['multipolis'] ?? $this->request->get('multipolis', 'integer');

            $tv_medical = $_SESSION['prolongation_data']['tv_medical'] ?? $this->request->get('tv_medical', 'integer');
            $tv_medical_id = $_SESSION['prolongation_data']['tv_medical_id'] ?? $this->request->get('tv_medical_id', 'integer');
            $tv_medical_amount = ($_SESSION['prolongation_data']['tv_medical_amount'] ?? $this->request->get('tv_medical_amount', 'integer')) * (int)$order->additional_service_tv_med;

            $star_oracle = 0;
            $star_oracle_id = 0;
            $star_oracle_amount = 0;
        } else {
            $multipolis_amount = $this->request->get('multipolis_amount', 'integer');
            $multipolis = $this->request->get('multipolis', 'integer');

            $tv_medical = $this->request->get('tv_medical', 'integer');
            $tv_medical_id = $this->request->get('tv_medical_id', 'integer');
            $tv_medical_amount = $this->request->get('tv_medical_amount', 'integer');

            $star_oracle = $this->request->get('star_oracle', 'integer');
            $star_oracle_id = $this->request->get('star_oracle_id', 'integer');
            $star_oracle_amount = $this->request->get('star_oracle_amount', 'integer');
        }

        $card_id = $this->request->get('card_id', 'integer');
        $creditRatingType = $this->request->get('credit_rating_type', 'integer');
        $refuser = $this->request->get('refuser', 'integer');
        $graceType = $this->request->get('grace_payment', 'integer');

        $chdp = $this->request->get('chdp', 'integer');
        $pdp = $this->request->get('pdp', 'integer');

        $calc_percents = $this->request->get('calc_percents', 'integer');
        $recurring_payment_so = $this->request->get('recurring_payment_so', 'integer');

        // для рекуррентного платежа
        $recurring_data = [];
        if ($recurring_payment_so) {
            $recurring_data = $this->star_oracle->getStarOracleRepaymentData($order, $amount, $action_type);

            $star_oracle = 0;
            $star_oracle_id = 0;
            $star_oracle_amount = 0;
        }

        if (empty($amount)) {
            $this->response['error'] = 'EMPTY_AMOUNT';
        } else {
            $params = array(
                'user_id' => $user_id,
                'order_id' => $order_id,
                'number' => $number,
                'card_id' => $payment_method == 'sbp' ? 0 : $card_id,
                'amount' => $amount,
                'insure' => $insure,
                'multipolis' => (int)($multipolis && $multipolis_amount > 0),
                'multipolis_amount' => $multipolis_amount,
                'tv_medical' => (int) ($tv_medical && $tv_medical_amount > 0),
                'tv_medical_id' => $tv_medical_id,
                'tv_medical_amount' => $tv_medical_amount,
                'star_oracle' => (int) ($star_oracle && $star_oracle_amount > 0),
                'star_oracle_id' => $star_oracle_id,
                'star_oracle_amount' => $star_oracle_amount,
                'prolongation' => $prolongation,
                'prolongation_day' => $prolongation_day,
                'asp' => $sms,
                'payment_type' => 'debt',
                'calc_percents' => $calc_percents,
                'grace_payment' => $graceType,
                'organization_id' => $organization_id,
                'chdp' => $chdp,
                'pdp' => $pdp,
                'payment_method' => $payment_method,
                'create_from' => $create_from,
                'refinance' => $refinance,
                'discount_amount' => $collection_promo,
                'referral_discount_amount' => $referralDiscountAmount,
                'action_type' => $action_type
            );

            if (!empty($recurring_data)) {
                $params['recurring_data'] = $recurring_data;
            }

            if ($creditRatingType && $paymentType = ($this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING[$creditRatingType] ?? null)) {
                $params['payment_type'] = $paymentType;
            } elseif ($refuser) {
                $params['payment_type'] = $this->best2pay::PAYMENT_TYPE_REFUSER;
                $params['amount'] = 49;
                $params['organization_id'] = $this->organizations->get_base_organization_id(['user_id' => $user_id]);
            }

            $payment_id = $this->best2pay->get_payment_link($params);

            $this->logging(__METHOD__, 'payment data from front', (array)$params, [
                    'payment_id' => $payment_id,
                    'is_web' => $isWeb,
                    'prolongation_session' => $_SESSION['prolongation_data'] ?? [],
                ]
                , 'b2p_payment.txt');

            if (isset($_COOKIE['source_for_pay'])) {
                try {
                    $params['payment_id'] = $payment_id;
                    $log_data = [
                        'user_id' => $params['user_id'],
                        'event' => $payment_id ? 'get_payment_link_success' : 'get_payment_link_error',
                        'source' => $_COOKIE['source_for_pay'],
                        'params' => json_encode($params),
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->best2pay->add_source_log($log_data);
                } catch (Throwable $exception) {
                }
            }

            if ($payment_id) {
                $this->response = $this->best2pay->get_payment($payment_id); // получение id оплаты по займу из b2p_payments
            } else {
                $this->response['error'] = 'NOT PAYMENT';
            }
        }
    }

    private function attach_card()
    {
        $user_id = $this->request->get('user_id');
        $organization_id = $this->request->get('organization_id', 'integer');
        $recurring_consent = $this->request->get('recurring_consent', 'integer');

        $card_id = $this->request->get('card_id', 'integer');

        if (empty($user_id)) {
            $user_id = $this->user->id;
        }

        $this->response['link'] = $this->addCardService->getAddCardLink(
            $user_id,
            $organization_id,
            $card_id,
            $recurring_consent
        );
    }

    private function attach_sbp()
    {
        $user_id = $this->request->get('user_id');
        $user_id = (int) $user_id ? $user_id : $this->user->id;
        $recurring_consent = $this->request->get('recurring_consent', 'integer');

        if (empty($user_id)) {
            $user_id = $this->user->id;
        }

        try {
            // Если пользователь использует СБП ТБанка
            if ($this->users->user_uses_sbp_tbank((int) $user_id)) {
                $this->response['link'] = $this->TBankService->addAccountQr([
                    'user_id' => $user_id,
                    'description' => 'Привязка карты к личному кабинету',
                ]);
                $this->logging(__METHOD__, 'attach_sbp', [
                    'user_id' => $user_id,
                    'description' => 'Привязка карты к личному кабинету',
                ], $this->response, date('d-m-Y').'-t-bank-error.txt');
            } else {
                $this->response['link'] = $this->best2pay->get_link_add_sbp($user_id, $recurring_consent);
            }
        } catch (Throwable $exception) {
            $this->response['error'] = 'Не удалось привязать карту';
            $this->logging(__METHOD__, 'attach_sbp', [
                'user_id' => $user_id,
                'description' => 'Привязка карты к личному кабинету',
            ], ['error' => $exception->getMessage()], date('d-m-Y').'-t-bank-error.txt');
            return;
        }
    }

    private function get_sb_accounts()
    {
        $this->response['accounts'] = [];
        if (isset($this->user->id) && !empty($this->user->id)) {
            $this->response['accounts'] = $this->users->getSbpAccounts($this->user->id) ?? [];
        }
    }

    /**
     * Привязка счета СБП для выплаты денег при получении займа
     */
    private function attach_sbp_registration()
    {
        if (!$this->best2pay->canAddSbpAccount((int)$this->user->id)) {
            $this->response = [
                'success' => false,
                'error' => 'Возникла ошибка. При сохранении ошибки обратитесь в поддержку или добавьте карту',
            ];
            return;
        }

        $addSbpLink = $this->best2pay->get_link_add_sbp((int)$this->user->id);

        if (empty($addSbpLink)) {
            $this->response = [
                'success' => false,
                'error' => 'Возникла ошибка при подключении СБП. При сохранении ошибки обратитесь в поддержку или добавьте карту',
            ];

            return;
        }

        $this->response = [
            'success' => true,
            'link' => $addSbpLink,
        ];
    }

    protected function output()
    {
        $origin = (!empty($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $this->allowedOrigins)) ? $_SERVER['HTTP_ORIGIN'] : $this->allowedOrigins[0];
        header("Access-Control-Allow-Origin: {$origin}");
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");

        echo json_encode($this->response);
    }
}
$ajax = new Best2payAjax();
$ajax->run();
exit;
