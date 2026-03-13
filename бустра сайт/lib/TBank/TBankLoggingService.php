<?php

require_once 'Simpla.php';

class TBankLoggingService extends Simpla
{
    public function log(string $method, array $request, array $response, string $url = '')
    {
        $method = $url ? sprintf('%s.%s', $method, $url) : $method;

        $this->logging(__METHOD__, $method, $request, $response, date('d-m-Y').'-t-bank-error.txt');
    }

    public function error(string $method, array $request, $response, string $url = '')
    {
        $this->log($method, $request, ['error' => $response], $url);
    }

    public function warning(string $method, array $request, $response, string $url = '')
    {
        $this->log($method, $request, ['warning' => $response], $url);
    }
}