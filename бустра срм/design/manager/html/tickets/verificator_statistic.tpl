                {$pk_percent = ($daily_pk/$settings->verificator_daily_plan_pk*100)|round}
                {if $pk_percent < 40}{$pk_color='danger'}
                {elseif $pk_percent < 65}{$pk_color='primary'}
                {elseif $pk_percent < 85}{$pk_color='info'}
                {else}{$pk_color='success'}{/if}
                {$nk_percent = ($daily_nk/$settings->verificator_daily_plan_nk*100)|round}
                {if $nk_percent < 40}{$nk_color='danger'}
                {elseif $nk_percent < 65}{$nk_color='primary'}
                {elseif $nk_percent < 85}{$nk_color='info'}
                {else}{$nk_color='success'}{/if}
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card m-0 bg-grey">
                            <div class="card-body">
                                <h5 class="card-title mb-1 text-center">Выдано в этом месяце</h5>
                                <div class="row">
                                    <div class="col-6 ">
                                        <h2 class="font-light text-info mb-0 text-center">
                                            <strong>НК: {$month_nk}</strong>
                                        </h2>
                                    </div>
                                    <div class="col-6">
                                        <h2 class="font-light mb-0 text-success text-center">
                                            <strong>ПК: {$month_pk}</strong>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0 bg-grey">
                            <div class="card-body">
                                <h5 class="card-title mb-1">Новые клиенты</h5>
                                <div class="row">
                                    <div class="col-4">
                                        <strong class="text-{$nk_color}">{$nk_percent}%</strong>
                                    </div>
                                    <div class="col-8">
                                        <h3 class="font-light mb-0 text-right">
                                            {$daily_nk}/{$settings->verificator_daily_plan_nk}
                                        </h3>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-{$nk_color}" role="progressbar" style="width: {$nk_percent}%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0 bg-grey">
                            <div class="card-body">
                                <h5 class="card-title mb-1">Повторные клиенты</h5>
                                <div class="row">
                                    <div class="col-4">
                                        <strong class="text-{$pk_color}">{$pk_percent}%</strong>
                                    </div>
                                    <div class="col-8">
                                        <h3 class="font-light mb-0 text-right">
                                            {$daily_pk}/{$settings->verificator_daily_plan_pk}
                                        </h3>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-{$pk_color}" role="progressbar" style="width: {$pk_percent}%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>