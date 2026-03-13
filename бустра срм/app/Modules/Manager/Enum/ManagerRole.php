<?php

namespace App\Modules\Manager\Domain\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class ManagerRole
 * Определяет все возможные роли менеджеров в системе для PHP 7.4.
 * Является единым источником правды для идентификаторов и названий ролей.
 *
 * @method static self DEVELOPER()
 * @method static self ADMIN()
 * @method static self USER()
 * @method static self VERIFICATOR()
 * @method static self VERIFICATOR_MINUS()
 * @method static self EDIT_VERIFICATOR()
 * @method static self CHIEF_VERIFICATOR()
 * @method static self ANALITIC()
 * @method static self JUNIOR_ANALITIC()
 * @method static self CONTACT_CENTER()
 * @method static self CONTACT_CENTER_ROBO()
 * @method static self CONTACT_CENTER_NEW()
 * @method static self CONTACT_CENTER_NEW_ROBO()
 * @method static self CONTACT_CENTER_PLUS()
 * @method static self SOTRUDNIK_CC()
 * @method static self VERIFICATOR_CC()
 * @method static self YURIST()
 * @method static self CHIEF_CC()
 * @method static self INDIVIDUALS()
 * @method static self BOSS_CC()
 * @method static self ACCOUNTANT()
 * @method static self DISCHARGE()
 * @method static self ROBOT_MINUS()
 * @method static self IP_VERIFICATOR()
 * @method static self PARTNER_SPECIALIST()
 */
class ManagerRole extends Enum
{
    private const DEVELOPER = 'developer';
    private const ADMIN = 'admin';
    private const USER = 'user';
    private const VERIFICATOR = 'verificator';
    private const VERIFICATOR_MINUS = 'verificator_minus';
    private const EDIT_VERIFICATOR = 'edit_verificator';
    private const CHIEF_VERIFICATOR = 'chief_verificator';
    private const ANALITIC = 'analitic';
    private const JUNIOR_ANALITIC = 'junior_analitic';
    private const CONTACT_CENTER = 'contact_center';
    private const CONTACT_CENTER_ROBO = 'contact_center_robo';
    private const CONTACT_CENTER_NEW = 'contact_center_new';
    private const CONTACT_CENTER_NEW_ROBO = 'contact_center_new_robo';
    private const CONTACT_CENTER_PLUS = 'contact_center_plus';
    private const SOTRUDNIK_CC = 'sotrudnik_cc';
    private const VERIFICATOR_CC = 'verificator_cc';
    private const YURIST = 'yurist';
    private const CHIEF_CC = 'chief_cc';
    private const INDIVIDUALS = 'individuals';
    private const BOSS_CC = 'boss_cc';
    private const ACCOUNTANT = 'accountant';
    private const DISCHARGE = 'discharge';
    private const ROBOT_MINUS = 'robot_minus';
    private const IP_VERIFICATOR = 'ip_verificator';
    private const PARTNER_SPECIALIST = 'partner_specialist';

    /**
     * Название ролей на русском языке.
     * @return string
     */
    private const ROLE_LABELS = [
        self::DEVELOPER => 'Разработчик',
        self::ADMIN => 'Ст. менеджер',
        self::USER => 'Менеджер',
        self::VERIFICATOR => 'Верификатор',
        self::VERIFICATOR_MINUS => 'Верификатор-',
        self::EDIT_VERIFICATOR => 'Верификатор с расширенными правами',
        self::CHIEF_VERIFICATOR => 'Шеф-Верификатор',
        self::ANALITIC => 'Аналитик',
        self::JUNIOR_ANALITIC => 'Младший аналитик',
        self::CONTACT_CENTER => 'Исходящий КЦ',
        self::CONTACT_CENTER_ROBO => 'Исходящий КЦ робокампания',
        self::CONTACT_CENTER_NEW => 'Исходящий КЦ 1-2 день',
        self::CONTACT_CENTER_NEW_ROBO => 'Исходящий КЦ робокомпания 1-2 день',
        self::CONTACT_CENTER_PLUS => 'Исходящий КЦ с расширенными правами',
        self::SOTRUDNIK_CC => 'Сотрудник КЦ',
        self::VERIFICATOR_CC => 'Верификатор КЦ',
        self::YURIST => 'Юрист',
        self::CHIEF_CC => 'Нач. отдела по претензионной работе',
        self::INDIVIDUALS => 'Инд. рассмотрение',
        self::BOSS_CC => 'Руководитель КЦ',
        self::ACCOUNTANT => 'Бухгалтер',
        self::DISCHARGE => 'Выгрузка',
        self::ROBOT_MINUS => 'Робот для минусовых дней',
        self::IP_VERIFICATOR => 'ИП Верификатор',
        self::PARTNER_SPECIALIST => 'Работа с партнёрами',
    ];

    /**
     * Получить название роли
     *
     * @return string
     */
    public function getLabel(): string
    {
        return self::ROLE_LABELS[$this->getValue()] ?? $this->getKey();
    }

    /**
     * Возвращает массив всех строковых значений ролей.
     * @return string[]
     */
    public static function getValues(): array
    {
        return array_values(self::toArray());
    }
}
