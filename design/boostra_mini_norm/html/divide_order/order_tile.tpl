<div class="main-tile-div tile-order-{$order_data->order->order_id}">
    <p>Заём
        {$organizations[$order_data->order->organization_id]|escape}
        {$order_data->balance->zaim_number}
    </p>

    <button class="main-tile-div-button" data-order = {$order_data->order->order_id}>Открыть</button>
</div>

<div class="hidden-content-{$order_data->order->order_id} order-data" style="display: none;">
    {include file='divide_order/balance.tpl' order_data_index=$order_data@index}
</div>

<style>
    .main-tile-div{
        width: 30%;
        border: 2px solid;
        border-radius: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-direction: column;
        min-width: 300px;
        padding: 5px 0 20px;
        margin: 20px 0!important;
        height: 150px;
        box-sizing: border-box;
        order: 1000;
    }
    .main-tile-div>p,.contracts-list>p{
        margin: 5px !important;
    }
    .main-tile-div-button{
        width: 70%;
        min-width: 100px;
        background: white !important;
        color: black !important;
        border: 1px solid;
        padding: 20px 0;
    }
    .main-tile-div-button:hover {
        background: black !important;
        color: white !important;
    }
    .order-data{
        display: none;
    }
    .contracts-list{
        width: 30%;
        min-width: 300px;
        background: white !important;
        color: black !important;
        border: 1px solid;
        justify-content: center;
        align-items: center;
        border-radius: 20px;
        gap: 10px;
        display: none;
        margin-bottom: 20px;
        cursor: pointer;
    }
    .contracts-main-tile-div{
        display: flex;
        flex-direction: column
    }
</style>

<script>
    $(document).on('click','.main-tile-div-button',function (){
        let id = $(this).data('order')

        $('.order-data').hide()
        $('.main-tile-div').show().css('order', 1000)

        $('.hidden-content-'+ id).show().css('order', 1000)
        $('.tile-order-'+ id).hide()

        let other = $('.main-tile-div').not('.tile-order-'+id)
        if ($('.tile-order-'+id).index() < other.index()) {
            other.css('order', 1000)
        }
        else {
            other.css('order', -1)
        }

        $('.contracts-list').css('display','flex')
    })

    $(document).on('click','.contracts-list',function (){
        $('.main-tile-div').css({
            'display':'flex',
            'order': 1000
        })
        $('.order-data').hide()
        $(this).hide()
    })


</script>