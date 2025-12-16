<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class JobDto implements BaseDtoInterface
{
    public ?int $jobTypeCode; // Код типа трудоустройства 7 – Трудоустроен официально 4 – Трудоустроен неофициально 10 – Индивидуальный предприниматель 5 – Пенсионер 3 – Не работаю
    public ?string $dateStart; // Дата трудоустройства на текущем месте работы. Формат: YYYY-MM-DD

    public TotalSeniorityDto $totalSeniorityDto;
    public LastSeniorityDto $lastSeniorityDto;

    public ?string $positionName; // Занимаемая должность Пример: Разработчик
    public PhoneDto $phoneDto;

    public ?string $jobEmployerName; // Наименование организации-работодателя
    public ?int $jobEmployerType; // Код типа организации-работодателя 11 - "ООО", 12 - "ЗАО", 14 - "ОАО", 15 - "ПАО", 13 - "АО", 17 - "ИП", 18 - "ФГУП", 19 - "Федеральные государственные бюджетные учреждения", 20	- "ГБУ", 99 - "Прочее"
    public ?string $jobEmployerInn;
    public ?int $jobEmployerNumberStaff; // Код численности сотрудников организации-работодателя 15 – до 20 80 – 21-100 работников 200 – 101-500 работников 550 – более 500  работников

    /**
     * Код сферы деятельности организации-работодателя
     * 1 - Оборона, правоохранительные органы
     * 8 - Здравоохранение, социальные услуги
     * 2 - Финансовая, страховая деятельность
     * 11 - Образование
     * 12 - Государственное, муниципальное управление
     * 17 - Строительство
     * 43 - Сфера торговли, услуг, связи
     * 9 - Профессиональная, научная и техническая деятельность
     * 27 - Добывающая промышленность
     * 21 - Транспорт
     * 25 - Электроэнергия, газо-, водо-, теплоснабжение
     * 15 - Сельское, лесное хозяйство, охота, рыболовство и рыбоводство
     * 30 - Обрабатывающая промышленность, производства
     * 6 - Культура, искусство, спортивная деятельность
     * 99 - Другое
    */
    public ?int $jobEnterpriseActivityTypeCode;

    /**
     * Тип занимаемой должности
     * 1 - Владелец бизнеса,ИП
     * 2 - Руководитель высшего звена
     * 3 - Руководитель подразделения
     * 4 - Специалист, служащий
     * 5 - Неквалифицированный рабочий
     * 6 - не работаю
     */
    public ?int $positionTypeCode;


    public function __construct(
        ?int $jobTypeCode,
        ?string $dateStart,
        TotalSeniorityDto $totalSeniorityDto,
        LastSeniorityDto $lastSeniorityDto,
        ?string $positionName,
        PhoneDto $phoneDto,
        ?string $jobEmployerName,
        ?int $jobEmployerType,
        ?string $jobEmployerInn,
        ?int $jobEmployerNumberStaff,
        ?int $jobEnterpriseActivityTypeCode,
        ?int $positionTypeCode
    ) {
        $this->jobTypeCode = $jobTypeCode;
        $this->dateStart = $dateStart;
        $this->totalSeniorityDto = $totalSeniorityDto;
        $this->lastSeniorityDto = $lastSeniorityDto;
        $this->positionName = $positionName;
        $this->phoneDto = $phoneDto;
        $this->jobEmployerName = $jobEmployerName;
        $this->jobEmployerType = $jobEmployerType;
        $this->jobEmployerInn = $jobEmployerInn;
        $this->jobEmployerNumberStaff = $jobEmployerNumberStaff;
        $this->jobEnterpriseActivityTypeCode = $jobEnterpriseActivityTypeCode;
        $this->positionTypeCode = $positionTypeCode;
    }

    public function isNull(): bool
    {
        // TODO: Implement isNull() method.
    }
}
