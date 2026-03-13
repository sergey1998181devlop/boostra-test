<?php

namespace App\Enums;

use InvalidArgumentException;
use MyCLabs\Enum\Enum;

class LogAction extends Enum
{
    public const SWITCH_ON_PROLONGATION = 'switch_on_prolongation';
    public const SWITCH_OFF_PROLONGATION = 'switch_off_prolongation';
    public const BLOCK_ACCOUNT = 'block_account';
    public const UNBLOCK_ACCOUNT = 'unblock_account';
    public const TOGGLE_AUTODEBIT_ON_ALL = 'toggle_autodebit_on_all';
    public const TOGGLE_AUTODEBIT_OFF_ALL = 'toggle_autodebit_off_all';
    public const TOGGLE_AUTODEBIT_ON_CARDS = 'toggle_autodebit_on_cards';
    public const TOGGLE_AUTODEBIT_OFF_CARDS = 'toggle_autodebit_off_cards';
    public const TOGGLE_AUTODEBIT_ON_SBP = 'toggle_autodebit_on_sbp';
    public const TOGGLE_AUTODEBIT_OFF_SBP = 'toggle_autodebit_off_sbp';

    public static array $labels = [
        self::SWITCH_ON_PROLONGATION => 'Включена пролонгация',
        self::SWITCH_OFF_PROLONGATION => 'Выключена пролонгация',
        self::BLOCK_ACCOUNT => 'Личный кабинет успешно заблокирован',
        self::UNBLOCK_ACCOUNT => 'Личный кабинет успешно разблокирован',
        self::TOGGLE_AUTODEBIT_ON_ALL => 'Автодебет включен для всех карт и СБП-счетов',
        self::TOGGLE_AUTODEBIT_OFF_ALL => 'Автодебет выключен для всех карт и СБП-счетов',
        self::TOGGLE_AUTODEBIT_ON_CARDS => 'Автодебет включен для всех карт',
        self::TOGGLE_AUTODEBIT_OFF_CARDS => 'Автодебет выключен для всех карт',
        self::TOGGLE_AUTODEBIT_ON_SBP => 'Автодебет включен для всех СБП-счетов',
        self::TOGGLE_AUTODEBIT_OFF_SBP => 'Автодебет выключен для всех СБП-счетов',
    ];

    public function getMessage(): string
    {
        if (!isset(self::$labels[$this->getValue()])) {
            throw new InvalidArgumentException("Unknown label for  $this->getValue()");
        }

        return self::$labels[$this->getValue()];
    }

    public function getCommentBlock(): ?string
    {
        if (in_array($this->getValue(), [self::SWITCH_ON_PROLONGATION, self::SWITCH_OFF_PROLONGATION])) {
            return CommentBlocks::ORDER;
        }

        if (in_array($this->getValue(), [self::BLOCK_ACCOUNT, self::UNBLOCK_ACCOUNT])) {
            return CommentBlocks::PERSONAL;
        }

        if (in_array($this->getValue(), [
            self::TOGGLE_AUTODEBIT_ON_ALL,
            self::TOGGLE_AUTODEBIT_OFF_ALL,
            self::TOGGLE_AUTODEBIT_ON_CARDS,
            self::TOGGLE_AUTODEBIT_OFF_CARDS,
            self::TOGGLE_AUTODEBIT_ON_SBP,
            self::TOGGLE_AUTODEBIT_OFF_SBP,
        ])) {
            return CommentBlocks::ORDER;
        }

        return null;
    }

    public function needsChangelog(): bool
    {
        if (in_array($this->getValue(), [
            self::SWITCH_ON_PROLONGATION,
            self::SWITCH_OFF_PROLONGATION,
            self::BLOCK_ACCOUNT,
            self::UNBLOCK_ACCOUNT,
        ])) {
            return true;
        }

        return false;
    }

    public function needsSendTo1C(): bool
    {
        if (in_array($this->getValue(), [
            self::SWITCH_ON_PROLONGATION,
            self::SWITCH_OFF_PROLONGATION,
            self::BLOCK_ACCOUNT,
            self::UNBLOCK_ACCOUNT,
        ])) {
            return true;
        }

        return false;
    }
}