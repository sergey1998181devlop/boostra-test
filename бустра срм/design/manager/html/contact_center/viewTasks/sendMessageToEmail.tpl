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
                    <div class="form-body"  id="task-info">
                                <h3 class="card-title">
                                    <span>Информация по задаче "{$meta_title}"</span>
                                </h3>
                                <hr>
                                {include file="../tasksUserBlock.tpl"}
                                <hr>
                                <div class="row">
                                    <div class="col-8">
                                        <textarea id="message" class="form-control"></textarea>
                                    </div>
                                    <div class="col">
                                        {if $task->taskStatus != 2}
                                        <button class="form-control" onclick="task.sendMessageToEmail(task.getValue('message'), false, 'RE ответ на обращение №{$taskInfo->ticketId}');task.clearInput('message');task.openBlock('taskСompleted');task.closeBlock('task-info');">Отправить сообщение</button>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                            <div id="taskСompleted" style="justify-content: center; text-align: center;">
                                <button type="button" class=" btn waves-effect waves-light" 
                                    style="margin: 2px; background: #55CE63; min-width: 120px; color: white;
                                    font-weight: bold; font-size: 12px; padding: 5px;"
                                    onclick="task.closeTask({$taskInfo->id});survey.closeBlock(survey.taskСompleted);">
                                        Задача выполнена
                                </button>
                            </div>
                </div>
            </div>
            {include file="./tasksFilds.tpl"}
        </div>
    </div>
    {include file="../modalWindowsByTasks.tpl"}
    {include file='footer.tpl'}  
</div>