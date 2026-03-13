<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPTrait.php to edit this template
 */

namespace chats\mango\traits\newQuestions;

/**
 *
 * @author alexey
 */
trait questions {

    public $step_1 = 'Добрый день! Компания бустра, специалист {managerName}, '
            . 'наш разговор записывается. Представтесь пожалуйста';
    public $step_2 = '{clientName}, слушаю Вас. Что Вы хотели сообщить?';
    public $collectorComplaint_1 = '{clientName}, будьте добры расскажите, что у Вас случилось?';
    public $collectorComplaint_2 = '{clientName}, наша компания работает в рамках 230-го Федерального Закона. '
            . 'И данная ситуация очень меня удивляет. Давайте разберемся, когда Вам поступал крайний звонок?';
    public $collectorComplaint_3 = 'Кто Вам звонил?';
    public $collectorComplaint_4 = 'С какого номера Вам звонили?';
    public $collectorComplaint_5 = 'Как представился сотрудник?';
    public $collectorComplaint_6 = 'Что говорили?';
    public $collectorComplaint_7 = 'Есть ли у Вас запись разговора?';
    public $collectorComplaint_9 = '{clientName}, вижу по Вашему договору имеется просрочка. Что повлияло на это ?'
                . '<br/><span style="color: silver; font-size:10px;">'
                . '(даем ответить клиенту, слушаем внимательно, фиксируем информацию)</span>';
    public $collectorComplaint_10 = 'В течении 2х дней необходимо пролонгировать или оплатить полностью Ваш долг. '
                . 'Вы готовы произвести оплату?';
    public $collectorComplaint_11 = 'Каким образом будете совершать оплату? На сайте или по реквизитам?';
    public $collectorComplaint_12 = 'Назовите пожалуйста дату, сумму и время до которого, Вы сможите, произвести оплату?';
    public $collectorComplaint_13 = 'Отлично. Фиксируем дату, время и сумму. После оплаты свяжитесь с нами. Всего Вам доброго, досвидания!';
    public $collectorComplaint_14 = 'Спасибо за обращение. Всего доброго!';

}
