<?php

namespace App\Modules\SbpAccount\Services;

use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\ChangelogAdapter;
use App\Modules\RecurrentsCenter\Services\RecurrentCenterService;

class SbpAccountService
{
    public const CHANGE_AUTODEBIT_PARAM_API_URL = 'api/sbp/change_autodebit_param';
    public const DELETE_API_URL = 'api/sbp/delete';
    private const CHANGELOG_TYPE = 'sbp_account_autodebit_change';

    private RecurrentCenterService $rcService;
    private \SbpAccount $sbpAccount;
    private \Best2Pay $best2Pay;
    private ChangelogAdapter $changelogAdapter;

    public function __construct(
        RecurrentCenterService $rcService,
        \SbpAccount $sbpAccount,
        \Best2Pay $best2Pay,
        ChangelogAdapter $changelogAdapter
    ) {
        $this->sbpAccount = $sbpAccount;
        $this->rcService = $rcService;
        $this->best2Pay = $best2Pay;
        $this->changelogAdapter = $changelogAdapter;
    }

    public function changeAutodebitParam(array $sbpAccountAutodebitParams, int $userId, int $orderId, int $managerId, string $userUid): array
    {
        $result = [];
        try {
            $url = $this->rcService->getUrl() . static::CHANGE_AUTODEBIT_PARAM_API_URL;
            $updatedData = [];
            $oldData = [];
            foreach ($sbpAccountAutodebitParams as $sbpAccountId => $value) {
                $sbpAccount = $this->sbpAccount->find($sbpAccountId);

                if (!$sbpAccount) {
                    continue;
                }

                $oldData[$sbpAccount->id] = $sbpAccount->autodebit;
                $updatedData[$sbpAccount->id] = $value;

                if ($oldData[$sbpAccount->id] === $updatedData[$sbpAccount->id]) {
                    $result[] = $sbpAccount;
                    continue;
                }

                $this->sbpAccount->updateSbpAccount(
                    $sbpAccount->id,
                    [
                        'autodebit' => $value,
                        'autodebit_changed_at' => date('Y-m-d H:i:s'),
                    ]
                );

                $this->best2Pay->add_sbp_log([
                    'card_id' => $sbpAccount->id,
                    'action' => \Orders::CARD_ACTIONS[$value ? 'AUTODEBIT_SBP_ON' : 'AUTODEBIT_SBP_OFF'],
                    'date' => date('Y-m-d H:i:s')
                ]);

                $this->rcService->sendRequest([
                    'token' => $sbpAccount->token,
                    'autodebit' => $value,
                    'client_uid' => $userUid,
                ], $url);

                $result[] = $sbpAccount;
            }

            if ($oldData && $updatedData && $oldData !== $updatedData) {
                $this->changelogAdapter->logAutodebitParamChange(
                    $orderId,
                    $userId,
                    static::CHANGELOG_TYPE,
                    $managerId,
                    serialize($oldData),
                    serialize($updatedData)
                );
            }
        } catch (\Exception $e) {
            logger('autodebit_change')->error(
                __METHOD__ . PHP_EOL
                . $e->getFile() . PHP_EOL
                . $e->getLine() . PHP_EOL
                . $e->getMessage());

        }

        return $result;
    }
}