<?php

namespace api\interfaces;

interface AdditionalPaysDbReportInterface
{
    /**
     * Получает данные по оплатам
     * @param array $data_filters
     * @return mixed
     */
    public function getPays(array $data_filters = []);
}
