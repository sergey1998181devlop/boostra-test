<?php
namespace App\Repositories;

class DoctorReturnLogRepository extends ReturnLogRepository
{
    public function __construct()
    {
        $cfg = config('services.extra_service');
        parent::__construct(
            $cfg['return_tables']['financial_doctor'],
            $cfg['return_status']
        );
    }
}
