<style>
        #consultation_block {
            border:2px solid #222;
            border-radius:15px;
            margin:20px 0;
        }
        #consultation_block_inner {
            padding:15px 30px;
            display:flex;
            align-items:center;
            justify-content:space-between;
        }
        #consultation_block_inner > div {
            font-size:1.5rem
        }
        #consultation_block_inner .alert {
            width:100%;
            text-align:center;
            margin:0;
        }
        #consultation_form {
            background:#fff;
            max-width: 600px;
            margin: 0 auto;
            border-radius: 20px;
        }
        @media (max-width:480px) 
        {
            #consultation_block_inner {
                display:block;
                text-align:center;
            }
        }
    </style>
    <div id="consultation_block">
        <div id="consultation_block_inner">
            <div>Хочешь избавиться от долгов за полгода под руководством профессионального консультанта?</div>
            <input type="hidden" name="user_id" value="{$user->id}" />
            <button class="button" id="consultation_form_open" type="submit">Получить бесплатную финансовую консультацию</button>
        </div>
        
        <div class="">
            <form method="POST" class="white-popup mfp-hide" id="consultation_form">
            <section id="worksheet">
				<div id="steps">
					
					
					<fieldset style="display: block;;">
                        
                        <input type="hidden" value="{$user->id}" name="user_id" />
                        
                        <div class="clearfix">
                        
                            <span class="subtitle">Для лучшего понимания Вашей ситуации просим заполнить небольшую анкету</span>
                            
                        <div class="alert" style="display:none"></div>
                        
    						<label id="" class="big readonly">
                                <input type="text" name="fio" value="" placeholder="" required=""/>
                                <span class="floating-label">ФИО</span>
    						</label>
                            
                        </div>
                        
                        <div class="clearfix">
                        
    						<label id="" class=" readonly">
                                <input type="text" name="age" value="" placeholder="" required=""/>
                                <span class="floating-label">Сколько полных лет</span>
    						</label>
                            
    						<label id="" class=" readonly">
                                <input type="text" name="income" value="" placeholder="" required=""/>
                                <span class="floating-label">Средний доход в месяц (приблизительно, среднегодовой)</span>
    						</label>
                            
    						<label id="" class=" readonly">
                                <input type="text" name="expense" value="" placeholder="" required=""/>
                                <span class="floating-label">Средний расход в месяц (без долговой нагрузки)</span>
    						</label>
                            
                        </div>
                        
                        <div class="clearfix">                                
                        
                            <label class="half">
                                <div class="select">
                                    <select name="debt_hole">
                                        <option value="" selected="">Выберите нужное</option>
                                        <option value="Меньше года" >Меньше года</option>
                                        <option value="От года до 3-х лет">От года до 3-х лет</option>
                                        <option value="От 3-х о 5-ти лет">От 3-х о 5-ти лет</option>
                                        <option value="Более 5-ти лет">Более 5-ти лет</option>
                                    </select>
                                </div>
                                <span class="floating-label">Период нахождения в долговой яме</span>
                            </label>
                        
                            <label class="half">
                                <div class="select">
                                    <select name="situation">
                                        <option value="" selected="">Выберите нужное</option>
                                        <option value="Растет">Растет</option>
                                        <option value="Не изменяется">Не изменяется</option>
                                        <option value="Уменьшается">Уменьшается</option>
                                    </select>
                                </div>
                                <span class="floating-label">Что происходит ежемесячно с общей суммой Вашего долга</span>
                            </label>
                            
                        </div>
                        
                        <div class="clearfix">
                            
    						<label id="" class="half readonly">
                                <input type="text" name="debt_total" value="" placeholder="" required=""/>
                                <span class="floating-label">Обшая сумма основного долга (сумма всех долгов на сегодняшнюю дату)</span>
    						</label>
                            
    						<label id="" class="half readonly">
                                <input type="text" name="debt_month" value="" placeholder="" required=""/>
                                <span class="floating-label">Сколько платите ежемесячно по долгам (сумма в рублях)</span>
    						</label>
                            
                        </div>
                        
                        <div class="clearfix">
                                         
                            <label class="half">
                                <div class="select">
                                    <select name="max_percent">
                                        <option value="" selected="">Выберите нужное</option>
                                        <option value="До 50%">До 50%</option>
                                        <option value="До 50%, но меньше 100%">До 50%, но меньше 100%</option>
                                        <option value="100%">100%</option>
                                    </select>
                                </div>
                                <span class="floating-label">Я могу выплачивать только часть ежемесячных платежей по долгам</span>
                            </label>
                        
                            <label class="half">
                                <div class="select">
                                    <select name="have_delay">
                                        <option value="" selected="">Выберите нужное</option>
                                        <option value="Да, все ежемесячные платежи оплачиваю вовремя, без просрочек">Да, все ежемесячные платежи оплачиваю вовремя, без просрочек</option>
                                        <option value="Нет не хватает, есть просрочки">Нет не хватает, есть просрочки</option>
                                    </select>
                                </div>
                                <span class="floating-label">Хватает ли денег на выплату всех ежемесячных долговых обязательств</span>
                            </label>
                        
                        </div>
                        
						<div class="next">
                            <button class="button big" id="doit" type="submit" name="neworder">Отправить</button>							
						</div>
					</fieldset>
				</div>
                </section>
            </form>
        </div>
    </div>
    <script>
        $(function(){
            
            $('#consultation_form_open').click(function(e){
                e.preventDefault();
                $.magnificPopup.open({
            		items: {
            			src: '#consultation_form'
            		},
            		type: 'inline',
                    showCloseBtn: true
            	});
            });
            
            $('#consultation_form').submit(function(e){
                e.preventDefault();
                
                var $form = $(this);
                $.ajax({
                    url:'ajax/consultation.php',
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
                            $form.html(_m);
                            $form.removeClass('loading');
                        }
                    }
                })
            })
            
            
        });
    </script>