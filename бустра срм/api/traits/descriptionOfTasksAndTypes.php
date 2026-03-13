<?php

namespace api\traits;

trait descriptionOfTasksAndTypes {

    /**
     * Статусы задач
     */
    public $taskStatuses = [
        0 => 'Новая',
        1 => 'В работе',
        2 => 'Выполнена'
    ];

    /**
     * Названия методов и шаблонов в соответствии с типом задачи
     */
    public $actionsByTaskType = [
        '0' => 'insuranceRefund', # Задача по возврату страховки
        '1' => 'paymentReminderCall', # Задача о напоминании платежа
        '2' => 'callToClarifyThePaymentDate', # Звонок при просрочке платежа
        '3' => 'sendMessageToEmail', # Задача о отправке сообщения на Email
        '4' => 'sendMessageToMessengers', # Задача о отправке сообщения в месенджеры
        '5' => 'sendMessageToSms', # Задача о отправке сообщения в смс
        '6' => 'BlacklistingClient', # Занесение клиента в черный список
        '7' => 'refineTaskData', # Уточнить данные по задаче
        '8' => 'complaintCollectionService', # Задача по жалобе на службу взыскания
        '9' => 'technicalProblems', # Задача по техническим проблемам
        '10' => 'insuranceRefundOrReceiptOfCertificates', # Возврат страховки или получение справок
        '11' => 'refineTaskDataByInsuranceRefund', # Уточнить данные по задаче возврат страховки
        '12' => 'repeatCallForTask', # Повторный звонок по задаче
        '13' =>'controlOfPaymentOnTheDatePromisedByTheClient', # контроль оплаты в дату обещанную клиентом
        '14' => 'callInCaseOfNonPaymentOnTheDatePromisedByTheClient', # Звонок в случае неоплаты в дату обещанную клиентом
    ];
    
    public $executorRole = [
        'insuranceRefund' => 'yurist',
        'paymentReminderCall' => 'yurist',
        'callToClarifyThePaymentDate' => 'yurist',
        'complaintCollectionService' => 'yurist',
        'technicalProblems' => 'developer',
        'insuranceRefundOrReceiptOfCertificates' => 'yurist',
        'refineTaskDataByInsuranceRefund' => 'yurist',
        'controlOfPaymentOnTheDatePromisedByTheClient' => 'yurist',
        'callInCaseOfNonPaymentOnTheDatePromisedByTheClient' => 'yurist',
    ];

    /**
     * Наименование задач
     */
    public $taskNames = [
        'insuranceRefund' => 'Звонок по возврату страховки',
        'paymentReminderCall' => 'Звонок о напоминании платежа',
        'callToClarifyThePaymentDate' => 'Звонок по просрочке платежа',
        'sendMessageToEmail' => 'Отправить сообщение на Email',
        'sendMessageToMessengers' => 'Отправить сообщение в месенджер',
        'sendMessageToSms' => 'Отправить сообщение в смс',
        'BlacklistingClient' => 'Внести клиента в черный список',
        'refineTaskData' => 'Уточнить данные по задаче',
        'complaintCollectionService' => 'Жалоба на службу взыскания',
        'technicalProblems' => 'Уточнить данные по Техническим проблемам',
        'insuranceRefundOrReceiptOfCertificates' => 'Уточнить данные по Возврату страховки или получение справок',
        'refineTaskDataByInsuranceRefund' => 'Уточнить данные по задаче возврат страховки',
        'repeatCallForTask' => 'Повторный звонок по задаче',
        'controlOfPaymentOnTheDatePromisedByTheClient' => 'Проконтролировать оплату в дату обещанную клиентом',
        'callInCaseOfNonPaymentOnTheDatePromisedByTheClient' => 'Звонок в случае неоплаты в дату обещанную клиентом',
    ];

}
