<?php

/**
 * Interface ReportInterface
 * Интерфейс для отчётов
 */
interface ReportInterface
{
    public function getResults();

    public static function getDefaultArray();

    public function getFilterData();
}
