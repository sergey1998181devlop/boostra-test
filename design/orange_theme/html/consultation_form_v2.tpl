<style>
        #consultation_block_v2 {
            margin:20px 0;
        }
        #consultation_block_v2_inner {
            padding:15px 0px;
            display:flex;
            align-items:center;
            justify-content:end;
        }
        #consultation_block_v2_inner > div {
            font-size:1.5rem
        }
        #consultation_block_v2_inner .alert {
            width:100%;
            text-align:center;
            margin:0;
        }
        #consultation_form_v2 {
            background:#fff;
            max-width: 300px;
            margin: 0 auto;
            border-radius: 20px;
        }
        #consultation_form_v2_open {
            width:220px;
            font-size: 1.2rem;
            height: 40px;
            border-radius: 20px;
        }
        #consultation_form_v2 .subtitle {
            font-size:1.5rem;
            line-height:1.7rem;
            font-weight:bold;
            margin-top:10px;
        }
        #consultation_form_v2 .subtitle-info {
            font-size:1.5rem;
            padding: 10px 15px 0 15px;
            font-weight:400;
        }
        #consultation_form_v2 .form-input {
            border: 2px solid #999;
            border-radius:20px;
            padding: 7px 15px;
            margin: 10px 0; 
            font-size:16px;
            color:#333;
            width:240px;
        }
        #consultation_form_v2 .button {
            margin:10px 0;
            width:240px;
            ;
        }
        #consultation_form_v2 .alert {
            margin: 10px 15px;
        }
        @media (max-width:480px) 
        {
            #consultation_block_v2_inner {
                display:block;
                text-align:center;
            }
        }
    </style>
    <div id="consultation_block_v2">
        <div id="consultation_block_v2_inner">
            <input type="hidden" name="user_id" value="{$user->id}" />
            <button class="button" id="consultation_form_v2_open" type="submit">Избавление от долгов</button>
        </div>
        
        <div class="">
            <form method="POST" class="white-popup mfp-hide" id="consultation_form_v2">
                <section id="worksheet">
				    <div id="">
						
                        <input type="hidden" value="{$user->id}" name="user_id" />
                        
                        <div class="subtitle">
                            Проблемы с долгами?
                            <br />
                            Их можно списать!
                        </div>
                        
                        <div class="subtitle-info">
                            Заполните заявку и узнайте как это сделать
                        </div>
                        
                        <div class="alert" style="display:none"></div>
                        
						<div class="form-inner">
                            <label id="" class="big readonly">
                                <input type="text" name="fio" class="form-input" value="" placeholder="Имя" required=""/>
    						</label>
                        
    						<label id="" class="big readonly">
                                <input type="text" name="phone" class="form-input" value="" placeholder="Контакный телефон" required=""/>
    						</label>
                                                    
                            <button class="button big" type="submit" >Отправить заявку</button>							
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
    <script>
        $(function(){
            
            $('#consultation_form_v2_open').click(function(e){
                e.preventDefault();
                $.magnificPopup.open({
            		items: {
            			src: '#consultation_form_v2'
            		},
            		type: 'inline',
                    showCloseBtn: true
            	});
            });
            
            $('#consultation_form_v2').submit(function(e){
                e.preventDefault();
                
                var $form = $(this);
                $.ajax({
                    url:'ajax/consultation_v2.php',
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
                            var _m = 'Ваша заявка отправлена. <br />Мы свяжемся с Вами в ближайшее время'
                            $form.find('.form-inner').hide();
                            $form.find('.alert').removeClass('alert-danger').addClass('alert-success').html(_m);
                            $form.removeClass('loading');
                        }
                    }
                })
            })
            
            
        });
    </script>