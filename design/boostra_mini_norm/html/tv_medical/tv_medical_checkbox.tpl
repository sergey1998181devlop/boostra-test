{if !$notOverdueLoan}
    {assign var="shouldChecked" value=$showExtraService['tv_medical']['enable']}
    {assign var="shouldShow" value=$showExtraService['tv_medical']['show']}

    {if $applied_promocode->disable_additional_services || (!empty($last_order_data) && isset($last_order_data['disable_additional_service_on_issue']) && $last_order_data['disable_additional_service_on_issue'] == 1)}
        <input type="hidden" value="0" id="tv_medical_check{$idkey}" name="tv_medical_check"/>
    {else}
        <div id="credit_doctor_check_wrapper" style="{if !$shouldShow}display:none;{else}{/if}">
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input type="checkbox" value="1" id="tv_medical_check{$idkey}" name="tv_medical_check"
                           {if $shouldChecked}checked{/if}/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p {if $currentPage == 'user'}{else}style="padding-left: 20px;"{/if}>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с приобретением ПО «ВитаМед»
                стоимостью
                <span class="tv_medical_amount">{$tv_medical_price}</span>
                рублей,
                предоставляемой в соответствии с <a href="user/docs?action=additional_service_tv_medical"
                                                    target="_blank">заявлением
                    о предоставлении дополнительных продуктов</a> и
                <a href="/files/doc/dogovor-oferta-cd.pdf" target="_blank">офертой</a>.
            </p>
        </div>
    {/if}
        <script>
            $(document).ready(function () {
                function updateHiddenField() {
                    if ($('#tv_medical_check{$idkey}').is(':checked')) {
                        $('#tv_medical_hidden{$idkey}').val('1');
                    } else {
                        $('#tv_medical_hidden{$idkey}').val('0');
                    }
                }

                updateHiddenField();

                $('#tv_medical_check{$idkey}').on('change', function () {
                    updateHiddenField();
                });

            });
        </script>
    {/if}
