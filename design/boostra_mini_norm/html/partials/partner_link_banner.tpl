<div class="partner_link_banner">
    <div class="partner_link_banner_background"></div>
    <div class="partner_link_banner_info">
        <h3>{$user->firstname}, наши партнеры одобрили вам 10 предложений, заберите деньги сейчас</h3>
        <a
          href="{$partner_link}"
          class="get-money js-partner-btn partner_link_banner_button"
          target="_blank"
          rel="nofollow"
          data-text="Одобрено"
          {if !empty($ab_key)}
          data-ab_key="{$ab_key}"
          {/if}
          {if !empty($background_link)}
          onclick="return clickPartner(event, this) || true;"
          {else}
          onclick="return metricsOnly(event, this) || true;"
          {/if}
        >
          <span>Нажми и забери</span>
        </a>
    </div>
</div>

<style>
    .partner_link_banner_background {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0.1;
        z-index: 0;
        background: #17ee03;
    }
    .partner_link_banner {
        position: relative;
        border: 1px solid #03ee0f;
        border-radius: 20px;
        text-align: left;
        padding: 25px;
        overflow: hidden;
        width: 600px;
        margin: {$margin};
        z-index: 0;
    }
    .partner_link_banner_info {
        z-index: 0;
        position: relative;
    }
    .partner_link_banner_button {
        padding: 10px;
        text-align: center;
        display: block;
        background: #038AEE;
        border-radius: 20px;
        color: #fff;
        font-weight: bold;
        margin-top: 15px;
        text-decoration: none !important;
    }

  .js-partner-btn span {
    display: inline-block;
    -webkit-transition: opacity 0.1s ease, -webkit-transform 0.1s ease;
    transition: opacity 0.1s ease, -webkit-transform 0.1s ease;
    transition: opacity 0.1s ease, transform 0.1s ease;
    transition: opacity 0.1s ease, transform 0.1s ease, -webkit-transform 0.1s ease;
    -webkit-animation: fadeInLeft 0.1s forwards;
    animation: fadeInLeft 0.1s forwards;
  }

  .js-partner-btn.js-partner-btn--animated {
    -webkit-animation-name: pulse;
    animation-name: pulse;
    -webkit-animation-duration: 1s;
    animation-duration: 1s;
    -webkit-animation-iteration-count: 1;
    animation-iteration-count: 1;
  }

.partner-new__button {
    flex: 2 1;
    display: flex;
    justify-content: flex-end;
    align-items: flex-end
}

.partner_link_banner .get-money {
    min-width: 180px;
    font-size: 21px;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
    color: #fff;
    background-color: #009e65;
    padding: 15px;
    border: 2px solid #009e65;
    border-radius: 14px;
    transition: all .3s ease
}

.partner_link_banner .get-money span {
    font-weight: 700;
    text-transform: uppercase;
}

.partner_link_banner .get-money:hover,.partner_link_banner .get-money:active {
    color: #009e65;
    background-color: rgba(0, 159, 101, .2)
}

    @media (max-width: 767px) {
        .partner_link_banner {
            width: auto !important;
        }
    }
    @-webkit-keyframes pulse {
        0% {
            -webkit-box-shadow: 0 0 0 0 rgba(0,159,101,0);
            box-shadow: 0 0 rgba(0,159,101,0)
        }

        20% {
            -webkit-box-shadow: 0 0 0 0 rgba(0,159,101,1);
            box-shadow: 0 0 rgba(0,159,101,1)
        }

        50% {
            -webkit-box-shadow: 0 0 0 .6em rgba(0,159,101,.5);
            box-shadow: 0 0 0 .6em rgba(0,159,101,.5)
        }

        to {
            -webkit-box-shadow: 0 0 0 .6em rgba(0,159,101,0);
            box-shadow: 0 0 0 .6em rgba(0,159,101,0)
        }
    }

    @keyframes pulse {
        0% {
            -webkit-box-shadow: 0 0 0 0 rgba(0,159,101,0);
            box-shadow: 0 0 rgba(0,159,101,0)
        }

        20% {
            -webkit-box-shadow: 0 0 0 0 rgba(0,159,101,1);
            box-shadow: 0 0 rgba(0,159,101,1)
        }

        50% {
            -webkit-box-shadow: 0 0 0 .6em rgba(0,159,101,.5);
            box-shadow: 0 0 0 .6em rgba(0,159,101,.5)
        }

        to {
            -webkit-box-shadow: 0 0 0 .6em rgba(0,159,101,0);
            box-shadow: 0 0 0 .6em rgba(0,159,101,0)
        }
    }

    @-webkit-keyframes fadeOutRight {
        0% {
            opacity: 1;
            -webkit-transform: translateX(0);
            transform: translate(0)
        }

        to {
            opacity: 0;
            -webkit-transform: translateX(20px);
            transform: translate(20px)
        }
    }

    @keyframes fadeOutRight {
        0% {
            opacity: 1;
            -webkit-transform: translateX(0);
            transform: translate(0)
        }

        to {
            opacity: 0;
            -webkit-transform: translateX(20px);
            transform: translate(20px)
        }
    }

    @-webkit-keyframes fadeInLeft {
        0% {
            opacity: 0;
            -webkit-transform: translateX(-20px);
            transform: translate(-20px)
        }

        to {
            opacity: 1;
            -webkit-transform: translateX(0);
            transform: translate(0)
        }
    }

    @keyframes fadeInLeft {
        0% {
            opacity: 0;
            -webkit-transform: translateX(-20px);
            transform: translate(-20px)
        }

        to {
            opacity: 1;
            -webkit-transform: translateX(0);
            transform: translate(0)
        }
    }
</style>
<script>
    var offerBtnSelector = '.js-partner-btn';
    var animatedBtnClass = 'js-partner-btn--animated';

    const d = ".js-partner-btn", c = "js-partner-btn--animated";
    document.addEventListener("DOMContentLoaded", function() {
        const a = window.offerBtnSelector ?? d
            , s = window.animatedBtnClass ?? c
            , n = document.querySelectorAll(a);
        n.length > 0 && setInterval( () => {
            n.forEach(e => {
                const t = e.querySelector("span")
                    , o = e.getAttribute("data-text");
                if (!t)
                    return;
                const r = t.textContent;
                t.style.animation = "fadeOutRight 0.1s forwards",
                setTimeout( () => {
                    t.style.animation = "fadeInLeft 0.1s forwards",
                    t.textContent = o,
                    e.setAttribute("data-text", r ?? ""),
                    e.classList.toggle(s)
                }
                , 300)
            }
            )
        }
        , 3e3)
    });

    function sendAbTestMetric(ab_key) {
        $.ajax({
            url: '/user?action=ab_test',
            method: 'POST',
            data: {
                ab_key: ab_key,
            }
        })
    }

    function clickPartner(e, el) {
        sendMetric('reachGoal', 'decline_monitoring_{$metric_id}');
        setTimeout(() => {
            //sendMetric('reachGoal', 'decline_monitoring_' + source_id)
            invokeShopview('bonon-background{$client_suffix}', '{$background_link}')
            window.location.href = '{$background_link}';
        }, 100)

        var ab_key = $(el).data('ab_key');
        if (ab_key) {
            sendAbTestMetric(ab_key);
        }

        return true
    }
    function metricsOnly(e, el) {
        invokeShopview('bonon-shop-window{$client_suffix}', '{$partner_link}')
        sendMetric('reachGoal', 'decline_monitoring_{$metric_id}')

        var ab_key = $(el).data('ab_key');
        if (ab_key) {
            sendAbTestMetric(ab_key);
        }

        return true
    }
</script>
