<?php
namespace App\Repositories;

class OracleReturnLogRepository extends ReturnLogRepository
{
    public function __construct()
    {
        $cfg = config('services.extra_service');
        parent::__construct(
            $cfg['return_tables']['star_oracle'],
            $cfg['return_status']
        );
    }
}
