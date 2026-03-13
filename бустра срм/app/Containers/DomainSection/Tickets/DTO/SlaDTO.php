<?php

namespace App\Containers\DomainSection\Tickets\DTO;

use App\Containers\InfrastructureSection\Contracts\DtoInterface;

class SlaDTO implements DtoInterface
{
    //region Attributes
    private int $id;
    private string $name;
    private int $quarter;
    private int $year;
    private int $priorityId;
    private float $reactionMinutes;
    private float $reactionPercent;
    private float $resolutionMinutes;
    private float $resolutionPercent;
    private float $totalReactionPercent;
    private float $totalResolutionPercent;
    //endregion Attributes

    //region Getters and Setter
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getQuarter(): int
    {
        return $this->quarter;
    }

    public function setQuarter(int $quarter): self
    {
        $this->quarter = $quarter;
        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;
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

    public function getReactionMinutes(): float
    {
        return $this->reactionMinutes;
    }

    public function setReactionMinutes(float $reactionMinutes): self
    {
        $this->reactionMinutes = $reactionMinutes;
        return $this;
    }

    public function getReactionPercent(): float
    {
        return $this->reactionPercent;
    }

    public function setReactionPercent(float $reactionPercent): self
    {
        $this->reactionPercent = $reactionPercent;
        return $this;
    }

    public function getResolutionMinutes(): float
    {
        return $this->resolutionMinutes;
    }

    public function setResolutionMinutes(float $resolutionMinutes): self
    {
        $this->resolutionMinutes = $resolutionMinutes;
        return $this;
    }

    public function getResolutionPercent(): float
    {
        return $this->resolutionPercent;
    }

    public function setResolutionPercent(float $resolutionPercent): self
    {
        $this->resolutionPercent = $resolutionPercent;
        return $this;
    }

    public function getTotalReactionPercent(): float
    {
        return $this->totalReactionPercent;
    }

    public function setTotalReactionPercent(float $totalReactionPercent): self
    {
        $this->totalReactionPercent = $totalReactionPercent;
        return $this;
    }

    public function getTotalResolutionPercent(): float
    {
        return $this->totalResolutionPercent;
    }

    public function setTotalResolutionPercent(float $totalResolutionPercent): self
    {
        $this->totalResolutionPercent = $totalResolutionPercent;
        return $this;
    }

    //endregion Getters and Setter

    public function __construct(
        int    $id = 0,
        string $name = '',
        int    $quarter = 0,
        int    $year = 0,
        int    $priorityId = 0,
        float  $reactionMinutes = 0,
        float  $reactionPercent = 0,
        float  $resolutionMinutes = 0,
        float  $resolutionPercent = 0,
        float  $totalReactionPercent = 0,
        float  $totalResolutionPercent = 0
    )
    {
        $quarter = $quarter ?: ceil((int)date('m') / 3);
        $year = $year ?: (int)date('Y');

        $this->id = $id;
        $this->name = $name ?: $this->getQuarterNameByNumber($quarter, $year);
        $this->quarter = $quarter;
        $this->year = $year;
        $this->priorityId = $priorityId;
        $this->reactionMinutes = $reactionMinutes;
        $this->reactionPercent = $reactionPercent;
        $this->resolutionMinutes = $resolutionMinutes;
        $this->resolutionPercent = $resolutionPercent;
        $this->totalReactionPercent = $totalReactionPercent;
        $this->totalResolutionPercent = $totalResolutionPercent;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'quarter' => $this->quarter,
            'year' => $this->year,
            'priority_id' => $this->priorityId,
            'reaction_minutes' => $this->reactionMinutes,
            'reaction_percent' => $this->reactionPercent,
            'resolution_minutes' => $this->resolutionMinutes,
            'resolution_percent' => $this->resolutionPercent,
            'total_reaction_percent' => $this->totalReactionPercent,
            'total_resolution_percent' => $this->totalResolutionPercent,
        ];
    }

    private function getQuarterNameByNumber(int $quarter, int $year): string
    {
        $name = '';

        switch ($quarter) {
            case 1:
                $name = 'Зима ' . $year;
                break;
            case 2:
                $name = 'Весна ' . $year;
                break;
            case 3:
                $name = 'Лето ' . $year;
                break;
            case 4:
                $name = 'Осень ' . $year;
                break;
        }

        return $name;
    }
}