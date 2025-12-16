<div id="expired-div" class="expired-div-toggle">
    <div class="grace-main-div" id="grace-div">
        <div class="grace-container-div">
            <h1>Спасибо, Ваш займ оплачен,  обрабатываем операцию.</h1>
            <button class="get-reference" >Получить
                справку об отсутствии задолженности
            </button>
        </div>
    </div>
</div>
<style>
    .expired-div-toggle{
        display: none;
    }

    .money-have {
        margin: 0!important;
    }

    #countdown-container {
        display: flex;
        /*justify-content: center;*/
        align-items: center;
        /*margin-top: 20px;*/
    }
    .countdown-item {
        text-align: center;
        margin: 0 15px;
    }
    .countdown-item span {
        display: block;
        font-size: 48px;
        color: #333;
        font-weight: 600;
    }

    .countdown-item p {
        margin: 0!important;
        font-size: 18px;
        color: #666;
    }

    #separator {
        font-size: 48px;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 35px;
    }

    #quick-approval-modal {
        background: #fff;
        max-width: 600px;
        border-radius: 20px;
        margin: 0 auto;
        padding: 20px;
    }

    #quick-approval-modal .modal-body {
        margin: 20px 0;
    }

    #quick-approval-modal .modal-title {
        font-weight: 700;
    }

    .payment-block-error {
        display: none;
        background: #fff url('../img/payment_error.png') center no-repeat;
        opacity: 1;
        z-index: 10;
        color: #f44336;
        align-items: center;
        justify-content: space-around;
        flex-direction: column;
    }

    .loan-review-status {
        margin: 20px 0;
        padding: 16px 24px;
        border-radius: 12px;
        background: #E3F2FD;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        width: 20rem;
        border: 1px solid #038AEE;
    }

    .status-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-icon {
        width: 24px;
        height: 24px;
        padding: 6px;
        border-radius: 50%;
        background: #2196F3;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .status-icon svg {
        width: 16px;
        height: 16px;
        fill: white;
    }

    .status-text {
        font-size: 16px;
        font-weight: 500;
        color: #333333;
        line-height: 1.4;
    }
</style>
<script>
    let currentDate = new Date();
    let year = currentDate.getFullYear()+'-'+(currentDate.getMonth()+1)+'-'+currentDate.getDate();
    let formattedTime = currentDate.getHours() + ':' + (currentDate.getMinutes() < 10 ? '0' : '') + currentDate.getMinutes();
    if (localStorage.graceValue === "true" && localStorage.graceButton === "true" && typeof localStorage.graceData !== undefined && JSON.parse(localStorage.graceData).expired_time > year + " " +formattedTime ) {
        $("#expired-div").removeClass("expired-div-toggle")
    }

    $(document).on('click','.get-reference',function (){
        if (localStorage.graceData !== undefined) {
            let data = JSON.parse(localStorage.graceData)
            let payment_id = data.payment_id
            $.ajax({
                url: 'ajax/create_payment_document.php',
                data: {
                    payment_id: payment_id,
                },
                type: 'GET',
                success: (resp) => {
                    console.log(resp)

                    resp = JSON.parse(resp)
                    const url = "document/" + resp.user_id + "/" + +resp.document_id + ".pdf"
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.target ="_blank"
                    a.href = url;
                    a.download = "loan_paid_reference.pdf";
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    delete localStorage.graceValue
                    delete localStorage.graceData
                    delete localStorage.graceButton
                    location.reload()
                }
            })
        }
    })

    window.addEventListener('load', function () {
      if (localStorage.payment_refuser) {
        $.magnificPopup.open({
          items: { src: '#quick-approval-modal' },
          type: 'inline',
          showCloseBtn: true,
          modal: true,
        })

        setTimeout(function () { delete localStorage.payment_refuser}, 1000)
      }

    })

    $(document).ready(function () {
      let el = $('#btn-modal-quick-approval')

      let order_id = el.attr('order-id')
      let organization_id = 6

      el.click(function (e) {
        $.ajax({
          url: 'ajax/b2p_payment.php',
          async: false,
          data: {
            action: 'get_payment_link',
            web: 1,
            refuser: 1,
            amount: 49,
            order_id: order_id,
            organization_id
          },
          success: function (resp) {

            if (!!resp.error) {
              $('.payment-block-error').html('Ошибка: ' + resp.error)
              $('.payment-block-error').css('display', 'block')
              e.preventDefault()
              return false
            } else {
              $('.payment-block-error').css('display', 'none')
              check_state(resp.payment_id)
              el.attr('href', resp.payment_link)

              return true
            }

          }
        })

        ym(45594498, 'reachGoal', 'quick_approval')
      })
    })

    function check_state (payment_id) {
      let check_timeout = setTimeout(function () {
        $.ajax({
          url: 'ajax/b2p_payment.php',
          data: {
            action: 'get_state',
            payment_id: payment_id,
          },
          success: function (resp) {
            console.log(resp)
            if (!!resp.error) {
              $('.payment-block-error').html('Ошибка: ' + resp.error)
              $('.payment-block-error').css('display', 'block')

            } else {
              if (resp.Status === 'CONFIRMED') {
                if ($('.payment-block-exitpool').length > 0) {
                } else {
                  $('.payment-block-error').html('Спасибо, оплата принята.')
                  $('.payment-block-error').css('display', 'block')
                }
              } else if (resp.Status === 'REJECTED') {
                $('.payment-block-error').html('Не получилось оплатить<br />' + resp.Message)
                $('.payment-block-error').css('display', 'block')
              } else {
                app.check_state()
                $('.payment-block-error').css('display', 'none')
              }
            }
          }
        })
      }, 5000)
    }

</script>



{if $user->order}

    {view_order current_order=$user->order}

    {if $cross_orders && !$cross_orders_up}
        {foreach $cross_orders as $cross_order}
            {view_order current_order=$cross_order}
        {/foreach}
    {/if}


{elseif !in_array($user->balance->buyer, ['Правовая защита', 'БИКЭШ']) && $user->balance->zaim_number =='Нет открытых договоров'}
    <div class="about">
{*            <div>Открытых займов не найдено</div>*}
    </div>
    {loan_form cards=$cards}
{/if}

{include  file='tv_medical_modals.tpl'}

