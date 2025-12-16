<?php

if(strpos(strtolower(php_sapi_name()), 'cli') === false) {
    $r_headers = array_filter((array)getallheaders(),
                            function($item) {
                                return strtolower($item) == 'xmlhttprequest';
                            });
    if(count($r_headers)) {
        set_exception_handler(function(Throwable $exception) {
            Simpla::flushLogger($exception);
            echo json_encode([
                'soap_fault' => true,
                'error' => 'Сервер перегружен!'
            ]);
            exit;
        });
    }
}
