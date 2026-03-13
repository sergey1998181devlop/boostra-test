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
                <div class="form-body">
                    <h3 class="card-title">
                        <span>Информация по задаче "{$meta_title}"</span>
                    </h3>
                    <hr>
                    {include file="../tasksUserBlock.tpl"}
                    <hr>
                    <div class="row">
                        <div id="surveyForTheTask">
                            Позвонить клиенту
                            <div id="Question">
                                
                            </div>
                            <div id="Answers">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {include file="./tasksFilds.tpl"}
        </div>
    </div>
    {include file="../modalWindowsByTasks.tpl"}
    {include file='footer.tpl'}  
</div>   

