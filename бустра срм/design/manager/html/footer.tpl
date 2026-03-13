<script>

</script>
{if in_array('cc_tasks', $manager->permissions)}
    <!--
    <script src="/js/mangoDialog.js?v=1"></script>
-->
{/if}


<script src="/js/mainApps.js?v=1" type="text/javascript"></script>
<script src="/js/myTasks.js?v=1" type="text/javascript"></script>
<script src="/js/customerSurvey.js?v=1" type="text/javascript"></script>
<script src="/js/ViewImage.js?v=1" type="text/javascript"></script>
<script>
    var task = new Tasks();
    var survey = new CustomerSurvey();
    task.commentBlock = 'comment';
    task.commentBlockTaskId = 'taskId';
    task.commentTextBlock = 'commentText';
    task.commentBlockManagerId = 'managerId';
    task.userInfo = {json_encode($userInfo)};
    task.managerInfo = {json_encode($manager)};
    task.taskInfo = {json_encode($taskInfo)};
    task.creditInfo = {json_encode($creditInfo)};
    {if $ticket}
    task.tiketId = {$ticket->id};
    {/if}
    var managerInfo = {json_encode($manager)};

    async function change_rules(id) {
        let json = {
            "id": id,
            "role": $('#rules_' + id + ' option:selected').val()
        }

        const response = await fetch('/ajax/change_rule.php', {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(json) // body data type must match "Content-Type" header
        });
        let result = await response.json();
        result = JSON.parse(result)
        console.log(result); // parses JSON response into native JavaScript objects
    }


</script>
<!--
    <script src="/js/mangoQuestion.js?v=1" type="text/javascript"></script>
    -->
<footer class="footer">
    © {''|date:'Y'} Boostra Admin
</footer>


<div id="ViewImage" class="modalBlock"></div>