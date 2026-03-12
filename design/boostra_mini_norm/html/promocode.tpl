{if $promo_block !== Promocodes::MODE_NONE}
    <style>
        .promocodes a:hover {
            text-decoration: none;
        }
        .promocodes {
            min-height: 48px;
            .promo_code_alert {
                color: #0a3622;
                background: #d1e7dd;
                border: 1px solid #a3cfbb;
                border-radius: 3px;
                padding: 10px 5px;
                margin-top: 10px;
            }
        }
    </style>
    <div class="promocodes">
        {if $promo_block === Promocodes::MODE_APPLY}
            <div style="font-style: normal; color: #959595;">
                Вы применили промокод
                {if $promo_code}
                    <b>{$promo_code}</b>
                {/if}
            </div>
        {else}
            <div style="display: flex; margin: 15px 0 0;">
                <a
                    href="#"
                    id="promo-title-link"
                    onclick="return onPromoTitleClick(this);"
                    style="font-style: normal;
                            font-weight: 600;
                            color: #038AEE;
                            font-size: 16px;
                            border: 2px #038AEE solid;
                            box-sizing: border-box;
                            border-radius: 232px;
                            min-width: clamp(220px, -146.5px + 111.111vw, 320px);
                            display: inline-block;
                            text-align: center;
                            padding: .95rem 2.8rem;
                            line-height: 1;
                        "
                >У меня есть промокод</a>
            </div>
            <div id="promocode-applied" style="font-style: normal; color: #959595; display: none;">
                Вы применили промокод
                {if $promo_code}
                    <b>{$promo_code}</b>
                {/if}
            </div>
            {if $promo_block === Promocodes::MODE_BANNER}
                <div class="promo-block" style="display: none; font-size: 18px; margin-top: 10px;">
                    Поздравляем! Вам доступны льготные условия займа! Отправьте заявку на заём.
                    <br/>После её одобрения у Вас появится возможность применить промокод.
                </div>
                <script>
                    function onPromoTitleClick(elem) {
                        $('.promo-block').show();
                        return false;
                    }
                </script>
            {elseif $promo_block === Promocodes::MODE_FORM}
                <div class="promo-block" style="display: none;">
                    <input
                        value="{if $smarty.cookies.promocode}{$smarty.cookies.promocode}{/if}"
                        type="text"
                        name="promocode"
                        placeholder="Введите промокод"
                        style="border: 2px solid #000;
                                border-radius: 0.5rem;
                                padding: 0.8rem;
                                text-align: center;
                                margin-right: 15px;
                                text-transform: uppercase;"
                    />
                    <button
                        type="button"
                        class="big"
                        style="border-radius: 1rem; background-color: #0a53be; border: none;"
                        onclick="applyPromocode(); return false;"
                    >Применить</button>
                </div>
                <div id="promocode-alert" style="font-style: normal; color: #f00; display: none;">
                    Промокод отсутствует в системе
                </div>
                {if $module == 'AutoConfirmAspView'}
                    <div class="promo_code_alert" style="display: none;">
                        <h5>Промокод <b></b> применен. Скидка активирована!</h5>
                    </div>
                {/if}
                <script>
                    function onPromoTitleClick(elem) {
                        $('.promo-block').show();
                        $(elem).hide();
                        return false;
                    }
                    function applyPromocode() {
                        var code = document.querySelector('input[name="promocode"]').value.trim();
                        var info_field = document.querySelector('#full-loan-info');
                        var old_contract   = document.querySelector('#old_contract');
                        var gray_contract  = document.querySelector('#gray_contract');
                        var green_contract = document.querySelector('#green_contract');

                        if(code) {
                            $("body").addClass('is_loading');
                            $.post('/ajax/promocodes.php', { code: code })
                            .done(function(json) {
                                if (json.success) {

                                    {if $module == 'AutoConfirmAspView'}
                                        $(".promo_code_alert").show();
                                        $(".promo_code_alert b").text(code);
                                    {else}
                                        if(info_field) {
                                            info_field.dataset.percent   = json.promocode.percent;
                                            info_field.dataset.promocode = json.promocode.id;

                                            changeSliderStyles();
                                            updateFullLoanInfo(document.querySelector('#money-edit').value)
                                        } else if ($('#calculator').length) {
                                            if ($('#percent').length && json.promocode.percent !== undefined) {
                                                $('#percent').val(json.promocode.percent);
                                            }
                                            if ($('#max_period').length && json.promocode.limit_term) {
                                                $('#max_period').val(json.promocode.limit_term);
                                            }
                                            if (typeof calculate === 'function') {
                                                try { calculate(); } catch(e) {}
                                            }
                                        }

                                        if(old_contract) {
                                            var link_parts = old_contract.href.split('/');
                                            link_parts.pop();
                                            link_parts.pop();
                                            link_parts.push(json.promocode.contract);
                                            if(old_contract) {
                                                old_contract.href = link_parts.join('/');
                                            }
                                            if(gray_contract) {
                                                gray_contract.href = link_parts.join('/');
                                            }
                                            if(green_contract) {
                                                green_contract.href = link_parts.join('/');
                                            }
                                        }
                                    {/if}

                                    $('.promo-block').hide();
                                    $('#promo-title-link').hide();
                                    $('#promocode-alert').hide();
                                    $('#promocode-applied').show();
                                } else {
                                    document.querySelector('input[name="promocode"]').style.borderColor = "#f00";
                                    document.querySelector('input[name="promocode"]').style.color = "#f00";
                                    $('#promocode-alert').show();
                                }
                                $("body").removeClass('is_loading');
                            });
                        }
                    }

                    {if $smarty.cookies.promocode}
                        $(document).ready(function () {
                            applyPromocode();
                        });
                    {/if}
                </script>
            {/if}
        {/if}
    </div>
{/if}