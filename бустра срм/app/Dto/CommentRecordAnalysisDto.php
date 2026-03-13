<?php

namespace App\Dto;

use App\Enums\CommentBlocks;
use JsonSerializable;

final class CommentRecordAnalysisDto implements JsonSerializable
{
    private array $data;
    private array $comment;

    public function __construct(array $data, array $comment)
    {
        $this->data = $data;
        $this->comment = $comment;
    }

    /**
     * @throws \JsonException
     */
    public function jsonSerialize(): array
    {
        $commentText = json_decode($this->comment['text'], true, 512, JSON_THROW_ON_ERROR);

        if ($this->comment['block'] === CommentBlocks::INCOMING_CALL) {
            $analysis = $this->getIncomingCallAnalysis();
        } elseif ($this->comment['block'] === CommentBlocks::OUTGOING_CALL) {

            if (isset($commentText['provider']) && $commentText['provider'] === 'mango') {
                $analysis = $this->getMangoOutgoingCallAnalysis();
            } else {
                $analysis = $this->getOutgoingCallAnalysis();
            }
        } else {
            $analysis = null;
        }

        return [
            'comment_id' => $this->data['comment_id'],
            'user_id' => $this->data['user_id'],
            'analysis' => $analysis,
        ];
    }

    private function getIncomingCallAnalysis(): string
    {
        return json_encode([
            'politeness' => $this->data['вежливость'] ?? '',
            'active_listening_score' => $this->data['активное_слушание'] ?? '',
            'resume' => $this->data['резюмирование'] ?? '',
            'correct_solution' => $this->data['правильность_решения'] ?? '',
            'client_satisfaction' => $this->data['удовлетворенность_клиента'] ?? '',
            'client_conflict' => $this->data['клиент_конфликтоген'] ?? '',
            'manager_conflict' => $this->data['менеджер_конфликтоген'] ?? '',
            'explanation' => $this->data['объяснение'] ?? '',
            'conversation_topic' => $this->data['Тема разговора'] ?? $this->data['тема_обращения'] ?? '',
            'recommendations' => $this->data['Рекомендации'] ?? $this->data['рекомендации'] ?? '',
            'transcribe' => $this->data['transcribe'] ?? '',
            // Дополнительные данные анализа звонков по доп. услугам
            'sale' => $this->data['Продажа'] ?? '',
            'use_name_additional_software' => [
                'assessment' => $this->data['оценки']['Использовал название дополнительного ПО']['оценка'] ?? '',
                'justification' => $this->data['оценки']['Использовал название дополнительного ПО']['объяснение'] ?? ''
            ],
            'use_script' => [
                'assessment' => $this->data['оценки']['Использование скрипта']['оценка'] ?? '',
                'justification' => $this->data['оценки']['Использование скрипта']['объяснение'] ?? ''
            ],
            'final_sale' => [
                'improvements' => $this->data['Финал продажа']['Что улучшить'] ?? '',
                'sale_recommendations' => $this->data['Финал продажа']['Рекомендации продажа'] ?? ''
            ],
            'total_assessment' => $this->getTotalAssessment(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    private function getOutgoingCallAnalysis(): string
    {
        return json_encode([
            'identification_procedures' => [
                'assessment' => $this->data['Соблюдение процедур идентификации и верификации']['оценка'] ?? '',
                'justification' => $this->data['Соблюдение процедур идентификации и верификации']['обоснование'] ?? ''
            ],
            'call_objective_and_motivation' => [
                'assessment' => $this->data['Доведение цели звонка и мотивации']['оценка'] ?? '',
                'justification' => $this->data['Доведение цели звонка и мотивации']['обоснование'] ?? ''
            ],
            'compliance_230FZ' => [
                'assessment' => $this->data['Соответствие 230-ФЗ']['оценка'] ?? '',
                'justification' => $this->data['Соответствие 230-ФЗ']['обоснование'] ?? ''
            ],
            'argumentation_and_outcomes' => [
                'assessment' => $this->data['Аргументация и итоги']['оценка'] ?? '',
                'justification' => $this->data['Аргументация и итоги']['обоснование'] ?? ''
            ],
            'recommendations' => $this->data['Рекомендации по улучшению работы'] ?? '',
            'total_assessment' => $this->getTotalAssessment(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    private function getMangoOutgoingCallAnalysis(): string
    {
        return json_encode([
            'greeting' => $this->data['приветствие'] ?? '',
            'position_and_company' => $this->data['должность_и_компания'] ?? '',
            'notified_about_recording' => $this->data['уведомил_о_записи'] ?? '',
            'client_identification' => $this->data['идентификация_клиента'] ?? '',
            'no_third_party_discussion' => $this->data['не_говорил_с_3_м_лицом'] ?? '',
            'debt_amount' => $this->data['сумма_долга'] ?? '',
            'repayment_term' => $this->data['срок_погашения'] ?? '',
            'was_a_dialogue' => $this->data['был_диалогом'] ?? '',
            'call_at_permitted_time' => $this->data['звонок_в_разрешенное_время'] ?? '',
            'no_conflicts' => $this->data['не_было_конфликтов'] ?? '',
            'official_address' => $this->data['официальное_обращение'] ?? '',
            'strong_arguments' => $this->data['сильные_аргументы'] ?? '',
            'final_summary' => $this->data['итоговое_резюме'] ?? '',
            'logical_sequence' => $this->data['логичная_последовательность'] ?? '',
            'total_assessment' => $this->incrementInts($this->data),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    function incrementInts($data) {
        $sum = 0;
        $count = 0;
        foreach ($data as $k => $v) {
            if (in_array($k, ['comment_id', 'user_id'])) {
                continue;
            }
            if (is_int($v)) {
                $sum += $v;
                $count++;
            }
        }

        return $count > 0 ? round(($sum / $count) * 100, 1) : 0;
    }

    private function getTotalAssessment(): float
    {
        $sum = 0;
        $count = 0;

        if ($this->comment['block'] === CommentBlocks::INCOMING_CALL && !isset($this->data['оценки'])) {
            $mainKeys = [
                'вежливость',
                'активное_слушание',
                'резюмирование',
                'правильность_решения',
                'удовлетворенность_клиента',
                'клиент_конфликтоген',
                'менеджер_конфликтоген',
                'объяснение',
            ];

            foreach ($mainKeys as $key) {
                if (isset($this->data[$key]) && is_numeric($this->data[$key])) {
                    $sum += $this->data[$key];
                    $count++;
                }
            }

            return $count > 0 ? round(($sum / $count) * 100, 1) : 0;
        } elseif ($this->comment['block'] === CommentBlocks::OUTGOING_CALL) {
            $mainKeys = [
                'Соблюдение процедур идентификации и верификации',
                'Доведение цели звонка и мотивации',
                'Соответствие 230-ФЗ',
                'Аргументация и итоги'
            ];
        } else {
            $mainKeys = [];
        }

        foreach ($mainKeys as $key) {
            if (isset($this->data[$key]['оценка']) && is_numeric($this->data[$key]['оценка'])) {
                $sum += $this->data[$key]['оценка'];
                $count++;
            }
        }

        if (isset($this->data['оценки']) && is_array($this->data['оценки'])) {
            foreach ($this->data['оценки'] as $assessment) {
                if (isset($assessment['оценка']) && is_numeric($assessment['оценка'])) {
                    $sum += $assessment['оценка'];
                    $count++;
                }
            }
        }

        return $count > 0 ? round($sum / $count, 1) : 0;
    }
}
