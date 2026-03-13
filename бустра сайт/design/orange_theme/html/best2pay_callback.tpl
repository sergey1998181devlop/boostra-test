{capture name='page_scripts'}

<script>
    $(function(){
        if ($('#redirect').length > 0)
        {
            var _timer = 5;
            var _interval = setInterval(function(){
                if (_timer > 0)
                {
                    $('#redirect').html('Вы будете перенаправлены в <a href="user">личный кабинет</a> через '+_timer+' сек.');
                }
                else
                {
//                    if (!window.is_developer)
                        location.href = '/user';
                        clearInterval(_interval);
                }
                _timer--;
console.info(_timer)
            }, 1000);
        }
    });
</script>

{/capture}

{capture name='page_styles'}
    
{/capture}

<section id="info">
	<div>
		<div class="box">
			<div>
				
                <div style="text-align:center">
                
                    {if $error}<h1 class="callback_error" style="color:red;">{$error}</h1>{/if}
                    {if $success}<h1 class="callback_success" style="color:green">{$success}</h1>{/if}
                    {if $reason_code_description}<h3 class="reason_code_description">{$reason_code_description}</h3>{/if}
                    
                    <p id="redirect" class="callback_redirect" style="text-align:center"></p>
                    
                </div>
                
			</div>
			
            
            
		</div>
	</div>
</section>
