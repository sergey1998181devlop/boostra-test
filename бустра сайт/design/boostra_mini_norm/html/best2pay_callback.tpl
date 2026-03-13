{capture name='page_scripts'}

<script>
    $(function(){

        var cardAttach = $(".card").val();

        if (cardAttach === 'true') {
            $('#redirect').css('display', 'none');
        }

        if ($('#redirect').length > 0)
        {
            var _timer = 2;
            var _interval = setInterval(function(){
                if (_timer > 0)
                {
                    $('#redirect').html('Вы будете перенаправлены через '+_timer+' сек.');
                }
                else
                {
                    delete localStorage.prolongation_link
                    let card = $(".card").val()
                    let grace = $(".grace").val()
                    let payment_id = $(".payment_id").val()
                    let card_pan = $('.card_pan').val()
                    let new_card_id = $('.new_card_id').val()
                  let payment_refuser = $('.payment_refuser').val()
                  let redirect_link = $('.redirect_link').val()
                  if (payment_refuser) {
                    localStorage.payment_refuser = 1
                  }
                    if (grace) {
                        let date = new Date();
                        let year = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
                        date.setMinutes(date.getMinutes() + 30);
                        let formattedTime = date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes();
                        if (localStorage.graceValue) {
                            localStorage.graceData = JSON.stringify({
                                payment_id:payment_id,
                                expired_time:year + " " +formattedTime,
                            })
                        }
                    }
                    if (localStorage.openCardModal === "false" && card){
                        localStorage.cardPan  = card_pan
                        localStorage.newCardId  = new_card_id
                    }

                        location.href = redirect_link;
                        clearInterval(_interval);
                }
                _timer--;
console.info(_timer)
            }, 1000);
        }
    });

    {if $page_type}
        {if $success}window.postMessage('{$page_type}_success');{/if}
        {if $error}window.postMessage('{$page_type}_error');{/if}
    {/if}

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
                    <input type = "hidden" class="card" value="{$card_attach}">
                    <input type = "hidden" class="card_pan" value="{$card_pan}">
                    <input type = "hidden" class="new_card_id" value="{$new_card_id}">
                    <input type = "hidden" class="grace" value="{$grace}">
                    <input type = "hidden" class="payment_id" value="{$payment_id}">
                    <input type = "hidden" class="redirect_link" value="{if !empty($redirect_link)}{$redirect_link}{else}{'/user'}{/if}">
                    <input type="hidden" class="payment_refuser" value="{$payment_refuser}">
                    <p id="redirect" class="callback_redirect" style="text-align:center"></p>
                </div>
                
			</div>
			
            
            
		</div>
	</div>
</section>
