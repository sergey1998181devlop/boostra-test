<?php

namespace App\Modules\VoxCallsArchive\Application\DTO;

/**
 * Class VoxCallDTO
 * Data Transfer Object для записи звонка Voximplant
 */
class VoxCallDTO
{
    /** @var float|null Стоимость звонка */
    public $cost;

    /** @var string|null Код результата звонка */
    public $callResultCode;

    /** @var string|null Дата и время начала звонка */
    public $datetimeStart;

    /** @var int|null Длительность звонка в секундах */
    public $duration;

    /** @var int|null ID звонка в Voximplant */
    public $voxCallId;

    /** @var bool|null Входящий звонок */
    public $isIncoming;

    /** @var string|null Телефон А (звонящий) */
    public $phoneA;

    /** @var string|null Телефон Б (принимающий) */
    public $phoneB;

    /** @var int|null ID сценария */
    public $scenarioId;

    /** @var string|null JSON-строка с тегами */
    public $tags;

    /** @var string|null Дата создания записи */
    public $created;

    /** @var int|null ID пользователя (клиента) в нашей системе */
    public $userId;

    /** @var int|null ID очереди */
    public $queueId;

    /** @var int|null ID пользователя Voximplant (оператора) */
    public $voxUserId;

    /** @var string|null URL записи разговора */
    public $recordUrl;

    /** @var int|null Оценка качества звонка (1-5) */
    public $assessment;

    /**
     * Создать DTO из stdClass (легаси формат)
     *
     * @param \stdClass $call
     * @return self
     */
    public static function fromLegacy(\stdClass $call): self
    {
        $dto = new self();

        $dto->cost = $call->call_cost ?? null;
        $dto->callResultCode = $call->call_result_code ?? null;
        $dto->datetimeStart = $call->datetime_start ?? null;
        $dto->duration = isset($call->duration) ? (int)$call->duration : null;
        $dto->voxCallId = isset($call->id) ? (int)$call->id : null;
        $dto->isIncoming = isset($call->is_incoming) ? (bool)$call->is_incoming : null;
        $dto->phoneA = $call->phone_a ?? null;
        $dto->phoneB = $call->phone_b ?? null;
        $dto->scenarioId = isset($call->scenario_id) ? (int)$call->scenario_id : null;

        // Теги могут быть массивом или JSON строкой
        if (isset($call->tags)) {
            $dto->tags = is_array($call->tags) ? json_encode($call->tags) : $call->tags;
        }

        $dto->created = $call->created ?? date('Y-m-d H:i:s');
        $dto->userId = $call->user_id_internal ?? (isset($call->user_id) && !is_string($call->user_id) ? (int)$call->user_id : null);
        $dto->queueId = isset($call->queue_id) ? (int)$call->queue_id : null;
        $dto->voxUserId = isset($call->user_id) && is_numeric($call->user_id) ? (int)$call->user_id : null;
        $dto->recordUrl = $call->record_url ?? null;
        $dto->assessment = isset($call->assessment) && $call->assessment !== '' ? (int)$call->assessment : null;

        return $dto;
    }

    /**
     * Создать DTO из массива
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();

        $dto->cost = $data['cost'] ?? null;
        $dto->callResultCode = $data['call_result_code'] ?? null;
        $dto->datetimeStart = $data['datetime_start'] ?? null;
        $dto->duration = isset($data['duration']) ? (int)$data['duration'] : null;
        $dto->voxCallId = isset($data['vox_call_id']) ? (int)$data['vox_call_id'] : null;
        $dto->isIncoming = isset($data['is_incoming']) ? (bool)$data['is_incoming'] : null;
        $dto->phoneA = $data['phone_a'] ?? null;
        $dto->phoneB = $data['phone_b'] ?? null;
        $dto->scenarioId = isset($data['scenario_id']) ? (int)$data['scenario_id'] : null;
        $dto->tags = $data['tags'] ?? null;
        $dto->created = $data['created'] ?? date('Y-m-d H:i:s');
        $dto->userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
        $dto->queueId = isset($data['queue_id']) ? (int)$data['queue_id'] : null;
        $dto->voxUserId = isset($data['vox_user_id']) ? (int)$data['vox_user_id'] : null;
        $dto->recordUrl = $data['record_url'] ?? null;
        $dto->assessment = isset($data['assessment']) && $data['assessment'] !== '' ? (int)$data['assessment'] : null;

        return $dto;
    }

    /**
     * Преобразовать в массив для вставки в БД
     *
     * @return array
     */
    public function toDbArray(): array
    {
        return [
            'cost' => $this->cost,
            'call_result_code' => $this->callResultCode,
            'datetime_start' => $this->datetimeStart,
            'duration' => $this->duration,
            'vox_call_id' => $this->voxCallId,
            'is_incoming' => $this->isIncoming !== null ? ($this->isIncoming ? 1 : 0) : null,
            'phone_a' => $this->phoneA,
            'phone_b' => $this->phoneB,
            'scenario_id' => $this->scenarioId,
            'tags' => $this->tags,
            'created' => $this->created,
            'user_id' => $this->userId,
            'queue_id' => $this->queueId,
            'vox_user_id' => $this->voxUserId,
            'record_url' => $this->recordUrl,
            'assessment' => $this->assessment,
        ];
    }
}
