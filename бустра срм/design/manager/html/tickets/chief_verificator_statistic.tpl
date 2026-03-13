<div class="card m-0 bg-grey p-2" style="float: right; width: 440px;">
    <div class="row">
        <div class="col-4 text-center">
            <h4 class="text-white">НК</h4>
            <h2 class="text-info mb-0">{($infoCurentDay->summNewClients/1000000)|round:2} kk</h2>
            <h5 class="text-info mb-0">{$infoCurentDay->countNewClients} шт</h5>
            <p class="text-info mb-0"><small>ping3: {$infoCurentDayPing3->countNewClients}</small></p>
        </div>
        <div class="col-4 text-center">
            <h4 class="text-white">ПК</h4>
            <h2 class="text-success mb-0">{($infoCurentDay->summRegularClients/1000000)|round:2} kk</h2>
            <h5 class="text-success mb-0">{$infoCurentDay->countRegularClients} шт</h5>
            <p class="text-success mb-0"><small>ping3: {$infoCurentDayPing3->countRegularClients}</small></p>
        </div>
        <div class="col-4 text-center">
            <h4 class="text-white">Всего</h4>
            <h2 class="text-warning mb-0">{($infoCurentDay->totalSumm/1000000)|round:2} kk</h2>
            <h5 class="text-warning mb-0">{$infoCurentDay->totalOrders} шт</h5>
            <p class="text-warning mb-0"><small>ping3: {$infoCurentDayPing3->totalOrders}</small></p>
        </div>
    </div>
</div>
