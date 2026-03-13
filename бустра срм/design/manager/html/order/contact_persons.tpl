<form action="{url}" class="js-order-item-form mb-3" id="contact_persons_form">

    <input type="hidden" name="action" value="contacts" />
    <input type="hidden" name="order_id" value="{$order->order_id}" />
    <input type="hidden" name="user_id" value="{$order->user_id}" />

    <div class="row">
        <div class="col-md-9">
            <h3 class="box-title">
                {if (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator'])}
                    <a href="javascript:void(0);" class="js-edit-form js-event-add-click"  data-event="15" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                        <span>Контактные лица</span>
                    </a>
                {else}
                    <span>Контактные лица</span>
                {/if}
                <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="contactpersons">
                    <i class="mdi mdi-comment-text"></i>
                </a>
            </h3>
        </div>
        <div class="col-md-3 d-flex justify-content-end">
            {if count($contactpersons) > 2}
                <button class="btn btn-primary toggle-contacts" type="button" data-toggle="collapse" data-target="#contactsCollapse" aria-expanded="false" aria-controls="contactsCollapse">
                    Показать всех
                </button>
            {/if}
        </div>
    </div>
    <hr>

    <div class="row view-block {if $contacts_error}hide{/if}">

        <div class="js-comments-block-contactpersons">{display_comments block='contactpersons'}</div>

        {foreach $contactpersons as $key => $contactperson}
            {if $key < 2}
                <div class="col-md-12">
                    <div class="form-group row {if in_array('empty_fakthousing', (array)$contacts_error)}has-danger{/if}">
                        <div class="col-md-8">
                            <p class="form-control-static">
                                {if !in_array($manager->role, ['verificator_minus'])}
                                    <strong>
                                        {$contactperson->name|escape}
                                        ({$contactperson->relation|escape})
                                        {$contactperson->phone|escape}
                                    </strong>
                                {/if}
                                {if $contactperson->phone}
                                    <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call js-event-add-click" data-phone="{$contactperson->phone|escape}" data-event="23" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                    <button
                                            class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                            data-phone="{$contactperson->phone|escape}">
                                        <i class="fas fa-phone-square"></i>

                                    </button>
                                {/if}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <i>{$contactperson->comment|escape}</i>
                        </div>
                    </div>
                </div>
            {/if}
        {/foreach}

        {if count($contactpersons) > 2}
            <div class="collapse col-md-12" id="contactsCollapse">
                <div class="row">
                    {foreach $contactpersons as $key => $contactperson}
                        {if $key >= 2}
                            <div class="col-md-12">
                                <div class="form-group row {if in_array('empty_fakthousing', (array)$contacts_error)}has-danger{/if}">
                                    <div class="col-md-8">
                                        <p class="form-control-static">
                                            {if !in_array($manager->role, ['verificator_minus'])}
                                                <strong>
                                                    {$contactperson->name|escape}
                                                    ({$contactperson->relation|escape})
                                                    {$contactperson->phone|escape}
                                                </strong>
                                            {/if}
                                            {if $contactperson->phone}
                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call js-event-add-click" data-phone="{$contactperson->phone|escape}" data-event="23" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                <button
                                                        class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                        data-phone="{$contactperson->phone|escape}">
                                                    <i class="fas fa-phone-square"></i>

                                                </button>
                                            {/if}
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <i>{$contactperson->comment|escape}</i>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
        {/if}
    </div>

    <div class="row edit-block {if !$contacts_error}hide{/if}">
        {if $contacts_error}
            <div class="col-md-12">
                <ul class="alert alert-danger">
                    {if in_array('empty_contact_person_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица!</li>{/if}
                    {if in_array('empty_contact_person_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица!</li>{/if}
                    {if in_array('empty_contact_person_relation', (array)$contacts_error)}<li>Укажите кем приходится контакное лицо!</li>{/if}
                    {if in_array('empty_contact_person2_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица 2!</li>{/if}
                    {if in_array('empty_contact_person2_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица 2!</li>{/if}
                    {if in_array('empty_contact_person2_relation', (array)$contacts_error)}<li>Укажите кем приходится контакное лицо 2!</li>{/if}
                    {if in_array('empty_contact_person3_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица 3!</li>{/if}
                    {if in_array('empty_contact_person3_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица 3!</li>{/if}
                    {if in_array('empty_contact_person3_relation', (array)$contacts_error)}<li>Укажите кем приходится контакное лицо 3!</li>{/if}
                </ul>
            </div>
        {/if}

        <div class="col-12" id="contactperson_edit_block">

            {foreach $contactpersons as $contactperson}
                <div class="row">
                    <input type="hidden" name="contact_person_id[]" value="{$contactperson->id}" />
                    <div class="col-md-4">
                        <div class="form-group {if in_array('empty_contact_person_name', (array)$contacts_error)}has-danger{/if}">
                            <label class="control-label">ФИО контакного лица</label>
                            <input type="text" class="form-control" name="contact_person_name[]" value="{$contactperson->name|escape}" placeholder="" required="true" />
                            {if in_array('empty_contact_person_name', (array)$contacts_error)}<small class="form-control-feedback">Укажите ФИО контакного лица!</small>{/if}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {if in_array('empty_contact_person_phone', (array)$contacts_error)}has-danger{/if}">
                            <label class="control-label">Тел. контакного лица</label>
                            <input type="text" class="form-control" name="contact_person_phone[]" value="{$contactperson->phone|escape}" placeholder="" required="true" />
                            {if in_array('empty_contact_person_phone', (array)$contacts_error)}<small class="form-control-feedback">Укажите тел. контакного лица!</small>{/if}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {if in_array('empty_contact_person_relation', (array)$contacts_error)}has-danger{/if}">
                            <label class="control-label">Кем приходится</label>
                            <select class="form-control custom-select" name="contact_person_relation[]">
                                <option value="" {if $contactperson->relation == ''}selected=""{/if}>Выберите значение</option>
                                <option value="мать/отец" {if $contactperson->relation == 'мать/отец'}selected=""{/if}>мать/отец</option>
                                <option value="муж/жена" {if $contactperson->relation == 'муж/жена'}selected=""{/if}>муж/жена</option>
                                <option value="сын/дочь" {if $contactperson->relation == 'сын/дочь'}selected=""{/if}>сын/дочь</option>
                                <option value="коллега" {if $contactperson->relation == 'коллега'}selected=""{/if}>коллега</option>
                                <option value="друг/сосед" {if $contactperson->relation == 'друг/сосед'}selected=""{/if}>друг/сосед</option>
                                <option value="иной родственник" {if $contactperson->relation == 'иной родственник'}selected=""{/if}>иной родственник</option>
                            </select>
                            {if in_array('empty_contact_person_relation', (array)$contacts_error)}<small class="form-control-feedback">Укажите кем приходится контакное лицо!</small>{/if}
                        </div>
                    </div>
                    <div class="col-md-12 mb-5">
                        <div class="form-group {if in_array('empty_contact_person_comment', (array)$contacts_error)}has-danger{/if}">
                            <label class="control-label">Комментарий</label>
                            <input type="text" class="form-control" name="contact_person_comment[]" value="{$contactperson->comment|escape}" placeholder=""  />
                            {if in_array('empty_contact_person_comment', (array)$contacts_error)}<small class="form-control-feedback">Укажите тел. контакного лица!</small>{/if}
                        </div>
                    </div>
                </div>
            {/foreach}


        </div>

        <div class="col-md-12">
            <div class="form-actions">
                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                <button type="submit" class="btn btn-rounded btn-outline-success js-add-contactperson float-right"><i class="fa fa-plus-circle"></i> Добавить</button>
            </div>
        </div>
    </div>
</form>

<div class="row js-contactperson-block" id="new_contactperson">
    <input type="hidden" name="contact_person_id[]" value="" />
    <div class="col-md-4">
        <div class="form-group">
            <label class="control-label">ФИО контакного лица</label>
            <input type="text" class="form-control" name="contact_person_name[]" value="" placeholder="" required="true" />
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="control-label">Тел. контакного лица</label>
            <input type="text" class="form-control" name="contact_person_phone[]" value="" placeholder="" required="true" />
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="control-label">Кем приходится</label>
            <select class="form-control custom-select" name="contact_person_relation[]">
                <option value="">Выберите значение</option>
                <option value="мать/отец">мать/отец</option>
                <option value="муж/жена">муж/жена</option>
                <option value="сын/дочь">сын/дочь</option>
                <option value="коллега">коллега</option>
                <option value="друг/сосед">друг/сосед</option>
                <option value="иной родственник">иной родственник</option>
            </select>
        </div>
    </div>
    <div class="col-md-12 mb-5">
        <div class="form-group">
            <label class="control-label">Комментарий</label>
            <div class="row">
                <div class="col-10">
                    <input type="text" class="form-control" name="contact_person_comment[]" value="" placeholder=""  />
                </div>
                <div class="col-2 ">
                    <label class="control-label">&nbsp; </label>
                    <button class="btn btn-danger js-remove-contactperson"><i class="fas fas fa-times-circle"></i> Удалить</button>
                </div>
            </div>

        </div>
    </div>

</div>