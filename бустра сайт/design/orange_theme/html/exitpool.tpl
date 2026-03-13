{$view_banner = 1}

<style>
#exitpool_block {
    border:2px solid #222;
    border-radius:15px;
    margin:20px 0;
}
#exitpool_block_inner {
    padding:15px 30px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
#exitpool_block_inner > div {
    font-size:1.5rem
}
#exitpool_block_inner .alert {
    width:100%;
    text-align:center;
    margin:0;
}
#exitpool_form {
//    background:#fff;
    max-width: 400px;
    margin: 0 auto;
//    border-radius: 20px;
}
#exitpool_form .title {
    font-size: 1.3rem !important;
    text-align: center !important;
    margin-bottom: 10px;
}
#exitpool_form .subtitle {
    font-size: 1.0rem !important;
    text-align: center !important;
    padding-bottom: 15px;
    display:block;
}

.rating-area {
	overflow: hidden;
	width: 270px;
	margin: 0.5rem auto;
    direction:rtl;    
}
.rating-area:not(:checked) > input {
	display: none;
}
.rating-area:not(:checked) > label {
	float: right;
	width: 42px;
	padding: 0;
	cursor: pointer;
	font-size: 32px;
	line-height: 32px;
	color: lightgrey;
	text-shadow: 1px 1px #bbb;
}
.rating-area:not(:checked) > label:before {
	content: '★';
}
.rating-area > input:checked ~ label {
	color: gold;
	text-shadow: 1px 1px #c60;
}
.rating-area:not(:checked) > label:hover,
.rating-area:not(:checked) > label:hover ~ label {
	color: gold;
}
.rating-area > input:checked + label:hover,
.rating-area > input:checked + label:hover ~ label,
.rating-area > input:checked ~ label:hover,
.rating-area > input:checked ~ label:hover ~ label,
.rating-area > label:hover ~ input:checked ~ label {
	color: gold;
	text-shadow: 1px 1px goldenrod;
}
.rating-area > label:active {
	position: relative;
}
.rating-area  label {
    float: right!important;
    width:auto!important;
    padding:0!important;
    margin: 0!important;
    display:inline-block;
    transition:all 0.3s;
}

@media (max-width:480px) 
{
    #exitpool_block_inner {
        display:block;
        text-align:center;
    }
}
        
    </style>

    <div class="hidden">
        <div id="exitpool_block">        
        
            <form method="POST" class="white-popup mfp-hide" id="exitpool_form">
                <section id="worksheet">

                    {if $view_banner}
                        <div style="border-radius:20px;background:#fff;display:block;padding:10px;">
                            <h3 style="font-weight:normal;padding-bottom:10px;">
                                Поздравляем!
                                <br />
                                Вы можете принять участие в Розыгрыше
                            </h3>
                            <h2 class="text-red">100 000 руб.</h2>
                            <img src="design/{$settings->theme|escape}/img/banner_promo100000.png" style="max-width:100%;" />
                            <a class="button big" style="margin-top:20px" type="button" href="promo100000">Подробнее</a>
                        </div>
    				{else}

    				<div id="steps">
    					
    					<fieldset style="display: block;;">
                            
                            <input type="hidden" value="{$user->id}" name="user_id" />
                            
                            <div class="clearfix">
                            
                                <span class="title">Пожалуйста, помогите нам сделать сервис лучше, ответив на несколько вопросов</span>
                                <span class="subtitle">Оцените от 1 до 10, где 1-очень плохо, 10-очень хорошо</span>
                                
                                <div class="alert" style="display:none"></div>
                            
                            </div>
                            
                            <div class="clearfix">
                            
        						{foreach $exitpool_questions as $question}
                                <div id="" class="big readonly">
                                    <span class="floating-label">{$question->question}</span>
                                     <div class="rating-area">
                                    	<input type="radio" id="question-{$question->id}-star-10" name="question[{$question->id}]" value="10">
                                    	<label class="" for="question-{$question->id}-star-10" title="Оценка «5»"></label>	
                                    	<input type="radio" id="question-{$question->id}-star-9" name="question[{$question->id}]" value="9">
                                    	<label for="question-{$question->id}-star-9" title="Оценка «9»"></label>    
                                    	<input type="radio" id="question-{$question->id}-star-8" name="question[{$question->id}]" value="8">
                                    	<label for="question-{$question->id}-star-8" title="Оценка «8»"></label>  
                                    	<input type="radio" id="question-{$question->id}-star-7" name="question[{$question->id}]" value="7">
                                    	<label for="question-{$question->id}-star-7" title="Оценка «7»"></label>    
                                    	<input type="radio" id="question-{$question->id}-star-6" name="question[{$question->id}]" value="6">
                                    	<label for="question-{$question->id}-star-6" title="Оценка «6»"></label>
                                    	<input type="radio" id="question-{$question->id}-star-5" name="question[{$question->id}]" value="5">
                                    	<label class="" for="question-{$question->id}-star-5" title="Оценка «5»"></label>	
                                    	<input type="radio" id="question-{$question->id}-star-4" name="question[{$question->id}]" value="4">
                                    	<label for="question-{$question->id}-star-4" title="Оценка «4»"></label>    
                                    	<input type="radio" id="question-{$question->id}-star-3" name="question[{$question->id}]" value="3">
                                    	<label for="question-{$question->id}-star-3" title="Оценка «3»"></label>  
                                    	<input type="radio" id="question-{$question->id}-star-2" name="question[{$question->id}]" value="2">
                                    	<label for="question-{$question->id}-star-2" title="Оценка «2»"></label>    
                                    	<input type="radio" id="question-{$question->id}-star-1" name="question[{$question->id}]" value="1">
                                    	<label for="question-{$question->id}-star-1" title="Оценка «1»"></label>
                                    </div>                               
                                </div>
                                {/foreach}
                                
                            </div>
                            
    						<div class="next">
                                <button class="button big" id="doit" type="submit" name="neworder">Отправить</button>							
    						</div>
    					</fieldset>
    				</div>
                        {/if}
                </section>
            </form>
        </div>
    </div>
    <script>
    
        function ExitpoolApp(_order_id)
        {
            var app = this;
console.log(_order_id);
            
            app.open = function(){
                $.magnificPopup.open({
            		items: {
            			src: '#exitpool_form'
            		},
            		type: 'inline',
                    showCloseBtn: true,
                    callbacks: {
                      beforeClose: function() {
                        location.reload()
                      },
                    }
            	});
            };
            
            var _init_submit = function(){
                $('#exitpool_form').submit(function(e){
                    e.preventDefault();
                    
                    $.magnificPopup.close();
                    
                    var $form = $(this);
                    $.ajax({
                        url:'ajax/exitpool.php',
                        data: $form.serialize(),
                        type: 'POST',
                        beforeSend: function(){
                            $form.addClass('loading');
                        },
                        success: function(resp){
                            if (resp.error)
                            {
                                $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn()
                                $form.removeClass('loading');
                            }
                            else
                            {
                                var _m = '<div class="alert alert-success">Ваша заявка отправлена. Мы свяжемся с Вами в ближайшее время</div>'
                                $form.find('.alert').addClass('alert-success').removeClass('alert-danger').html(_m);
                                $form.removeClass('loading');
                                location.reload()
                            }
                        }
                    })
                })
                
            };
            
            var _init_click = function(){
                $('#rozigrysh_link').click(function(){
                    location.reload();
                    $.magnificPopup.close();
                })
            };
            
            ;(function(){
                app.open();
                _init_submit();
                _init_click();
            })();
        }
    
    
    
    
        $(function(){
            
            $('#exitpool_form_open').click(function(e){
                e.preventDefault();
                $.magnificPopup.open({
            		items: {
            			src: '#exitpool_form'
            		},
            		type: 'inline',
                    showCloseBtn: true
            	});
            });
            
        });
    </script>