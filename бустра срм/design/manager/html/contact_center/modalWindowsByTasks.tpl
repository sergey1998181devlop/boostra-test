<div id="commentTask" class="modalBlock">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" onclick="task.closeBlock('commentTask');">×</button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий:</label>
                        <textarea class="form-control" name="comment" id="commentText"></textarea>
                    </div>
                    {if $task->taskStatus!==2}
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" onclick="task.closeBlock('commentTask');">Отмена</button>
                        <button type="button" class="btn btn-success waves-effect waves-light" onclick="task.saveCommentTask('commentText');">Сохранить</button>
                    </div>
                    {/if}
                </form>
            </div>
        </div>
    </div>
</div>