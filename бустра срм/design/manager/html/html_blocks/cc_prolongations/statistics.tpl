{* ============================================ *}
{* СЕКЦИЯ: Статистика                          *}
{* ============================================ *}

<div class="col-md-5 offset-md-1 col-4 align-self-center">

    {if $statistic->total_amount > 0}
    <div class="row bg-grey">
        <div class="col-md-6 text-center">
            <h3 class="pt-1">
                <i class="fas fa-id-card-alt"></i>
                <span>Портфель: {$statistic->total_amount|round} P</span>
            </h3>
        </div>
        <div class="col-md-6 text-center">
            <h3 class="pt-1">
                <i class=" far fa-money-bill-alt"></i>
                <span>Собрано: {$statistic->total_paid|round} P</span>
                <span class="label label-info">
                    <h4 class="mb-0">
                        {if $statistic->total_amount > 0}
                            {($statistic->total_paid / $statistic->total_amount * 100)|round}%
                        {else}
                            0%
                        {/if}
                    </h4>
                </span>
            </h3>
        </div>
        <div class="col-md-12">
            <hr class="m-0" />
        </div>
        <div class="col-md-4">
            <div class="card m-0  bg-grey">
                <div class="card-body p-0 pt-2">
                    <div class="row">
                        <div class="col-4">
                            <div data-label="{($statistic->inwork/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{($statistic->inwork/$statistic->total*10)|round*10}"></div>
                        </div>
                        <div class="col-8">
                            <h5 class="card-title mb-1">Обработано</h5>
                            <h6 class="text-white">
                                {$statistic->inwork} / {$statistic->total}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card m-0 bg-grey">
                <div class="card-body p-0 pt-2">
                    <div class="row">
                        {if $manager->role == 'contact_center'}
                            {$real_percents = ($statistic->prolongation/$settings->cc_pr_prolongation_plan*100)|round}
                            {$round_percents = ($statistic->prolongation/$settings->cc_pr_prolongation_plan*10)|round*10}
                            {$cc_pr_prolongation_plan = $settings->cc_pr_prolongation_plan}
                        {else}
                            {$real_percents = ($statistic->prolongation/$statistic->total*100)|round}
                            {$round_percents = ($statistic->prolongation/$statistic->total*10)|round*10}
                            {$cc_pr_prolongation_plan = $statistic->total}
                        {/if}
                        <div class="col-4">
                            <div data-label="{$real_percents}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{$round_percents}"></div>
                        </div>
                        <div class="col-8">
                            <h5 class="card-title mb-1">Пролонгации</h5>
                            <h6 class="text-white">
                                {$statistic->prolongation} / {$cc_pr_prolongation_plan}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card m-0 bg-grey">
                <div class="card-body p-0 pt-2">
                    <div class="row">
                        {if $manager->role == 'contact_center'}
                            {$real_percents = ($statistic->closed/$settings->cc_pr_close_plan*100)|round}
                            {if ($statistic->closed/$settings->cc_pr_close_plan*10)|round*10 > 100}
                                {$round_percents = 100}
                            {else}
                                {$round_percents = ($statistic->closed/$settings->cc_pr_close_plan*10)|round*10}
                            {/if}
                            {$cc_pr_close_plan = $settings->cc_pr_close_plan}
                        {else}
                            {$real_percents = ($statistic->closed/$statistic->total*100)|round}
                            {$round_percents = ($statistic->closed/$statistic->total*10)|round*10}
                            {$cc_pr_close_plan = $statistic->total}
                        {/if}
                        <div class="col-4">
                            <div data-label="{$real_percents}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{$round_percents}"></div>
                        </div>
                        <div class="col-8">
                            <h5 class="card-title mb-1">Закрытия</h5>
                            <h6 class="text-white">
                                {$statistic->closed} / {$cc_pr_close_plan}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card m-0  bg-grey">
                <div class="card-body p-0 pt-2">
                    <div class="row">
                        <div class="col-4">
                            <div data-label="{($statistic->perezvon/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-primary css-bar-{($statistic->perezvon/$statistic->total*10)|round*10}"></div>
                        </div>
                        <div class="col-8">
                            <h5 class="card-title mb-1">Перезвон</h5>
                            <h6 class="text-white">
                                {$statistic->perezvon} / {$statistic->total}
                            </h6>
                            <h6 class="text-white">
                                {$statistic->perezvonPaid} / {$statistic->totalPaid} Р
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card m-0  bg-grey">
                <div class="card-body p-0 pt-2">
                    <div class="row">
                        <div class="col-4">
                            <div data-label="{($statistic->nedozvon/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-warning css-bar-{($statistic->nedozvon/$statistic->total*10)|round*10}"></div>
                        </div>
                        <div class="col-8">
                            <h5 class="card-title mb-1">Недозвон</h5>
                            <h6 class="text-white">
                                {$statistic->nedozvon} / {$statistic->total}
                            </h6>
                            <h6 class="text-white">
                                {$statistic->nedozvonPaid} / {$statistic->totalPaid} Р
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card m-0  bg-grey">
                <div class="card-body p-0 pt-2">
                    <div class="row">
                        <div class="col-4">
                            <div data-label="{($statistic->perspective/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{($statistic->perspective/$statistic->total*10)|round*10}"></div>
                        </div>
                        <div class="col-8">
                            <h5 class="card-title mb-1" style="width: 105px">Перспектива</h5>
                            <h6 class="text-white">
                                {$statistic->perspective} / {$statistic->total}
                            </h6>
                            <h6 class="text-white">
                                {$statistic->perspectivePaid} / {$statistic->totalPaid} Р
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card m-0  bg-grey">
                <div class="card-body p-0 pt-2">
                    <div class="row">
                        <div class="col-4">
                            <div data-label="{($statistic->decline/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-danger css-bar-{($statistic->decline/$statistic->total*10)|round*10}"></div>
                        </div>
                        <div class="col-8">
                            <h5 class="card-title mb-1">Отказ</h5>
                            <h6 class="text-white">
                                {$statistic->decline} / {$statistic->total}
                            </h6>
                            <h6 class="text-white">
                                {$statistic->declinePaid} / {$statistic->totalPaid} Р
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>


