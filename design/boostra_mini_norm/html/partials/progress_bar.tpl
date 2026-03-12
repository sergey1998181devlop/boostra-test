{assign var=od value=$orderData|default:null}

{* стили конкретно прогресс-бара (переопределяют component defaults при необходимости) *}
<link rel="stylesheet" href="design/{$settings->theme}/css/progress_bar.css?v=1.2" />

<div class="overdue-progress"
     data-user-id="{$od->order->user_id|default:0}"
     data-order-id="{$od->order->order_id|default:0}"
     data-overdue-days="{$od->due_days|default:1}"
     data-bucket="{$od->due_days|default:1}">

    <div class="overdue-progress__message" role="status">
        Автоматические уведомления о просрочке
    </div>

    <div class="overdue-progress__slider-wrap">

        {include file='partials/slider.tpl'
            id='overdue-day'
            name='overdue_day'
            min=0
            max=32
            step=2
            value=0
            wrapperClassName='overdue-progress__range'
            attrs=['class'=>'overdue-progress__slider','aria-label'=>'День просрочки']
        }

        <div class="overdue-progress__ticks">
            <span class="overdue-progress__tick" data-value="0">0</span>
            <span class="overdue-progress__tick" data-value="4">4</span>
            <span class="overdue-progress__tick" data-value="10">10</span>
            <span class="overdue-progress__tick" data-value="31">31</span>
        </div>

        <button type="button" class="overdue-progress__donut" aria-label="Риск">
            <span class="overdue-progress__donut-value">80%</span>
        </button>

        <button type="button" class="overdue-progress__info" aria-label="Подробнее" title="Подробнее">i</button>
    </div>

    <div class="overdue-progress__tooltip" hidden>
        Чем больше просрочка, тем ниже вероятность одобрения нового займа
    </div>
</div>

<script src="design/{$settings->theme}/js/progress_bar.js?v=1.1"></script>
