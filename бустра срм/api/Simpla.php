<?php

ini_set("soap.wsdl_cache_enabled", 1);
ini_set("soap.wsdl_cache_ttl", 86400);
ini_set("default_socket_timeout", 300);

/**
 * Основной класс Simpla для доступа к API Simpla
 *
 * @copyright 	2014 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */
define('Simpla', str_replace('api', '', __DIR__));

if (!function_exists('dd')) {
    function dd($array)
    {
        echo "<PRE>";
        print_r($array);
        echo "</PRE>";
    }
}

require_once Simpla . 'vendor/autoload.php';

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
 * @property AutomationFails $automationFails
 * @property Database $db
 * @property DatabaseAccess $dbAccess
 * @property Soap1c $soap
 * @property PaymentExitpools $payment_exitpools
 * @property CDoctor $cdoctor
 * @property Managers $managers
 * @property Orders $orders
 * @property PartnerHref $PartnerHref
 * @property PostBack $post_back
 * @property Transactions $transactions
 * @property Tinkoff $tinkoff
 * @property Users $users
 * @property Articles $articles
 * @property Request $request
 * @property Design $design
 * @property Tickets $tickets
 * @property Telegram $telegram
 * @property NotificationsManagers $notificationsManagers
 * @property NewYearCodes $newYearCodes
 * @property LinkToSafeFlow $linkToSafeFlow
 * @property Scorings $scorings
 * @property Tasks $tasks
 * @property DiscountInsure $discount_insure
 * @property Response $response
 * @property Sms $sms
 * @property Settings $settings
 * @property Smssender $smssender
 * @property Reasons $reasons
 * @property Docs $docs
 * @property Organizations $organizations
 * @property OrganizationsData $organizations_data
 * @property Changelogs $changelogs
 * @property OrdersAutoApprove $orders_auto_approve
 * @property Scorista $scorista
 * @property Contactpersons $contactpersons
 * @property Documents $documents
 * @property Comments $comments
 * @property FSSPApi $fssp_api
 * @property Insurances $insurances
 * @property CustomMetric $custom_metric
 * @property Blacklist $blacklist
 * @property Fms $fms
 * @property Promocodes $promocodes
 * @property StopListWebId $stop_list_web_id
 * @property CreditDoctor $credit_doctor
 * @property StarOracle $star_oracle
 * @property SafeDeal $safe_deal
 * @property Operations $operations
 * @property Best2pay $best2pay
 * @property Issuance $issuance
 * @property Queue $queue
 * @property PushToken $push_token
 * @property Multipolis $multipolis
 * @property Missings $missings
 * @property VoxCalls $voxCalls
 * @property VoxUsers $voxUsers
 * @property VoxQueues $voxQueues
 * @property VoxUserDepartments $voxUserDepartments
 * @property TVMedical $tv_medical
 * @property Receipts $receipts
 * @property Leadgid $leadgid
 * @property CreditRating $credit_rating
 * @property Filestorage $filestorage
 * @property ReplaceDocument $replaceDocument
 * @property Contracts $contracts
 * @property Finroznica $finroznica
 * @property Voximplant $voximplant
 * @property ServiceReturnRequests $serviceReturnRequests
 * @property UserBankRequisites $userBankRequisites
 * @property DBrainApi $dbrain_api
 * @property DBrainAxi $dbrainAxi
 * @property DownloadCallList $callList
 * @property PdnCalculation $pdnCalculation
 * @property Config $config
 * @property UserPhones $phones
 * @property Visitors $visitors
 * @property UserEmails $emails
 * @property LeadgidScorista $leadgidScorista
 * @property ApproveAmountSettings $approve_amount_settings
 * @property Image $image
 * @property JuicescoreCriteria $juicescoreCriteria
 * @property OrderData $order_data
 * @property UserData $user_data
 * @property SmsShortLink $smsShortLink
 * @property LeadPrice $leadPrice
 * @property RosstatRegion $rosstatRegion
 * @property Infosphere $infosphere
 * @property FinKartaAPI $finkarta_api
 * @property Uprid $uprid
 * @property Terrorist $terrorist
 * @property Faq $faq
 * @property Axilink $axilink
 * @property Pdn $pdn
 * @property BlockedAdvSms $blocked_adv_sms
 * @property CompanyOrders $company_orders
 * @property Infosphere $Infosphere
 * @property VkApi $vk_api
 * @property Axi $axi
 * @property Report $report
 * @property Efrsb $efrsb
 * @property Pyton_smp $pyton_smp
 * @property Pyton_nbki $pyton_nbki
 * @property Fssp $fssp
 * @property VkMessageSettings $vk_message_settings
 * @property SspNbkiRequestLog $ssp_nbki_request_log
 * @property ShortFlow $short_flow
 * @property Sites $sites
 * @property SprVersions $spr_versions
 * @property Cross_orders $cross_orders
 * @property Centrifugo $centrifugo
 * @property AuthCodes $authcodes
 * @property AxiLtv $axi_ltv
 * @property Bonondo $bonondo
 * @property BonondoApi $bonondoApi
 * @property S3ApiClient $s3_api_client
 * @property CreditHistory $credit_history
 * @property SoglasieBKIHashCode $soglasie_bki_hash_code
 * @property Autoconfirm $autoconfirm
 * @property ScorApprove $scor_approve
 * @property B2pBankList $b2p_bank_list
 * @property B2pSbpIssuanceLog $b2p_sbp_issuance_log
 * @property HyperC $hyper_c
 * @property SbpAccount $sbpAccount
 * @property Ping3Data $ping3_data
 * @property Import1c $import1c
 * @property OrderOrgSwitch $order_org_switch
 * @property NotificationCenter $notificationCenter
 * @property SoyaplacePostback $soyaplace_postback
 * @property NeomaniPostback $neomani_postback
 * @property Installments $installments
 * @property Helpers $helpers
 * @property Caches $caches
 * @property Rcl $rcl
 * @property CbRequests $cbRequests
 * @property MindboxApi $mindboxApi
 * @property VirtualCard $virtualCard
 */

class Simpla {

    // Свойства - Классы API
    private $classes = array(
        'automationFails' => 'AutomationFails',
        'db' => 'Database',
        'dbAccess' => 'DatabaseAccess',
        'soap' => 'Soap1c',
        'payment_exitpools' => 'PaymentExitpools',
        'cdoctor' => 'CDoctor',
        'post_back' => 'PostBack',
        'discount_insure' => 'DiscountInsure',
        'orders_auto_approve' => 'OrdersAutoApprove',
        'fssp_api' => 'FSSPApi',
        'custom_metric' => 'CustomMetric',
        'stop_list_web_id' => 'StopListWebId',
        'credit_doctor' => 'CreditDoctor',
        'star_oracle' => 'StarOracle',
        'safe_deal' => 'SafeDeal',
        'operations' => 'Operations',
        'best2pay' => 'Best2pay',
        'issuance' => 'Issuance',
        'queue' => 'Queue',
        'push_token' => 'PushToken',
        'tv_medical' => 'TVMedical',
        'docs' => 'Docs',
        'receipts' => 'Receipts',
        'credit_rating' => 'CreditRating',
        'finroznica' => 'Finroznica',
        'dbrain_api' => 'DBrainApi',
        'callList' => 'DownloadCallList',
        'phones' => 'UserPhones',
        'megafon' => 'Megafon',
        'emails' => 'UserEmails',
        'order_data' => 'OrderData',
        'user_data' => 'UserData',
        'finkarta_api' => 'FinKartaAPI',
        'voximplant' => 'Voximplant',
        'faq' => 'Faq',
        'company_orders' => 'CompanyOrders',
        'blocked_adv_sms' => 'BlockedAdvSms',
        'vk_api' => 'VkApi',
        'axi' => 'Axi',
        'approve_amount_settings' => 'ApproveAmountSettings',
        'vk_message_settings' => 'VkMessageSettings',
        'ssp_nbki_request_log' => 'SspNbkiRequestLog',
        'short_flow' => 'ShortFlow',
        'sites' => 'Sites',
        'spr_versions' => 'SprVersions',
        'cross_orders' => 'Cross_orders',
        'centrifugo' => 'Centrifugo',
        'authcodes' => 'AuthCodes',
        'axi_ltv' => 'AxiLtv',
        'bonondo' => Bonondo::class,
        'sites' => Sites::class,
        'bonondoApi' => BonondoApi::class,
        's3_api_client' => 'S3ApiClient',
        'credit_history' => 'CreditHistory',
        'soglasie_bki_hash_code' => SoglasieBKIHashCode::class,
        'b2p_bank_list' => 'B2pBankList',
        'b2p_sbp' => 'B2pSbp',
        'b2p_sbp_issuance_log' => 'B2pSbpIssuanceLog',
        'scor_approve' => 'ScorApprove',
        'hyper_c' => 'HyperC',
        'organizations_data' => OrganizationsData::class,
        'vsev_debt_task' => 'VsevDebtTask',
        'ping3_data' => 'Ping3Data',
        'order_org_switch' => 'OrderOrgSwitch',
        'serviceReturnRequests' => ServiceReturnRequests::class,
        'userBankRequisites' => UserBankRequisites::class,
        'notificationCenter' => NotificationCenter::class,
        'soyaplace_postback' => SoyaplacePostback::class,
        'neomani_postback' => NeomaniPostback::class,
        'installments' => 'Installments',
        'voxCalls' => 'VoxCalls',
        'voxUsers' => 'VoxUsers',
        'voxQueues' => 'VoxQueues',
        'voxUserDepartments' => 'VoxUserDepartments',
        'caches' => 'Caches',
        'cbRequests' => 'CbRequests',
        'MindboxApi' => 'mindboxApi',
    );
    // Созданные объекты
    private static $objects = array();
    public $is_developer = 0;

    /**
     * Конструктор оставим пустым, но определим его на случай обращения parent::__construct() в классах API
     */
    public function __construct() {
        if (isset($_GET['set_dev']) && $_GET['set_dev'] == 'bs29031981a') {
            setcookie('developer', 'bs29031981a', time() + 86400, '/');
            header('Location: /');
            exit;
        }

        if (isset($_GET['set_dev']) && $_GET['set_dev'] == 'unset') {
            setcookie('developer', NULL, time() -1, '/');
            header('Location: /');
            exit;
        }

        if (!empty($_COOKIE['developer']) && $_COOKIE['developer'] == 'bs29031981a') {
//            setcookie('developer', 1, time()+300*86400, '/');
            $this->is_developer = 1;
        }

        if ($this->is_developer) {
            error_reporting(-1);
            ini_set('display_errors', 'On');
        }
    }

    /**
     * Магический метод, создает нужный объект API
     */
    public function __get($name) {
        // Если такой объект уже существует, возвращаем его
        if (isset(self::$objects[$name])) {
            return(self::$objects[$name]);
        }

        if (file_exists(dirname(__FILE__) . '/' . ucfirst($name) . '.php')) {
            $class = ucfirst($name);
            $filename = dirname(__FILE__) . '/' . $class . '.php';
        } elseif (array_key_exists($name, $this->classes)) {
            $class = $this->classes[$name];
            $filename = dirname(__FILE__) . '/' . $class . '.php';
        } elseif (file_exists(dirname(__FILE__) . '/../scorings/' . ucfirst($name) . '.php')) {
            $class = ucfirst($name);
            $filename = dirname(__FILE__) . '/../scorings/' . ucfirst($name) . '.php';
        } else {
            return null;
        }

        // Подключаем его
        include_once($filename);

        // Сохраняем для будущих обращений к нему
        self::$objects[$name] = new $class();

        // Возвращаем созданный объект
        return self::$objects[$name];
    }

    public function logging($method, $url, $request, $response, $log_filename = 'soap.txt')
    {
        $log = 1; // 1 - включить логирование, 0 - выключить

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
     * Генератор UIDGEN
     * @return string
     */
    public static function UIDGEN() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
                       mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
                       mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
                       mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
                       mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
                       mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function getManagerId() {
        return $_SESSION['manager_id'] ?? null;
    }
}
