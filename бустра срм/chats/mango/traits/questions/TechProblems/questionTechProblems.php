<?php

namespace chats\mango\traits\questions\TechProblems;

trait questionTechProblems {

    public function startTechProblem() {
        return '<span style="color: silver; font-size: 10px;">(Выберите проблему с которой столкнулся клиент)</span>';
    }

    public function questionStep_EndTechProblems() {
        return 'Спасибо за обращение. Ваша заявка принята. '
                . '{clientName}, приносим Вам свои извинения за доставленые неудобства.'
                . 'В ближайшее время наши специалисты все исправят. Всего Вам доброго, досвидания!';
    }

}
