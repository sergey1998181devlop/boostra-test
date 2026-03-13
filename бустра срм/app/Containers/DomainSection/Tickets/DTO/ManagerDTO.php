<?php

namespace App\Containers\DomainSection\Tickets\DTO;

use App\Containers\InfrastructureSection\Contracts\DtoInterface;
use DateTime;
use Exception;

class ManagerDTO implements DtoInterface
{
    //region Attributes
    private int $id;
    private string $login;
    private string $password;
    private string $name;
    private string $name1c;
    private string $role;
    private string $lastIp;
    private $lasVisit; //\DateTime|false
    private string $salt;
    private int $mangoNumber;
    private string $avatar;
    private bool $isBlocked; //"blocked" in database
    private bool $isVoxDeleted; //"vox_deleted" in database
    //endregion Attributes

    /**
     * @param int $id
     * @param string $login
     * @param string $password
     * @param string $name
     * @param string $name1c
     * @param string $role
     * @param string $lastIp
     * @param string $lasVisit
     * @param string $salt
     * @param int $mangoNumber
     * @param string $avatar
     * @param bool $isBlocked
     * @param bool $isVoxDeleted
     * @throws Exception
     */
    public function __construct(
        int    $id = 0,
        string $login = '',
        string $password = '',
        string $name = '',
        string $name1c = '',
        string $role = '',
        string $lastIp = '',
        string $lasVisit = '',
        string $salt = '',
        int    $mangoNumber = 0,
        string $avatar = '',
        bool   $isBlocked = false,
        bool   $isVoxDeleted = false
    )
    {
        $this->id = $id;
        $this->login = $login;
        $this->password = $password;
        $this->name = $name;
        $this->name1c = $name1c;
        $this->role = $role;
        $this->lastIp = $lastIp;
        $this->lasVisit = empty($lasVisit) ? false : new DateTime($lasVisit);;
        $this->salt = $salt;
        $this->mangoNumber = $mangoNumber;
        $this->avatar = $avatar;
        $this->isBlocked = $isBlocked;
        $this->isVoxDeleted = $isVoxDeleted;
    }

    //region Getters and Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
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

    public function getName1c(): string
    {
        return $this->name1c;
    }

    public function setName1c(string $name1c): self
    {
        $this->name1c = $name1c;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getLastIp(): string
    {
        return $this->lastIp;
    }

    public function setLastIp(string $lastIp): self
    {
        $this->lastIp = $lastIp;
        return $this;
    }

    public function getLasVisit()
    {
        return $this->lasVisit;
    }

    public function setLasVisit(DateTime $lasVisit): self
    {
        $this->lasVisit = $lasVisit;
        return $this;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;
        return $this;
    }

    public function getMangoNumber(): int
    {
        return $this->mangoNumber;
    }

    public function setMangoNumber(int $mangoNumber): self
    {
        $this->mangoNumber = $mangoNumber;
        return $this;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): self
    {
        $this->isBlocked = $isBlocked;
        return $this;
    }

    public function isVoxDeleted(): bool
    {
        return $this->isVoxDeleted;
    }

    public function setIsVoxDeleted(bool $isVoxDeleted): self
    {
        $this->isVoxDeleted = $isVoxDeleted;
        return $this;
    }

    //endregion Getters and Setters

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'password' => $this->password,
            'name' => $this->name,
            'name_1c' => $this->name1c,
            'role' => $this->role,
            'last_ip' => $this->lastIp,
            'last_visit' => !empty($this->lasVisit) ? $this->lasVisit->format('Y-m-d H:i:s') : null,
            'salt' => $this->salt,
            'mango_number' => $this->mangoNumber,
            'avatar' => $this->avatar,
            'blocked' => (int)$this->isBlocked,
            'vox_deleted' => (int)$this->isVoxDeleted,
        ];
    }
}