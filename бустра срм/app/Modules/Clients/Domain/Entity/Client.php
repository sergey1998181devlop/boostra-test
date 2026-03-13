<?php

namespace App\Modules\Clients\Domain\Entity;

class Client
{
    private int $id;
    private string $uid;
    private string $firstname;
    private string $lastname;
    private string $patronymic;
    private string $phone;
    private bool $blocked;
    private ?string $loanHistory;
    private ?string $saleInfo;
    private ?string $buyer;
    private ?string $buyerPhone;
    private bool $autoInformerEnabled;
    private bool $recurrentsDisabled;

    public function __construct(
        int $id,
        string $uid,
        string $firstname,
        string $lastname,
        string $patronymic,
        string $phone,
        bool $blocked,
        ?string $loanHistory = null,
        ?string $saleInfo = null,
        ?string $buyer = null,
        ?string $buyerPhone = null,
        bool $autoInformerEnabled = true,
        bool $recurrentsDisabled = false
    ) {
        $this->id = $id;
        $this->uid = $uid;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->patronymic = $patronymic;
        $this->phone = $phone;
        $this->blocked = $blocked;
        $this->loanHistory = $loanHistory;
        $this->saleInfo = $saleInfo;
        $this->buyer = $buyer;
        $this->buyerPhone = $buyerPhone;
        $this->autoInformerEnabled = $autoInformerEnabled;
        $this->recurrentsDisabled = $recurrentsDisabled;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            $data['UID'] ?? '',
            $data['firstname'] ?? '',
            $data['lastname'] ?? '',
            $data['patronymic'] ?? '',
            $data['phone_mobile'] ?? '',
            (bool)($data['blocked'] ?? false),
            $data['loan_history'] ?? null,
            !empty($data['sale_info']) ? $data['sale_info'] : null,
            !empty($data['buyer']) ? $data['buyer'] : null,
            !empty($data['buyer_phone']) ? $data['buyer_phone'] : null,
            (bool)($data['auto_informer_enabled'] ?? true),
            (bool)($data['recurrents_disabled'] ?? false)
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getPatronymic(): string
    {
        return $this->patronymic;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    public function getLoanHistory(): array
    {
        if (empty($this->loanHistory)) {
            return [];
        }

        $decoded = json_decode($this->loanHistory, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function hasActiveLoanHistory(): bool
    {
        $history = $this->getLoanHistory();
        
        foreach ($history as $loan) {
            if (empty($loan['close_date'])) {
                return true;
            }
        }
        
        return false;
    }

    public function getSaleInfo(): ?string
    {
        return $this->saleInfo;
    }

    public function getBuyer(): ?string
    {
        return $this->buyer;
    }

    public function getBuyerPhone(): ?string
    {
        return $this->buyerPhone;
    }

    public function isAutoInformerEnabled(): bool
    {
        return $this->autoInformerEnabled;
    }

    public function areRecurrentsDisabled(): bool
    {
        return $this->recurrentsDisabled;
    }
}