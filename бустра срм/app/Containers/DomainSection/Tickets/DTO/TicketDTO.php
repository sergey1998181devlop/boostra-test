<?php

namespace App\Containers\DomainSection\Tickets\DTO;

use App\Containers\InfrastructureSection\Contracts\DtoInterface;
use Exception;
use \App\Containers\DomainSection\Tickets\DTO\ClientDTO;
use \DateTime;

class TicketDTO implements DtoInterface
{
    //region Table properties
    private int $id;
    private ClientDTO $client;
    private int $channelId;
    private int $departmentId;
    private int $managerId;
    private int $subjectId;
    private int $statusId;
    private string $description;
    private array $data;
    private $createdAt; //DateTime|false
    private $updatedAt; //DateTime|false
    private int $priorityId;
    private int $clientStatusId;
    private bool $isRepeat;
    private int $orderId;
    private int $initiatorId;
    private int $companyId;
    private $acceptedAt; //DateTime|false
    private $closedAt; //DateTime|false
    private int $workingTime;
    private int $responsiblePersonId;
    private bool $isDuplicate;
    private int $mainTicketId;
    private int $duplicatesCount;
    private bool $isFeedbackReceived;
    private bool $isNotifyUser;
    private string $finalComment;
    private int $directionId;
    //endregion Table properties

    //region Getters and setters
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getClient(): ClientDTO
    {
        return $this->client;
    }

    public function setClient(ClientDTO $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getChannelId(): int
    {
        return $this->channelId;
    }

    public function setChannelId(int $channelId): self
    {
        $this->channelId = $channelId;
        return $this;
    }

    public function getDepartmentId(): int
    {
        return $this->departmentId;
    }

    public function setDepartmentId(int $departmentId): self
    {
        $this->departmentId = $departmentId;
        return $this;
    }

    public function getManagerId(): int
    {
        return $this->managerId;
    }

    public function setManagerId(int $managerId): self
    {
        $this->managerId = $managerId;
        return $this;
    }

    public function getSubjectId(): int
    {
        return $this->subjectId;
    }

    public function setSubjectId(int $subjectId): self
    {
        $this->subjectId = $subjectId;
        return $this;
    }

    public function getStatusId(): int
    {
        return $this->statusId;
    }

    public function setStatusId(int $statusId): self
    {
        $this->statusId = $statusId;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getPriorityId(): int
    {
        return $this->priorityId;
    }

    public function setPriorityId(int $priorityId): self
    {
        $this->priorityId = $priorityId;
        return $this;
    }

    public function getClientStatusId(): int
    {
        return $this->clientStatusId;
    }

    public function setClientStatusId(int $clientStatusId): self
    {
        $this->clientStatusId = $clientStatusId;
        return $this;
    }

    public function isRepeat(): bool
    {
        return $this->isRepeat;
    }

    public function setIsRepeat(bool $isRepeat): self
    {
        $this->isRepeat = $isRepeat;
        return $this;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getInitiatorId(): int
    {
        return $this->initiatorId;
    }

    public function setInitiatorId(int $initiatorId): self
    {
        $this->initiatorId = $initiatorId;
        return $this;
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): self
    {
        $this->companyId = $companyId;
        return $this;
    }

    public function getAcceptedAt()
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(DateTime $acceptedAt): self
    {
        $this->acceptedAt = $acceptedAt;
        return $this;
    }

    public function getClosedAt()
    {
        return $this->closedAt;
    }

    public function setClosedAt(DateTime $closedAt): self
    {
        $this->closedAt = $closedAt;
        return $this;
    }

    public function getWorkingTime(): int
    {
        return $this->workingTime;
    }

    public function setWorkingTime(int $workingTime): self
    {
        $this->workingTime = $workingTime;
        return $this;
    }

    public function getResponsiblePersonId(): int
    {
        return $this->responsiblePersonId;
    }

    public function setResponsiblePersonId(int $responsiblePersonId): self
    {
        $this->responsiblePersonId = $responsiblePersonId;
        return $this;
    }

    public function isDuplicate(): bool
    {
        return $this->isDuplicate;
    }

    public function setIsDuplicate(bool $isDuplicate): self
    {
        $this->isDuplicate = $isDuplicate;
        return $this;
    }

    public function getMainTicketId(): int
    {
        return $this->mainTicketId;
    }

    public function setMainTicketId(int $mainTicketId): self
    {
        $this->mainTicketId = $mainTicketId;
        return $this;
    }

    public function getDuplicatesCount(): int
    {
        return $this->duplicatesCount;
    }

    public function setDuplicatesCount(int $duplicatesCount): self
    {
        $this->duplicatesCount = $duplicatesCount;
        return $this;
    }

    public function isFeedbackReceived(): bool
    {
        return $this->isFeedbackReceived;
    }

    public function setIsFeedbackReceived(bool $isFeedbackReceived): self
    {
        $this->isFeedbackReceived = $isFeedbackReceived;
        return $this;
    }

    public function isNotifyUser(): bool
    {
        return $this->isNotifyUser;
    }

    public function setIsNotifyUser(bool $isNotifyUser): self
    {
        $this->isNotifyUser = $isNotifyUser;
        return $this;
    }

    public function getFinalComment(): string
    {
        return $this->finalComment;
    }

    public function setFinalComment(string $finalComment): self
    {
        $this->finalComment = $finalComment;
        return $this;
    }

    public function getDirectionId(): int
    {
        return $this->directionId;
    }

    public function setDirectionId(int $directionId): self
    {
        $this->directionId = $directionId;
        return $this;
    }
    //endregion Getters and setters

    /**
     * @param ClientDTO $client
     * @param int $id
     * @param int $channelId
     * @param int $departmentId
     * @param int $managerId
     * @param int $subjectId
     * @param int $statusId
     * @param string $description
     * @param array $data
     * @param string $createdAt
     * @param string $updatedAt
     * @param int $priorityId
     * @param int $clientStatusId
     * @param bool $isRepeat
     * @param int $orderId
     * @param int $initiatorId
     * @param int $companyId
     * @param string $acceptedAt
     * @param string $closedAt
     * @param int $workingTime
     * @param int $responsiblePersonId
     * @param bool $isDuplicate
     * @param int $mainTicketId
     * @param int $duplicatesCount
     * @param bool $isFeedbackReceived
     * @param bool $isNotifyUser
     * @param string $finalComment
     * @param int $directionId
     * @throws Exception
     */
    public function __construct
    (
        ClientDTO $client,
        int       $id = 0,
        int       $channelId = 0,
        int       $departmentId = 0,
        int       $managerId = 0,
        int       $subjectId = 0,
        int       $statusId = 0,
        string    $description = '',
        array     $data = [],
        string    $createdAt = '',
        string    $updatedAt = '',
        int       $priorityId = 0,
        int       $clientStatusId = 0,
        bool      $isRepeat = false,
        int       $orderId = 0,
        int       $initiatorId = 0,
        int       $companyId = 0,
        string    $acceptedAt = '',
        string    $closedAt = '',
        int       $workingTime = 0,
        int       $responsiblePersonId = 0,
        bool      $isDuplicate = false,
        int       $mainTicketId = 0,
        int       $duplicatesCount = 0,
        bool      $isFeedbackReceived = false,
        bool      $isNotifyUser = false,
        string    $finalComment = '',
        int       $directionId = 0
    )
    {
        $this->id = $id;
        $this->client = $client;
        $this->channelId = $channelId;
        $this->departmentId = $departmentId;
        $this->managerId = $managerId;
        $this->subjectId = $subjectId;
        $this->statusId = $statusId;
        $this->description = $description;
        $this->data = $data;
        $this->createdAt = empty($createdAt) ? false : new DateTime($createdAt);
        $this->updatedAt = empty($updatedAt) ? false : new DateTime($updatedAt);
        $this->priorityId = $priorityId;
        $this->clientStatusId = $clientStatusId;
        $this->isRepeat = $isRepeat;
        $this->orderId = $orderId;
        $this->initiatorId = $initiatorId;
        $this->companyId = $companyId;
        $this->acceptedAt = empty($acceptedAt) ? false : new DateTime($acceptedAt);
        $this->closedAt = empty($closedAt) ? false : new DateTime($closedAt);
        $this->workingTime = $workingTime;
        $this->responsiblePersonId = $responsiblePersonId;
        $this->isDuplicate = $isDuplicate;
        $this->mainTicketId = $mainTicketId;
        $this->duplicatesCount = $duplicatesCount;
        $this->isFeedbackReceived = $isFeedbackReceived;
        $this->isNotifyUser = $isNotifyUser;
        $this->finalComment = $finalComment;
        $this->directionId = $directionId;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        if (empty($this->id)) {
            throw new Exception('Empty id!');
        }

        return [
            'id' => $this->id,
            'client_id' => $this->client->getId() ?: null,
            'channel_id' => $this->channelId,
            'department_id' => $this->departmentId,
            'manager_id' => $this->managerId,
            'subject_id' => $this->subjectId,
            'status_id' => $this->statusId,
            'description' => $this->description,
            'data' => $this->data ? json_encode($this->data, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => !empty($this->createdAt) ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updated_at' => !empty($this->updatedAt) ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            'priority_id' => $this->priorityId,
            'client_status' => $this->clientStatusId,
            'is_repeat' => (int)$this->isRepeat,
            'order_id' => $this->orderId,
            'company_id' => $this->companyId,
            'accepted_at' => !empty($this->acceptedAt) ? $this->acceptedAt->format('Y-m-d H:i:s') : null,
            'closed_at' => !empty($this->closedAt) ? $this->closedAt->format('Y-m-d H:i:s') : null,
            'working_time' => $this->workingTime,
            'responsible_person' => $this->responsiblePersonId,
            'is_duplicate' => (int)$this->isDuplicate,
            'main_ticket_id' => $this->mainTicketId ?: null,
            'duplicates_count' => $this->duplicatesCount,
            'feedback_received' => (int)$this->isFeedbackReceived,
            'notify_user' => (int)$this->isNotifyUser,
            'final_comment' => $this->finalComment ?: null,
            'direction_id' => $this->directionId ?: null,
        ];
    }
}