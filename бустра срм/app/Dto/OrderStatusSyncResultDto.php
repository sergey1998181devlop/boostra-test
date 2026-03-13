<?php

namespace App\Dto;

final class OrderStatusSyncResultDto
{
    public bool $success;
    public string $message;
    public bool $hasChanges;
    public ?string $currentStatus;
    public ?string $oldStatus;
    public ?string $newStatus;

    public static function success(bool $hasChanges, string $currentStatus, ?string $oldStatus = null, ?string $newStatus = null): self
    {
        $dto = new self();
        $dto->success = true;
        $dto->hasChanges = $hasChanges;
        $dto->currentStatus = $currentStatus;
        $dto->oldStatus = $oldStatus;
        $dto->newStatus = $newStatus;
        $dto->message = $hasChanges 
            ? 'Статус обновлен' 
            : 'Статус актуален';
        
        return $dto;
    }

    public static function error(string $message): self
    {
        $dto = new self();
        $dto->success = false;
        $dto->message = $message;
        $dto->hasChanges = false;
        $dto->currentStatus = null;
        $dto->oldStatus = null;
        $dto->newStatus = null;
        
        return $dto;
    }

    public function toArray(): array
    {
        $result = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if ($this->success) {
            $result['has_changes'] = $this->hasChanges;
            
            if ($this->hasChanges) {
                $result['old_status'] = $this->oldStatus;
                $result['new_status'] = $this->newStatus;
            } else {
                $result['current_status'] = $this->currentStatus;
            }
        }

        return $result;
    }
}

