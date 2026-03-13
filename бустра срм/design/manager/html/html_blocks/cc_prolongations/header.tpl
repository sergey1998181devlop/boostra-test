{* ============================================ *}
{* СЕКЦИЯ: Заголовок и фильтры                *}
{* ============================================ *}

{* Кнопки выбора МКК *}
<div class="row mb-3" id="mkk-selector" style="margin-top: 20px;">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Выбор МКК</h5>
                <div class="btn-group" role="group">
                    {foreach $available_organizations as $organization}
                        <button
                                type="button"
                                class="btn btn-outline-primary mkk-btn {if $selected_organization_id == $organization.id}active{/if}"
                                data-mkk="{$organization.id}"
                                data-mkk-name="{$organization.label|escape}"
                        >
                            {$organization.label|escape}
                        </button>
                    {/foreach}
                </div>
                <input type="hidden" id="selected-mkk" value="{$selected_organization_id|escape}">
                <input type="hidden" id="selected-mkk-name" value="{$selected_organization_name|escape}">
            </div>
        </div>
    </div>
</div>

{* Заголовок страницы и фильтры *}
<div class="row page-titles">
    <div class="col-md-2 col-8 align-self-center">
        <h3 class="text-themecolor mb-0 mt-0">
            <i class="mdi mdi-closed-caption"></i>
            <span>Продление нулевой день  </span>
            <br />
            {if $request_date_from}
                <small class="text-white">С {$request_date_from}</small>
            {/if}
            {if $request_date_to}
                <small class="text-white"> по {$request_date_to}</small>
            {/if}
            {if $filter_manager}
            <small class="text-white">{$managers[$filter_manager]->name|escape}</small>
            {/if}
            </h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Главная</a></li>
            <li class="breadcrumb-item active">Продление</li>
        </ol>
    </div>
    <div class="col-md-1 pt-4">
        {if 0 && $manager->role == 'contact_center'}{*убрал кнопку*}
        <button type="button" class="btn btn-primary " id="distribute_me">Распределить мне</button>
        {elseif $manager->id == 77 || $manager->id == 89 || $manager->id == 144 || in_array($manager->role, ['developer', 'admin', 'opr', 'ts_operator'])}
{*                <button type="button" class="btn btn-primary js-distribute-open">Распределить</button>*}
            <button type="button" class="btn btn-primary js-schedule-open">График </button>
        {/if}
    </div>
    <div class="col-md-3">
        <div class="p-2">
            <form autocomplete="off" action="{url}">

                <input type="hidden" name="period" value="{$filter_period}" />
                <input type="hidden" name="organization_id" value="{$selected_organization_id|escape}" />

                <div class="row">
                    <div class="col-md-9">
                        <div class="input-group mb-3">
                            <input type="text" name="date_range" class="form-control daterange" value="{$request_date_from} - {$request_date_to}">
                            <div class="input-group-append">
                                    <span class="input-group-text">
                                        <span class="ti-calendar"></span>
                                    </span>
                            </div>
                        </div>
                        {if $manager->role != 'contact_center'}
                            <select multiple class="form-control js-filter-manager" id="js-filter-manager" name="manager_id[]" placeholder = "Менеджеры">
                                {*<option value="" {if !$filter_manager}selected=""{/if}>Все менеджеры</option>*}
                                {foreach $managers as $m}
                                    {if $m->role == 'contact_center' || $m->role == 'contact_center_robo'}
                                        <option value="{$m->id}" {if in_array($m->id, $filter_manager)}selected{/if}>{$m->name|escape}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        {/if}
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-success runFilter">
                            <span>Выбрать</span>
                        </button>
                        <button type="button" class="btn btn-success downloadCallList mt-3" style="width: 170px">
                            <span>Выгрузить колл лист</span>
                        </button>

                        <button type="button" class="btn btn-success downloadPushSmsCount mt-3" style="width: 170px">
                            <span>Выгрузить количество смс и пуш</span>
                        </button>
                    </div>
                </div>

            </form>


        </div>
    </div>

    {include file='html_blocks/cc_prolongations/statistics.tpl'}
</div>

