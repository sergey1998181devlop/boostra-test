{$meta_title='Оплата задолженности' scope=parent}

{capture name='page_scripts'}
    <script>
        function C2oApp()
        {
            var app = this;

            app.init_open_payform = function(){
                $(document).on('click', '.js-open-payform', function(e){
                    e.preventDefault();

                    $(this).hide();

                    $('.js-payform-wrapper').fadeIn();
                })

            };

            app.init_start_payform = function(){
                $(document).on('click', '.js-start-payform', function(e){
                    e.preventDefault();

                    var $form = $(this).closest('form');

                    if ($form.hasClass('loading'))
                        return false;

                    $.ajax({
                        url: '/ajax/b2p_payment.php',
                        data: $form.serialize(),
                        beforeSend: function(){
                            $form.addClass('loading');
                        },
                        success: function(resp){
                            if (resp.payment_link)
                                location.href = resp.payment_link;
                            else
                                alert(resp.error);
                        }
                    });
                })

            };

            ;(function(){
            app.init_open_payform();
            app.init_start_payform();
        })();
        }

        $(function(){
            new C2oApp();
        })
    </script>
{/capture}

{capture name='page_styles'}

{/capture}

<main class="main">
    <div class="section ">
        <div class="box" style="text-align: center">
            <br>
            <h1 class="text-center">
                Уважаемый клиент
            </h1>
            <br>
            <br>


            {$total_summ = $user_balance->ostatok_od + $user_balance->ostatok_percents + $user_balance->ostatok_peni}

            {if $total_summ > 0}
                <h3 class="text-center -red">
                    По № договора {$user_balance->zaim_number}<br />задолженность составляет {$total_summ} руб
                </h3>
                <br>
                <br>
                <div class="text-center">
                    <a href="{url}/pay" class="btn button btn-primary js-open-payform">Погасить задолженность</a>
                    <div style="display:none" class="js-payform-wrapper">
                        <div class="row">
                            <div class="col-lg-4 offset-lg-4">
                                <div class="itop_calc">
                                    <form class="js-payform" method="POST">

                                        <input type="hidden" name="number" value="{$user_balance->zaim_number}" />
                                        <input type="hidden" name="action" value="get_payment_link" />

                                        <input type="hidden" name="prolongation" value="0" />
                                        <input type="hidden" name="code_sms" value="" />

                                        <div class="form-group form-phone">
                                            <div class="form-group-title -fs-22 -gil-m text-center" for="amount-one">
                                                Оплатить займ
                                            </div>
                                        </div>
                                        <br>

                                        <div id="">
                                            <div class="form-group form-phone">
                                                <span class="phone_info -fs-14">Сумма</span>
                                                <input type="text" name="amount" id="amount" class="form-control -fs-18 -gil-m" value="{$total_summ}">
                                            </div>
                                            <br>
                                            <br>
                                            <div class="form-group form-btn">
                                                <a href="#" class="btn button btn-secondary -fs-20 -fullwidth js-start-payform">Оплатить</a>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            {else}
                <h3 class="text-center">
                    По № договора{$user_balance->zaim_number}<br />у вас нет задолженности!
                </h3>
            {/if}

        </div>
    </div>
</main>