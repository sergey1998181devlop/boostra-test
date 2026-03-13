<?php

namespace chats\mango\traits;

trait questionsAll {

    public function questionStep_1() {
        return 'Добрый день! Компания бустра, специалист {managerName}, '
                . 'наш разговор записывается. Представтесь пожалуйста';
    }

    public function questionStep_2() {
        return '{clientName}, слушаю Вас. Что Вы хотели сообщить?';
    }

    public function questionStep_3() {
        return '{clientName}, будте добры расскажите, что у Вас случилось?';
    }

    public function questionStep_4() {
        return '{clientName}, наша компания работает в рамках 230-го Федерального Закона. И данная ситуация очень меня удивляет. Давайте разберемся, когда Вам поступал крайний звонок?';
    }

    public function questionStep_5() {
        return 'Кто Вам звонил?';
    }

    public function questionStep_6() {
        return 'С какого номера Вам звонили?';
    }

    public function questionStep_7() {
        return 'Как представился сотрудник?';
    }

    public function questionStep_8() {
        return 'Что говорили?';
    }

    public function questionStep_9() {
        return 'Есть ли у Вас запись разговора?';
    }

    public function questionStep_10() {
        return '{clientName}, вижу по Вашему договору имеется просрочка. '
                . '<span style="color: silver; font-size:10px;">'
                . '(даем ответить клиенту, слушаем внимательно, фиксируем информацию)</span>';
    }

    public function questionStep_11() {
        return 'В течении 2х дней необходимо пролонгировать или оплатить полностью Ваш долг. '
                . 'Вы готовы произвести оплату?';
    }

    public function questionStep_12() {
        return 'Каким образом будете совершать оплату? На сайте или по реквизитам?';
    }

    public function questionStep_13() {
        return 'Назовите пожалуйста дату, сумму и время до которого, Вы сможите, произвести оплату?';
    }

    public function questionStep_14() {
        return 'Отлично. Фиксируем дату, время и сумму. После оплаты свяжитесь с нами. Всего Вам доброго, досвидания!';
    }
    
    public function questionStep_EndNoPayment() {
        return 'Спасибо за обращение. Всего доброго!';
    }

}