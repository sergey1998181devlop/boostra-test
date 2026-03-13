<?php

namespace App\Modules\Card\Services;

use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\ChangelogAdapter;
use App\Modules\RecurrentsCenter\Services\RecurrentCenterService;
use Best2pay;

class CardService
{
    public const CHANGE_AUTODEBIT_PARAM_API_URL = 'api/cards/change_autodebit_param';
    const DELETE_API_URL = 'api/cards/delete';
    const CHANGELOG_TYPE = 'card_autodebit_change';

    private RecurrentCenterService $rcService;
    private Best2Pay $best2Pay;
    private ChangelogAdapter $changelogAdapter;

    public function __construct(
        RecurrentCenterService $rcService,
        \Best2Pay $best2Pay,
        ChangelogAdapter $changelogAdapter
    ) {
        $this->rcService = $rcService;
        $this->best2Pay = $best2Pay;
        $this->changelogAdapter = $changelogAdapter;
    }

    public function changeAutodebitParam(array $cardAutodebitParams, int $userId, int $orderId, int $managerId, string $userUid): array
    {
        try {
            logger('rc')->info(1);
            $url = $this->rcService->getUrl() . static::CHANGE_AUTODEBIT_PARAM_API_URL;
            $updatedData = [];
            $oldData = [];
            $cards = [];

            foreach ($this->best2Pay->get_cards(['user_id' => $userId]) as $card) {
                $cards[$card->id] = $card;
            }

            foreach ($cardAutodebitParams as $cardId => $value) {
                $oldData[$cards[$cardId]->pan] = $cards[$cardId]->autodebit;
                $updatedData[$cards[$cardId]->pan] = $value;
                $cards[$cardId]->autodebit = $value;

                if ($oldData === $updatedData) {
                    continue;
                }

                $this->best2Pay->update_card(
                    $cardId,
                    [
                        'autodebit' => $value,
                        'autodebit_changed_at' => date('Y-m-d H:i:s'),
                    ]
                );

                $this->best2Pay->add_sbp_log([
                    'card_id' => $cardId,
                    'action' => \Orders::CARD_ACTIONS[$value ? 'AUTODEBIT_CARD_ON' : 'AUTODEBIT_CARD_OFF'],
                    'date' => date('Y-m-d H:i:s')
                ]);

                $this->rcService->sendRequest([
                    'pan' => $cards[$cardId]->pan,
                    'token' => $cards[$cardId]->token,
                    'autodebit' => $value,
                    'client_uid' => $userUid
                ], $url);
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

            return $cards;
        } catch (\Exception $e) {
            logger('autodebit_change')->error(
                __METHOD__ . PHP_EOL
                . $e->getFile() . PHP_EOL
                . $e->getLine() . PHP_EOL
                . $e->getMessage());
        }
    }
}