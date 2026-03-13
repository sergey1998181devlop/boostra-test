<h4>Выберите тариф, кликнув по одному из них:</h4>
<div id="tv_medical__wrapper">
    <ul>
        {foreach $tv_medical_tariffs as $tv_medical_tariff}
            <li>
                <input id="tv_medical_{$tv_medical_tariff@key}" {if $tv_medical_tariff->id == $tv_medical_id} checked {/if}
                       type="radio"
                       name="tv_medical_id"
                       data-amount="{$tv_medical_tariff->price}"
                       data-number="{$user->balance->zaim_number}"
                       value="{$tv_medical_tariff->id}"
                />
                <label for="tv_medical_{$tv_medical_tariff@key}">
                    <h3><b>{$tv_medical_tariff->name}</b></h3>
                    <h5>{$tv_medical_tariff->price} руб / {$tv_medical_tariff->days} дней</h5>
                    {$tv_medical_tariff->description|@html_entity_decode}
                    <button class="button button-inverse medium" type="button">выбрать</button>
                </label>
            </li>
        {/foreach}
    </ul>
    <p>
        <small>
            <span>*</span> По умолчанию, если Вы не выбрали тариф, активным становится тот, который был у Вас на момент подписания заявления.
        </small>
    </p>
    <p>
        <small>
            (<span>*</span>) - Цифра в скобках - это количество возможных обращений.
        </small>
    </p>
</div>

{literal}
    <style>
        #tv_medical__wrapper h3 {
            margin-bottom: 10px;
        }

        #tv_medical__wrapper p span {
            font-family: initial;
        }

        #tv_medical__wrapper > ul {
            padding-left: 0;
            display: flex;
            max-width: 100%;
            list-style: none;
            gap: 5px;
        }

        #tv_medical__wrapper > ul > li {
            overflow-wrap: anywhere;
            flex: 1 1 0;
        }

        #tv_medical__wrapper label {
            text-align: center;
            display: grid;
            height: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 3px solid rgba(4, 43, 57, 0.1);
            box-sizing: border-box;
            cursor: pointer;
        }

        #tv_medical__wrapper [name="tv_medical_id"]:checked:not(:disabled) + label {
            border: 3px solid rgba(4, 43, 57, 1);
        }

        #tv_medical__wrapper [name="tv_medical_id"]:disabled + label {
            cursor: no-drop;
            opacity: .5;
        }

        #tv_medical__wrapper label ul {
            text-align: left;
            margin: 20px 0;
            font-size: 13px;
            padding-left: 20px;
            position: relative;
            box-sizing: border-box;
        }

        #tv_medical__wrapper label ul li {
            margin: 10px 0;
            list-style-position: outside;
            padding-left: 10px;
        }

        #tv_medical__wrapper label ul li::marker {
            font-size: 16px;
        }

        #tv_medical__wrapper label ul > li::marker {
            font-family: "bootstrap-icons" !important;
        }

        #tv_medical__wrapper label ul > li:first-child::marker {
            content: '\F417';
        }

        #tv_medical__wrapper label ul > li:nth-child(2)::marker {
            content: '\F7DF';
        }

        #tv_medical__wrapper label ul > li:last-child::marker {
            content: '\F293';
        }

        #tv_medical__wrapper [name="tv_medical_id"] {
            display: none;
        }

        #tv_medical__wrapper label button {
            margin: auto;
            display: flex;
            text-transform: uppercase;
            border: 3px solid;
        }

        @media screen and (max-width: 768px) {
            #tv_medical__wrapper label ul li {
                font-size: .85rem;
                padding-left: 5px;
            }
            #tv_medical__wrapper label ul {
                margin: 10px 0;
                padding-left: 10px;
            }
            #tv_medical__wrapper label ul li::marker {
                font-size: 12px;
            }
            #tv_medical__wrapper label button {
                border: 2px solid;
                overflow-wrap: break-word;
                padding: 5px;
                width: 100%;
                text-align: center;
                justify-content: center;
            }
            #tv_medical__wrapper > ul {
                flex-flow: column;
            }
        }
    </style>
    <script>
        $('[name="tv_medical_id"]').on('change', function () {
            let amount = $(this).data('amount'),
                number = $(this).data('number'),
                id = $(this).val();

            $("#prolongation_sms_block [name='tv_medical_id']").val(id);
            $("#prolongation_sms_block [name='tv_medical_amount']").val(amount);
            $(".tv_medical__amount_text").text(amount);

            // если выбрана тв медицина выполним перерасчет
            if (!!$("#prolongation_sms_block [name='tv_medical']").val()) {
                prolongationRefreshAmount(number);
            }
        });
    </script>
{/literal}
