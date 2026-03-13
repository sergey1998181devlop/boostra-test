<?php

namespace App\Dto;

use App\Core\Application\Request\Request;

final class FromtechIncomingCallDto
{
    public string $msisdn;
    public ?string $call_log;
    public ?string $dialog_log;
    public ?string $call_transcript;
    public ?int $duration;
    public ?int $bot_duration;
    public ?string $client;
    public ?string $call_record;
    public bool $switch_to_operator;
    public array $methods_list;
    public ?int $manager_id;

    public static function fromRequest(Request $request): self
    {
        $dto = new self();
        $dto->msisdn = (string)$request->input('msisdn', '');
        $dto->call_log = $request->input('call_log');
        $dto->dialog_log = $request->input('dialog_log');
        $dto->call_transcript = $request->input('call_transcript');
        $dto->duration = $request->input('duration') !== null ? (int)$request->input('duration') : null;
        $dto->bot_duration = $request->input('bot_duration') !== null ? (int)$request->input('bot_duration') : null;
        $dto->client = $request->input('client');
        $dto->call_record = $request->input('call_record');
        $dto->switch_to_operator = (bool)filter_var($request->input('switch_to_operator', false), FILTER_VALIDATE_BOOLEAN);
        $dto->methods_list = $request->input('methods_list') ?? [];
        $dto->manager_id = $request->input('manager_id') !== null ? (int)$request->input('manager_id') : null;

        return $dto;
    }

    public function toArray(): array
    {
        return [
            'msisdn' => $this->msisdn,
            'call_log' => $this->call_log,
            'dialog_log' => $this->dialog_log,
            'call_transcript' => $this->call_transcript,
            'duration' => $this->duration,
            'bot_duration' => $this->bot_duration,
            'client' => $this->client,
            'call_record' => $this->call_record,
            'switch_to_operator' => $this->switch_to_operator,
            'methods_list' => $this->methods_list,
            'manager_id' => $this->manager_id,
        ];
    }
}


