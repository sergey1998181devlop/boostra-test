<?php

namespace App\Dto;

use App\Models\User;
use App\Models\UserEmail;
use JsonSerializable;

final class UsedeskTicketAnalysisDto implements JsonSerializable
{
    private array $data;
    private array $ticket;

    public function __construct(array $data, array $ticket)
    {
        $this->data = $data;
        $this->ticket = $ticket;
    }

    public function jsonSerialize(): array
    {
        return [
            'ticket_id' => $this->data['ticket_id'],
            'user_id' => $this->getUserId(),
            'analysis' => $this->getAnalysis(),
        ];
    }

    private function getUserId()
    {
        $userId = (new User())->get(['id'], ['email' => $this->ticket['email']])->getData()['id'] ?? null;

        if (empty($userId)) {
            $userId = (new UserEmail())->get(['user_id'], ['email' => $this->ticket['email']])->getData()['user_id'] ?? null;
        }

        return $userId;
    }

    private function getAnalysis(): string
    {
        return json_encode([
            'completeness_response' => [
                'assessment' => $this->data['Полнота ответа']['оценка'] ?? '',
                'recommendations' => $this->data['Полнота ответа']['рекомендации'] ?? ''
            ],
            'problem_solving_efficiency' => [
                'assessment' => $this->data['Эффективность решения проблемы']['оценка'] ?? '',
                'solution' => (bool)$this->data['Эффективность решения проблемы']['решение'] ?? '',
                'recommendations' => $this->data['Эффективность решения проблемы']['рекомендации'] ?? ''
            ],
            'answer_politeness' => [
                'assessment' => $this->data['Вежливость ответа']['оценка'] ?? '',
                'recommendations' => $this->data['Вежливость ответа']['рекомендации'] ?? ''
            ],
            'answer_literacy' => [
                'assessment' => $this->data['Грамотность ответа']['оценка'] ?? '',
                'recommendations' => $this->data['Грамотность ответа']['рекомендации'] ?? ''
            ],
            'recommendations' => $this->data['общие_рекомендации'] ?? '',
            'total_assessment' => $this->getTotalAssessment(),
        ], JSON_UNESCAPED_UNICODE);
    }

    private function getTotalAssessment(): float
    {
        $sum = 0;
        $count = 0;

        $mainKeys = [
            'Полнота ответа',
            'Эффективность решения проблемы',
            'Вежливость ответа',
            'Грамотность ответа'
        ];

        foreach ($mainKeys as $key) {
            if (isset($this->data[$key]['оценка']) && is_numeric($this->data[$key]['оценка'])) {
                $sum += $this->data[$key]['оценка'];
                $count++;
            }
        }

        return $count > 0 ? round($sum / $count, 1) : 0;
    }
}
