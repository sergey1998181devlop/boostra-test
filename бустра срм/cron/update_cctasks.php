<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__) . '/../api/Simpla.php';

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!function_exists('config')) {
    require_once dirname(__DIR__) . '/app/Core/Helpers/BaseHelper.php';
}
\App\Core\Application\Application::getInstance();

use App\Service\UserBalanceImportService;
use App\Service\VoximplantLogger;

class UpdateCCTasksCron extends Simpla
{
    /** @var UserBalanceImportService */
    private $importService;

    /** @var string */
    private $company = 'Boostra';

    public function __construct()
    {
        parent::__construct();

        $cronStartTime = microtime(true);
        $cronStartDate = date('Y-m-d H:i:s');

        if (function_exists('logger')) {
            logger('user_balance_import')->info('update_cctasks cron started', [
                'start_time' => $cronStartDate,
                'timestamp' => $cronStartTime,
            ]);
        }

        $logger = new VoximplantLogger();
        $this->importService = new UserBalanceImportService(
            $this->users,
            $this->soap,
            $this->import1c,
            $this->organizations,
            $logger,
            $this->db
        );

        $options = getopt('c:', ['company:']);
        $company = $options['c'] ?? $options['company'] ?? null;

        if ($company) {
            $this->company = (string) $company;
        }

        $sitesList = $this->sites->getActiveSites();

        // Сброс балансов по дате — один раз за ночь (только для первой компании в расписании крона).
        // Остальные компании и все площадки только дополняют/обновляют данные, не затирая предыдущие.
        $dateToday = date('Y-m-d');
        if ($this->company === 'Boostra') {
            $this->importService->resetBalancesForDate($dateToday);
        }

        foreach ($sitesList as $site) {
            try {
                $this->importService->importBalancesForSite($site->site_id, $this->company);
            } catch (\Throwable $e) {
                if (function_exists('logger')) {
                    logger('user_balance_import')->error('UpdateCCTasksCron site failed', [
                        'site' => $site->site_id ?? null,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        $cronEndTime = microtime(true);
        $cronEndDate = date('Y-m-d H:i:s');
        $cronDuration = round($cronEndTime - $cronStartTime, 2);

        if (function_exists('logger')) {
            logger('user_balance_import')->info('update_cctasks cron finished', [
                'start_time' => $cronStartDate,
                'end_time' => $cronEndDate,
                'duration_seconds' => $cronDuration,
                'sites_count' => count($sitesList),
                'company' => $this->company,
            ]);
        }

        echo 'Обновлено';
    }
}

new UpdateCCTasksCron();
