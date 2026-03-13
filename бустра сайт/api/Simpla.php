<?php
require_once __DIR__ . '/../vendor/autoload.php';

use api\helpers\NewYearHelper;
use App\Service\UsedeskService;

ini_set("soap.wsdl_cache_enabled", 1);
ini_set("soap.wsdl_cache_ttl", 86400);
ini_set("default_socket_timeout", 200);

require_once __DIR__ . '/ExceptionHandler.php';

define('Simpla', str_replace('api', '', __DIR__));
spl_autoload_register(function ($className) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, Simpla . $className . '.php');

    if (!is_file($file)) {
        $file = __DIR__ . DIRECTORY_SEPARATOR . $className . '.php';
    }
    if (!is_file($file)) {
        $file = Simpla . 'view' . DIRECTORY_SEPARATOR . $className . '.php';
    }
    if (is_file($file)) {
        include_once $file;
    }
});


/**
 * Основной класс Simpla для доступа к API Simpla
 *
 * @copyright 	2014 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */

/**
 * @property AutomationFails $automationFails
 * @property Config $config
 * @property Request $request
 * @property Response $response
 * @property Database $db
 * @property DatabaseAccess $dbAccess
 * @property Settings $settings
 * @property Design $design
 * @property Products $products
 * @property NewYearPromo $newYearPromo
 * @property NewYearHelper $newYearHelper
 * @property Variants $variants
 * @property Categories $categories
 * @property Brands $brands
 * @property CarDepositApplication $car_deposit_application
 * @property Features $features
 * @property Money $money
 * @property Pages $pages
 * @property Blog $blog
 * @property Cart $cart
 * @property Image $image
 * @property Delivery $delivery
 * @property Payment $payment
 * @property Orders $orders
 * @property Users $users
 * @property UserEmails $userEmails
 * @property UserPhones $userPhones
 * @property UserUsedesk $userUsedesk
 * @property Coupons $coupons
 * @property Comments $comments
 * @property Feedbacks $feedbacks
 * @property Notify $notify
 * @property Managers $managers
 * @property Soap1c $soap
 * @property Transactions $transactions
 * @property Tinkoff $tinkoff
 * @property TinkoffId $tinkoffId
 * @property Docs $docs
 * @property Cloudkassir $cloudkassir
 * @property Fssp $fssp
 * @property Anticaptcha $anticaptcha
 * @property Fms $fms
 * @property Scorings $scorings
 * @property Referrals $referrals
 * @property Local_time $local_time
 * @property Location $location
 * @property Exitpools $exitpools
 * @property Contactpersons $contactpersons
 * @property Filestorage $filestorage
 * @property Import1c $import1c
 * @property Discounts $discounts
 * @property PaymentExitpools $payment_exitpools
 * @property Events $events
 * @property Visitors $visitors
 * @property Reasons $reasons
 * @property CDoctor $cdoctor
 * @property Individuals $individuals
 * @property Best2pay $best2pay
 * @property Recaptcha $recaptcha
 * @property SmsMessages $sms
 * @property AuthCodes $authcodes
 * @property Curl $curl
 * @property Chats $chats
 * @property Tasks $tasks
 * @property Appeals $appeals
 * @property CreditDoctor $credit_doctor
 * @property StarOracle $star_oracle
 * @property CreditRating $credit_rating
 * @property Documents $documents
 * @property Pdf $pdf
 * @property AmoCrm $amo_crm
 * @property Helpers $helpers
 * @property PartnerHref $partner_href
 * @property UserCreditDoctor $user_credit_doctor
 * @property YooKassaApi $yookassa_api
 * @property AmoCrmApi $amo_crm_api
 * @property UniSender $uni_sender
 * @property PostBack $post_back
 * @property AccountContract $account_contract
 * @property UniBell $uniBell
 * @property SmsValidate $sms_validate
 * @property OrdersAutoApprove $orders_auto_approve
 * @property CustomMetric $custom_metric
 * @property Queue $queue
 * @property Promocodes $promocodes
 * @property PushToken $push_token
 * @property Multipolis $multipolis
 * @property TVMedical $tv_medical
 * @property Receipts $receipts
 * @property Contracts $contracts
 * @property Changelogs $changelogs
 * @property Organizations $organizations
 * @property OrganizationsData $organizations_data
 * @property Installments $installments
 * @property OrderData $order_data
 * @property LeadPrice $leadPrice
 * @property LeadPriceLogs $leadPriceLogs
 * @property Likezaim $likezaim
 * @property Tickets $tickets
 * @property Faq $faq
 * @property VerificationCards $verification_cards
 * @property CompanyOrders $company_orders
 * @property BlockedAdvSms $blocked_adv_sms
 * @property VkApi $vk_api
 * @property HideService $hide_service
 * @property UserData $user_data
 * @property MobileId $mobileId
 * @property VkMessageSettings $vk_message_settings
 * @property Cyberity $cyberity
 * @property Bonondo $bonondo
 * @property Leadgid $leadgid
 * @property Complaint $complaint
 * @property ShortFlow $short_flow
 * @property NotifyApi $notifyApi
 * @property UserUtm $userUtm
 * @property TBankService $TBankService
 * @property TBankApiService $TBankApiService
 * @property TBankDatabaseService $TBankDatabaseService
 * @property TBankValidateService $TBankValidateService
 * @property TBankLoggingService $TBankLoggingService
 * @property DOPLicense $dop_license
 * @property SelfDec $self_dec
 * @property Idx $idx
 * @property Axi $axi
 * @property Autoconfirm $autoconfirm
 * @property TBankIdService $TBankIdService
 * @property TBankId $TBankId
 * @property Refinance $refinance
 * @property EsiaService $esia_service
 * @property B2pBankList $b2p_bank_list
 * @property SmsAuthValidate $sms_auth_validate_sms
 * @property ServiceAuthLogs $service_auth_logs
 * @property PromoEvents $promo_events
 * @property NotificationCenter $notification_center
 * @property AbTestService $ab_test_service
 * @property RestApiPartner $rest_api_partner
 * @property PhonePartnerModel $phone_partner_model
 * @property OpenSearchLogger $open_search_logger
 * @property OverdueSliderService $overdue_slider_service
 * @property Ping3Data $ping3_data
 * @property Caches $caches
 * @property VirtualCard $virtualCard
 * @property Rcl $rcl
 * @property FakeOffer $fake_offer
 */
class Simpla
{
	// Свойства - Классы API
    private $classes = [
        'automationFails' => 'AutomationFails',
        'config'            => 'Config',
        'request'           => 'Request',
        'response'          => 'Response',
        'db'                => 'Database',
        'dbAccess'          => 'DatabaseAccess',
        'settings'          => 'Settings',
        'design'            => 'Design',
        'products'          => 'Products',
        'variants'          => 'Variants',
        'categories'        => 'Categories',
        'brands'            => 'Brands',
        'car_deposit_application'            => 'CarDepositApplication',
        'features'          => 'Features',
        'money'             => 'Money',
        'pages'             => 'Pages',
        'blog'              => 'Blog',
        'cart'              => 'Cart',
        'image'             => 'Image',
        'delivery'          => 'Delivery',
        'payment'           => 'Payment',
        'orders'            => 'Orders',
        'users'             => 'Users',
        'userEmails'        => 'UserEmails',
        'userPhones'        => 'UserPhones',
        'userUsedesk'       => 'UserUsedesk',
        'coupons'           => 'Coupons',
        'comments'          => 'Comments',
        'feedbacks'         => 'Feedbacks',
        'notify'            => 'Notify',
        'managers'          => 'Managers',
        'soap'              => 'Soap1c',
        'transactions'      => 'Transactions',
        'tinkoff'           => 'Tinkoff',
        'docs'              => 'Docs',
        'cloudkassir'       => 'Cloudkassir',
        'fssp'              => 'Fssp',
        'anticaptcha'       => 'Anticaptcha',
        'fms'               => 'Fms',
        'scorings'          => 'Scorings',
        'referrals'         => 'Referrals',
        'local_time'        => 'Local_time',
        'location'          => 'Location',
        'exitpools'         => 'Exitpools',
        'contactpersons'    => 'Contactpersons',
        'filestorage'       => 'Filestorage',
        'import1c'          => 'Import1c',
        'discounts'         => 'Discounts',
        'payment_exitpools' => 'PaymentExitpools',
        'events'            => 'Events',
        'visitors'          => 'Visitors',
        'reasons'           => 'Reasons',
        'cdoctor'           => 'CDoctor',
        'individuals'       => 'Individuals',
        'best2pay'          => 'Best2pay',
        'recaptcha'         => 'Recaptcha',
        'sms'               => 'SmsMessages',
        'authcodes'         => 'AuthCodes',
        'curl'              => 'Curl',
        'chats'             => 'Chats',
        'tasks'             => 'Tasks',
        'appeals'           => 'Appeals',
        'credit_doctor'     => 'CreditDoctor',
        'star_oracle' => 'StarOracle',
        'credit_rating'     => 'CreditRating',
        'documents'         => 'Documents',
        'pdf'               => 'Pdf',
        'amo_crm'           => 'AmoCrm',
        'verify_phone'      => 'VerifyPhone/VerifyPhone',
        'verify_phone_adapter'          => 'VerifyPhone/VerifyPhoneAdapter',
        'verify_connection_manage'      => 'VerifyPhone/VerifyConnectionManage',
        'helpers'           => 'Helpers',
        'partner_href' => 'PartnerHref',
        'user_credit_doctor' => 'UserCreditDoctor',
        'yookassa_api' => 'YooKassaApi',
        'amo_crm_api' => 'AmoCrmApi',
        'uni_sender' => 'UniSender',
        'post_back' => 'PostBack',
        'account_contract' => 'AccountContract',
        'unibell' => 'UniBell',
        'sms_validate' => 'SmsValidate',
        'orders_auto_approve' => 'OrdersAutoApprove',
        'custom_metric' => 'CustomMetric',
        'promocodes' => 'Promocodes',
        'queue' => 'Queue',
        'push_token' => 'PushToken',
        'multipolis' => 'Multipolis',
        'tv_medical' => 'TVMedical',
        'receipts' => 'Receipts',
        'organizations' => 'Organizations',
        'transfer' => 'Transfer',
        'acado' => 'Acado',
        'installments' => 'Installments',
        'order_data' => 'OrderData',
        'leadPrice' => 'LeadPrice',
        'leadPriceLogs' => 'LeadPriceLogs',
        'tickets' => 'Tickets',
        'faq' => 'Faq',
        'form' => 'Form',
        'company_orders' => 'CompanyOrders',
        'blocked_adv_sms' => 'BlockedAdvSms',
        'vk_api' => 'VkApi',
        'hide_service' => 'HideService',
        'user_data' => 'UserData',
        'tinkoffId' => 'TinkoffId',
        'cyberity' => 'Cyberity',
        'vk_message_settings' => 'VkMessageSettings',
        'mobileId' => 'MobileId',
        'bonondo' => Bonondo::class,
        'short_flow' => ShortFlow::class,
        'notifyApi' => NotifyApi::class,
        'spr_versions' => 'SprVersions',
        'userUtm' => 'UserUtm',
        'TBankService' => TBankService::class,
        'TBankApiService' => TBankApiService::class,
        'TBankDatabaseService' => TBankDatabaseService::class,
        'TBankValidateService' => TBankValidateService::class,
        'TBankLoggingService' => TBankLoggingService::class,
        'organizations_data' => OrganizationsData::class,
        'self_dec' => 'SelfDec',
        'dop_license' => DOPLicense::class,
        'autoconfirm' => 'Autoconfirm',
        'TBankIdService' => TBankIdService::class,
        'TBankId' => TBankId::class,
        'refinance' => Refinance::class,
        'esia_service' => EsiaService::class,
        'b2p_bank_list' => 'B2pBankList',
        'b2p_sbp' => 'B2pSbp',
        'sms_auth_validate_sms' => SmsAuthValidate::class,
        'service_auth_logs' => 'ServiceAuthLogs',
        'promo_events' => PromoEvents::class,
        'notification_center' => NotificationCenter::class,
        'ab_test_service' => AbTestService::class,
        'rest_api_partner' => 'RestApiPartner',
        'phone_partner_model' => 'PhonePartnerModel',
        'open_search_logger' => 'OpenSearchLogger',
        'overdue_slider_service' => OverdueSliderService::class,
        'ping3_data' => 'Ping3Data',
        'caches' => 'Caches',
        'rcl' => 'Rcl',
        'fake_offer' => 'FakeOffer',
    ];

    // Созданные объекты
	private static $objects = array();
    private static $simpla = null;

    private static $loggerState = [
        'method' => '',
        'url' => '',
        'request' => '',
        'log_filename' => 'soap.txt',
    ];

    public $is_developer = 0;
    public $is_admin = 0;
    public $is_CB = 0;

	/**
	 * Конструктор оставим пустым, но определим его на случай обращения parent::__construct() в классах API
	 */
	public function __construct()
	{
        self::$simpla = $this;

        if (isset($_GET['set_dev']))
        {
            if ($_GET['set_dev'] == 'bs29031981a')
                setcookie('developer', 'bs29031981a', time()+86400, '/');
            elseif ($_GET['set_dev'] == 'unset')
                setcookie('developer', NULL, time()-1, '/');
            header('Location: /');
            exit;
        }

        $access_devmode_ip_list = [
            '82.29.96.150', // rkopyl
            '82.29.94.121', // rkopyl
            '51.250.54.113', // Outline VPN
            '89.169.29.154', // WireGuard VPN
        ];
        if ((!empty($_COOKIE['developer']) && $_COOKIE['developer'] == 'bs29031981a') && in_array($_SERVER['REMOTE_ADDR'], $access_devmode_ip_list))
        {
            $this->is_developer = 1;
            error_reporting(-1);
            ini_set('display_errors', 'On');

        }

        if (!empty($_SESSION['looker_mode']))
            $this->is_looker = 1;

	}

	/**
	 * Магический метод, создает нужный объект API
	 */
    public function __get($name)
    {
        // Если уже создан объект, возвращаем его
        if (isset(self::$objects[$name])) {
            return self::$objects[$name];
        }

        // Список каталогов для поиска классов
        $directories = [
            dirname(__FILE__),
            dirname(__FILE__) . '/../lib/TBank'
        ];

        $class = null;
        $filename = null;

        // Сначала пробуем с ucfirst($name)
        foreach ($directories as $dir) {
            $temp = $dir . '/' . ucfirst($name) . '.php';
            if (file_exists($temp)) {
                $class = ucfirst($name);
                $filename = $temp;
                break;
            }
        }

        // Затем попробуем использовать сопоставление классов, если файл еще не найден.
        if (!$class && array_key_exists($name, $this->classes)) {
            foreach ($directories as $dir) {
                $temp = $dir . '/' . $this->classes[$name] . '.php';
                if (file_exists($temp)) {
                    $class = $this->classes[$name];
                    $filename = $temp;
                    break;
                }
            }
        }

        if (!$class) {
            return null;
        }

        // Подключаем файл класса и сохраните экземпляр
        include_once($filename);
        self::$objects[$name] = new $class();

        return self::$objects[$name];
    }

    public function new_logging($method, $url, $request, $response, $log_filename = 'soap.php')
    {
        $log = 1; // 1 - включить логирование, 0 - выключить

        if (empty($log))
            return false;

        $filename = $this->config->root_dir.'logs/'.$log_filename;

        if (date('d', filemtime($filename)) != date('d'))
        {
        	$file_basename = pathinfo($log_filename, PATHINFO_BASENAME);
            $archive_filename = $this->config->root_dir.'logs/archive/'.$file_basename.'_'.date('ymd', filemtime($filename));
            rename($filename, $archive_filename);
            file_put_contents($filename, "\xEF\xBB\xBF");
        }


        $string = '';
        $string .= PHP_EOL.'******************************************************'.PHP_EOL;
        $string .= date('d.m.Y H:i:s').PHP_EOL;
        $string .= $method.PHP_EOL;
        $string .= $url.PHP_EOL;

        if (!empty($_SERVER['REMOTE_ADDR']))
            $string .= PHP_EOL.'IP: '.$_SERVER['REMOTE_ADDR'];
        if (!empty($_SESSION['referer']))
            $string .= PHP_EOL.'SESSION_REFERER: '.$_SESSION['referer'];
        if (isset($_SERVER['HTTP_REFERER']))
            $string .= PHP_EOL.'REFERER: '.$_SERVER['HTTP_REFERER'].PHP_EOL;
        if (isset($_SESSION['admin']))
            $string .= PHP_EOL.'IS_ADMIN: '.PHP_EOL;

        $string .= PHP_EOL.'REQUEST:'.PHP_EOL;
        if (is_array($request) || is_object($request))
        {
            foreach ($request as $rkey => $ritem)
            {
                if (is_array($ritem) || is_object($ritem))
                {

                    $string .= $rkey.' => ('.PHP_EOL;
                    foreach ($ritem as $subrkey => $subritem)
                        $string .= '    '.$subrkey.' => '.strval($subritem).PHP_EOL;

                    $string .= ')'.PHP_EOL;
                }
                else
                {
                    $string .= $rkey.' => '.$ritem.PHP_EOL;
                }
            }
        }
        else
        {
            $string .= $request.PHP_EOL;
        }

        $string .= PHP_EOL.'RESPONSE:'.PHP_EOL;
        if (is_array($response) || is_object($response))
        {
            foreach ($response as $key => $item)
            {
                if (is_array($item) || is_object($item))
                {
                    $string .= $key.' => ('.PHP_EOL;
                    foreach ($item as $subkey => $subitem)
                    {
                        if (is_array($subitem) || is_object($subitem))
                        {
                            $string .= '    '.$subkey.' => ('.PHP_EOL;
                            foreach ($subitem as $subsubkey => $subsubitem)
                                $string .= '        '.$subsubkey.' => '.strval($subsubitem).PHP_EOL;

                            $string .= '    )'.PHP_EOL;
                        }
                        else
                        {
                            $string .= '    '.$subkey.' => '.strval($subitem).PHP_EOL;
                        }
                    }
                    $string .= ')'.PHP_EOL;
                }
                else
                {
                    $string .= $key.' => '.$item.PHP_EOL;
                }
            }
        }
        else
        {
            $string .= $response.PHP_EOL;
        }


        $string .= PHP_EOL.'END'.PHP_EOL;
        $string .= PHP_EOL.'******************************************************'.PHP_EOL;

        file_put_contents($filename, $string, FILE_APPEND);
    }

    public static function setLoggerState($method = null, $url = null, $request = null, $log_filename = 'soap.txt') {
        self::$loggerState['method'] = $method ?: self::$loggerState['method'];
        self::$loggerState['url'] = $url ?: self::$loggerState['url'];
        self::$loggerState['request'] = $request ?: self::$loggerState['request'];
        self::$loggerState['log_filename'] = $log_filename ?: self::$loggerState['log_filename'];
    }

    public static function cleanLoggerState() {
        self::$loggerState['method'] = '';
        self::$loggerState['url'] = '';
        self::$loggerState['request'] = '';
        self::$loggerState['log_filename'] = 'soap.txt';
    }

    public static function flushLogger($result): void
    {
        if(self::$loggerState['method']) {
            (new self())->logging(
                self::$loggerState['method'],
                self::$loggerState['url'],
                self::$loggerState['request'],
                $result,
                self::$loggerState['log_filename']
            );
        } else {
            // Логирование с информацией о трассировке стека
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $caller = isset($trace[2]) ? $trace[2]['class'] . '::' . $trace[2]['function'] : 'UnknownCaller';

            (new self())->logging(
                $caller,
                $_SERVER['REQUEST_URI'] ?? 'NoUrl',
                [],
                $result instanceof Throwable ? [
                    'message' => $result->getMessage(),
                    'file' => $result->getFile(),
                    'line' => $result->getLine()
                ] : $result,
                'uncategorized_errors.log'
            );
        }
    }

    public function logging($method, $url, $request, $response, $log_filename = 'soap.txt')
    {
        $log = 1; // 1 - включить логирование, 0 - выключить
        self::cleanLoggerState();

        if (empty($log))
            return false;

    	$filename = $this->config->root_dir.'logs/'.$log_filename;
        $data_log = '';

        if (file_exists($filename))
        {
            if (date('d', filemtime($filename)) != date('d'))
            {
                $archive_filename = $this->config->root_dir.'logs/archive/'.date('ymd', filemtime($filename)).$log_filename;
                rename($filename, $archive_filename);
                $data_log .= "\xEF\xBB\xBF";
            }
        }

        $data_log .= PHP_EOL.'******************************************************'.PHP_EOL;
        $data_log .= date('d.m.Y H:i:s').PHP_EOL;
        $data_log .= $method.PHP_EOL;
        $data_log .= $url.PHP_EOL;

        if (!empty($_SERVER['REMOTE_ADDR']))
            $data_log .= PHP_EOL.'IP: '.$_SERVER['REMOTE_ADDR'];
        if (!empty($_SESSION['referer']))
            $data_log .= PHP_EOL.'SESSION_REFERER: '.$_SESSION['referer'];
        if (isset($_SERVER['HTTP_REFERER']))
            $data_log .= PHP_EOL.'REFERER: '.$_SERVER['HTTP_REFERER'].PHP_EOL;
        if (isset($_SESSION['admin']))
            $data_log .= PHP_EOL.'IS_ADMIN'.PHP_EOL;

        ob_start();
        var_export($request);
        $request_dump = ob_get_clean();

        ob_start();
        var_export($response);
        $response_dump = ob_get_clean();

        $data_log .= PHP_EOL . 'REQUEST:' . PHP_EOL;
        $data_log .= $request_dump . PHP_EOL;

        $data_log .= PHP_EOL . 'RESPONSE:' . PHP_EOL;
        $data_log .= $response_dump . PHP_EOL;

        $data_log .= PHP_EOL.'END'.PHP_EOL;
        $data_log .= PHP_EOL.'******************************************************'.PHP_EOL;

        file_put_contents($filename, $data_log, FILE_APPEND);
    }

    /**
     * Добавляет лог для методов в БД
     * @param $method
     * @param $data_request
     * @param $data_response
     * @param string $url
     * @param array $data_additional
     * @return void
     */
    public function addLogMethod($method, $data_request, $data_response, string $url = '', array $data_additional = [])
    {
        $request = json_encode($data_request, JSON_UNESCAPED_UNICODE);
        $response = json_encode($data_response, JSON_UNESCAPED_UNICODE);
        $additional = json_encode($data_additional, JSON_UNESCAPED_UNICODE);
        $user_id = $_SESSION['passport_user']['user_uid'] ?? $_SESSION['user_id'] ?? ''; // сначала проверяем залогинен ли по паспорту, затем стандартно

        $query = $this->db->placehold("INSERT INTO s_log_methods SET ?%", compact('method', 'request', 'response', 'additional', 'user_id', 'url'));
        $this->db->query($query);
    }

    /**
     * Получение существующего экземпляра класса Simpla
     * @return Simpla
     */
    public static function getSimpla()
    {
        return self::$simpla ?? new Simpla();
    }
}
