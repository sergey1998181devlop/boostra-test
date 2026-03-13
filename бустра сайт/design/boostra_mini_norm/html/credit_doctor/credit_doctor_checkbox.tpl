{if !$notOverdueLoan}
    {assign var="shouldChecked" value=$showExtraService['financial_doctor']['enable']}
    {assign var="shouldShow" value=$showExtraService['financial_doctor']['show']}

    {if $applied_promocode->disable_additional_services || (!empty($last_order_data) && isset($last_order_data['disable_additional_service_on_issue']) && $last_order_data['disable_additional_service_on_issue'] == 1)}
        <input type="hidden" value="0" id="credit_doctor_check{$idkey}" name="credit_doctor_check"/>
    {else}
        <div id="credit_doctor_check_wrapper" style="{if !$shouldShow}display:none;{else}{/if}">
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input type="checkbox" value="1" id="credit_doctor_check{$idkey}" name="credit_doctor_check"
                           {if $shouldChecked}checked{/if}/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с приобретением ПО «Финансовый доктор»
                СВФСИС №20156 стоимостью {$credit_doctor_amount}
                рублей, предоставляемой в соответствии с
                <a href="user/docs?action=additional_service" target="_blank">
                    заявлением о предоставлении дополнительных продуктов</a> и
                <a href="/files/doc/dogovor-oferta-cd.pdf" target="_blank">офертой</a>.
                <a type="button" class="btn btn-prolongation" id="btn-modal-creditdoctor">Подробнее</a>
            </p>
        </div>
    {/if}
    <script>
        $(document).ready(function () {
            function updateHiddenField() {
                if ($('#credit_doctor_check{$idkey}').is(':checked')) {
                    $('#credit_doctor_hidden{$idkey}').val('1');
                } else {
                    $('#credit_doctor_hidden{$idkey}').val('0');
                }
            }

            updateHiddenField();

            $('#credit_doctor_check{$idkey}').on('change', function () {
                updateHiddenField();
            });
        });
    </script>
{/if}
