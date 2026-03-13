<?php

namespace App\Service;

use App\Repositories\CbrLinkClickRepository;
use App\Repositories\UserRepository;

class CbrLinkClickReportService
{
    private CbrLinkClickRepository $clickRepo;
    private UserRepository $userRepo;

    public function __construct(CbrLinkClickRepository $clickRepo, UserRepository $userRepo)
    {
        $this->clickRepo = $clickRepo;
        $this->userRepo = $userRepo;
    }

    public function getReport($dateFrom, $dateTo, $page, $perPage)
    {
        $clicks = $this->clickRepo->getClicks($dateFrom, $dateTo, $page, $perPage);

        foreach ($clicks as &$click) {
            $user = null;
            $fio = '';
            $phone = '';
            $isDebtor = false;
            $debtDays = null;

            if ($click->user_id) {
                $user = $this->userRepo->getById($click->user_id);
            }

            if (!$user && $click->ip) {
                $user = $this->userRepo->getByIp($click->ip);
            }

            if ($user) {
                $fio = trim("{$user->lastname} {$user->firstname} {$user->patronymic}");
                $phone = $user->phone_mobile;
                $loanHistory = json_decode($user->loan_history, true);
                $click->user_id = $user->id;
                if (is_array($loanHistory)) {
                    $now = new \DateTime();
                    foreach ($loanHistory as $loan) {
                        if (empty($loan['close_date']) && !empty($loan['plan_close_date'])) {
                            $planClose = new \DateTime($loan['plan_close_date']);
                            if ($planClose < $now) {
                                $isDebtor = true;
                                $debtDays = $planClose->diff($now)->days;
                                break;
                            }
                        }
                    }
                }
            }

            $click->phone = $phone;
            $click->fio = $fio;
            $click->is_debtor = $isDebtor;
            $click->debt_days = $debtDays;
        }

        return $clicks;
    }

    public function count($dateFrom, $dateTo)
    {
        return $this->clickRepo->count($dateFrom, $dateTo);
    }
}