
            <div id="info" class="tab-pane" role="tabpanel">
                <div class="row" id="order_wrapper">
                    <div class="col-12">
                        <div class="card card-outline-info">
                            <div class="card-body">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p>
                                                        Дата создания: {$ticketInfo->dateCreate|date} {$ticketInfo->dateCreate|time}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="row">
                                                
                                            </div>                                                
                                            <hr />
                                        </div>                               
                                            <div class="col-lg-4 col-md-6 col-12">
                                                <div class="row edit-block">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">ФИО клиента</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$userInfo->lastname}
                                                                {$userInfo->firstname}
                                                                {$userInfo->patronymic}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Дата рождения</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$userInfo->birth|date}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Телeфон клиента</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$userInfo->phone_mobile}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-12">
                                                <div class="row edit-block">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Дата займа</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$task->{'ДатаЗайма'}|date}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Сумма займа</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$task->{'СуммаЗайма'}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Номер заявки</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$task->{'Заявка'}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Номер договора</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$task->{'НомерЗайма'}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-12">    
                                                <div class="row edit-block">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">ФИО принявшего заявку </label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$managers[$ticketInfo->managerId]->name}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Дата поступления обращения</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$ticketInfo->dateCreate|date}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Источник</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                {$ticketInfo->inputChanel}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            {$userId = $userInfo->id}
            {include file='../../chat.tpl'}
            <div id="ticketComments" class="tab-pane" role="tabpanel">
                <table class="table table-striped table-hover">
                    <tr>
                        <td style="width: 80px;" class="jsgrid-cell">
                            №
                        </td>
                        <td style="width: 150px;" class="jsgrid-cell">
                            Дата
                        </td>
                        <td style="width: 150px;" class="jsgrid-cell">
                            Сотрудник
                        </td>
                        <td class="jsgrid-cell">
                            Комментарий
                        </td>
                    </tr>
                    {foreach $task->ticketComments AS $ticketComment}
                    <tr>
                        <td class="jsgrid-cell">
                            {$ticketComment->id}
                        </td>
                        <td class="jsgrid-cell">
                            {$ticketComment->dateCreate|date} {$ticketComment->dateCreate|time}
                        </td>
                        <td class="jsgrid-cell">
                            {$managers[$ticketComment->managerId]->name}
                        </td>
                        <td class="jsgrid-cell">
                            {$ticketComment->comment}
                        </td> 
                    </tr>
                    {/foreach}
                </table>
            </div>
            <div id="taskComments" class="tab-pane" role="tabpanel">
                <table class="table table-striped table-hover">
                    <tr>
                        <td style="width: 60px;"  class="jsgrid-cell">
                            №
                        </td>
                        <td style="width: 150px;"  class="jsgrid-cell">
                            Дата
                        </td>
                        <td style="width: 150px;"  class="jsgrid-cell">
                            Сотрудник
                        </td>
                        <td class="jsgrid-cell">
                            Комментарий
                        </td>
                    </tr>
                    {foreach $task->taskComments AS $taskComment}
                    <tr>
                        <td class="jsgrid-cell">
                            {$taskComment->id}
                        </td>
                        <td class="jsgrid-cell">
                            {$taskComment->dateCreate|date} {$taskComment->dateCreate|time}
                        </td>
                        <td class="jsgrid-cell">
                            {$managers[$taskComment->managerId]->name}
                        </td>
                        <td class="jsgrid-cell">
                            {$taskComment->comment}
                        </td> 
                    </tr>
                    {/foreach}
                </table>
            </div>
            <div id="anket" class="tab-pane" role="tabpanel">
                 <table class="table table-striped table-hover">
                    <tr>
                        <td style="width: 40px;"  class="jsgrid-cell">
                            №
                        </td>
                        <td style="width: 100px;"  class="jsgrid-cell">
                            Дата
                        </td>
                        <td style="width: 120px;"  class="jsgrid-cell">
                            Сотрудник
                        </td>
                        <td class="jsgrid-cell">
                            Вопрос менеджера
                        </td>
                        <td class="jsgrid-cell">
                            Ответ клиента
                        </td>
                    </tr>
                    {foreach $task->anket AS $anket}
                    <tr>
                        <td class="jsgrid-cell">
                            {$anket->id}
                        </td>
                        <td class="jsgrid-cell">
                            {$anket->dateCreate|date}
                        </td>
                        <td class="jsgrid-cell">
                            {$managers[$anket->managerId]->name}
                        </td>
                        <td class="jsgrid-cell">
                            {$anket->question}
                        </td> 
                        <td class="jsgrid-cell">
                            {$anket->answer}
                        </td> 
                    </tr>
                    {/foreach}
                </table>
            </div>