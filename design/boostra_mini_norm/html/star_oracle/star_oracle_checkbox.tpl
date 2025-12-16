{if !$notOverdueLoan}
    {assign var="shouldChecked" value=$showExtraService['star_oracle']['enable']}
    {assign var="shouldShow" value=$showExtraService['star_oracle']['show']}

    {if $applied_promocode->disable_additional_services || (!empty($last_order_data) && isset($last_order_data['disable_additional_service_on_issue']) && $last_order_data['disable_additional_service_on_issue'] == 1)}
        <input type="hidden" value="0" id="star_oracle_check{$idkey}" name="star_oracle_check"/>
    {else}
        <div id="credit_doctor_check_wrapper" style="{if !$shouldShow}display:none;{else}{/if}">
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input type="checkbox" value="1" id="star_oracle_check{$idkey}" name="star_oracle_check"
                           {if $shouldChecked}checked{/if}/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p {if $currentPage == 'user'}{else}style="padding-left: 20px;"{/if}>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с приобретением ПО «Звездный Оракул» СВФСИС № 20246894
                стоимостью
                <span class="star_oracle_amount">триста пятьдесят</span>
                рублей,
                предоставляемой в соответствии с <a href="user/docs?action=additional_service_star_oracle"
                                                    target="_blank">заявлением
                    о предоставлении дополнительных продуктов</a> и
                <a href="/files/doc/dogovor-oferta-cd.pdf" target="_blank">офертой</a>.
                <a type="button" class="btn btn-prolongation" id="btn-modal-staroracle">Подробнее</a>
            </p>
        </div>
    {/if}
        <script>
            $(document).ready(function () {
                function updateHiddenField() {
                    if ($('#star_oracle_check{$idkey}').is(':checked')) {
                        $('#star_oracle_hidden{$idkey}').val('1');
                    } else {
                        $('#star_oracle_hidden{$idkey}').val('0');
                    }
                }

                updateHiddenField();

                $('#star_oracle_check{$idkey}').on('change', function () {
                    updateHiddenField();
                });

              const paymentButton = document.getElementById('btn-modal-staroracle');

              if (paymentButton) {
                paymentButton.addEventListener('click', () => {
                  $.magnificPopup.open({
                    items: {
                      src: '#modal-staroracle'
                    },
                    type: 'inline',
                    showCloseBtn: false,
                  })
                })
              }    

            });
        </script>
    {/if}
