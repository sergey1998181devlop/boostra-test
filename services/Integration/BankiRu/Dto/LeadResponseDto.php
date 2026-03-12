<?php

namespace services\Integration\BankiRu\Dto;

final class LeadResponseDto
{
    public $leadId;
    public $leadToken;
    public $urlType; //1 – Много предложений 2 – Мало предложений

    /**
     * Результат выполнения операции.
     * Возможные значения:
     * success – операция выполнена успешно
     *
     * error – в результате выполнения операции были обнаружены ошибки
     *
     * bad lead ¬¬– полученный лид не удовлетворяет условиям для запуска процесса веерной рассылки
     *
     * dublicate ¬¬– телефон, указанный в лиде является дубликатом, такой лид не готовы принимать
     */
    public $status;

    public function __construct(int $leadId, string $leadToken, ?int $urlType, string $status)
    {
        $this->leadId = $leadId;
        $this->leadToken = $leadToken;
        $this->urlType = $urlType;
        $this->status = $status;
    }
}
