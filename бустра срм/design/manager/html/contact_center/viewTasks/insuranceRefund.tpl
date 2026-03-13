{$meta_title=$taskNames[$taskTypes[$taskInfo->taskType]] scope=parent}
<div class="page-wrapper" id="page_wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i>{$meta_title}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">
                        Задача №{$taskInfo->id} "{$meta_title}"
                        <a href="javascript:void(0);" onclick="task.openBlock('commentTask');" title="Добавить комментарий к задаче">
                            <i class="mdi mdi-comment-text"></i>
                        </a>
                    </li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
            </div>
        </div>
        {include file="./headerTasksFilds.tpl"} 
        <div class="tab-content ">
            <div id="task" class="tab-pane active" role="tabpanel">
                <div id="basicgrid" class="jsgrid">
                    <div class="form-body">
                        <h3 class="card-title">
                            <span>Информация по задаче "{$meta_title}"</span>
                        </h3>
                        <hr>
                        <div class="row">
                                    <div class="col">
                                        Дата обращения: 
                                        <p>
                                            {$task->dateCreate|date}
                                        </p>
                                    </div>
                                    <div class="col">
                                        Канал обращения: 
                                        <p>
                                            {$task->inputChanel}
                                        </p>
                                    </div>
                                    <div class="col">
                                        ФИО принявшего обращение: 
                                        <p>
                                            {$task->accept_fio}
                                        </p>
                                    </div>
                                </div>
                        <hr>
                        {include file="../tasksUserBlock.tpl"}
                        <hr>
                        {include file="../taskCreditInfo.tpl"}
                        <div class="row">
                                    <table class="table table-striped table-hover">
                                        <tr>
                                            <td style="width: 40px;" class="jsgrid-cell">
                                                №
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                Наименование страховщика
                                            </td>
                                            <td class="jsgrid-cell">
                                                Номер полиса
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                Дата страхования
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                Сумма страховки
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                Дата возврата
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                Сумма возврата
                                            </td>
                                            <td style="width: 120px;" class="jsgrid-cell">
                                                Тип страховки к возврату
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                Метод возврата
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                Статус
                                            </td>
                                        </tr>
                                    {foreach $task->insurances as $key=>$insurance}
                                        <tr>
                                            <td class="jsgrid-cell">
                                                {$step=$key+1}
                                                {$step}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$insurance->organizationName}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$insurance->number}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$insurance->startDate|date}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$insurance->summ}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$insurance->endDate|date}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$insurance->returnSumm}
                                            </td>
                                            <td class="jsgrid-cell">
                                                
                                            </td>
                                            <td class="jsgrid-cell">
                                                
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$insurance->status}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </table>
                                </div>
                        <hr>
                        <div class="row">
                            <div class="col">
                                Ответственный:
                                <p>
                                    {$manager->name}
                                </p>
                            </div>
                            <div class="col">
                                Дедлайн:
                                <p>
                                    {$task->close_date|date}
                                </p>
                            </div>
                            <div class="col">
                                Активированные опции:
                                <p>
                                          
                                </p>
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
            {include file="./tasksFilds.tpl"}
        </div>
    </div>
    {include file="../modalWindowsByTasks.tpl"}
    {include file='footer.tpl'}  
</div>