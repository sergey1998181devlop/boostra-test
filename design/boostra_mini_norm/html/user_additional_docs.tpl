{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user/extra_docs" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

<link href="design/{$settings->theme|escape}/css/user_docs__extra_services.css" rel="stylesheet" type="text/css">

<section id="private">
    <div>
        <div class="tabs">
            
            {include file='user_nav.tpl' current='docs'}
            
            <div class="content">
                <div class="panel">
                    <p>{$extra_docs_message}</p>

                    {if $page_error}
                        <div>
                            <h2>Раздел сейчас не доступен!</h2>
                            <br />
                            <h4>Попробуйте зайти на эту страницу позже.</h4>
                        </div>
                    {else}
                        {foreach $extra_docs as $loan => $data}
                            {if $data['hidden']}{continue}{/if}

                            {if $data['crm']|@count > 0}
                                <div class="loan_docs" {if !$current_loan || $current_loan != $loan}data-hidden="1"{/if}>
                                    <h2 class="js-toggle-loan-docs"><ins>{$loan}{if $data['date']} от {$data['date']}{/if}</ins> <span>Нажмите, чтобы раскрыть</span></h2>
                                    <div class="loan_docs_list">
                                        <ul class="docs_list">
                                            {foreach $data['crm'] as $doc}
                                                {if $doc && $doc->name}
                                                    <li>
                                                        {if !$doc->replaced}
                                                            <a href="{$config->root_url}/document/{$doc->user_id}/{$doc->id}.pdf" target="_blank">
                                                                {$doc->name|escape}
                                                            </a>
                                                        {else}
                                                            <a href="/files/doc/doc_id_{$doc->id}.pdf"
                                                               target="_blank">{$doc->name|escape}</a>
                                                        {/if}
                                                    </li>
                                                {/if}
                                            {/foreach}
                                        </ul>
                                        <div class="js-expand-loan-docs"></div>
                                    </div>
                                </div>
                            {/if}
                        {/foreach}

                        {if empty($extra_docs)}
                            <p style="text-align: center;">Дополнительные документы отсутствуют.</p>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    </div>
</section>

{literal}
    <script type="text/javascript">
        $('.js-toggle-loan-docs').click(function() {
            let $loan_docs = $(this).closest('.loan_docs');
            let isHidden = $loan_docs.attr('data-hidden') === '1';
            $loan_docs.attr('data-hidden', isHidden ? '0' : '1');
        });

        $('.js-expand-loan-docs').click(function() {
            let $loan_docs = $(this).closest('.loan_docs');
            $loan_docs.attr('data-hidden', '0');
        });
    </script>
{/literal}
