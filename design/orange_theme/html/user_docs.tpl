{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user/upload" scope=parent}

{$body_class = "gray" scope=parent}
 
{$add_order_css_js = true scope=parent}

{literal}


{/literal}

<section id="private">
	<div>
		<div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">
			<div class="nav">
				<ul>
					<li><a href="/user?user_id={$user->id}" {if $action=='user'}class="current"{/if}>Текущий заём</a></li>
				    {* <li><a href="/user?user_id={$user->id}&action=history" {if $action == 'history'}class="current"{/if}>История займов</a></li> *}
                    <li><a href="/user/loanhistory">Мои заявки</a></li>					
                    <li><a href="/user/upload">Мои файлы</a></li>					
                    <li><a href="/user/docs" class="current">Документы</a></li>					
					<li><a href="user/faq">FAQ</a></li>
					<li><a href="user/logout">Выйти</a></li>
				</ul>
			</div>
			<div class="content">
				<div class="panel">

                    <p>
                        {$credit_rating_paid_message}
                    </p>

                    <ul class="docs_list">
                    {if $page_error}
                        <div>
        					<h2>Раздел сейчас не доступен!</h2>
                            <br />
                            <h4>Попробуйте зайти на эту страницу позже.</h4>
                        </div>
                    {elseif $uid_docs|count > 0}
                        {foreach $uid_docs as $doc}
                        {if !$doc->hide}
                        {if $doc->uid != '19e7e23e-4ea3-426f-8f36-86deff750c38'}
                        <li>
                            <a href="{$config->root_url}/user/docs/{$doc->uid}.pdf" target="_blank">
                                {$doc->name|escape}
                            </a>
                        </li>
                        {/if}
                        {/if}
                        {/foreach}
                    {elseif $user_docs|count > 0}
                        {foreach $user_docs as $in => $doc}
                        <li>
                            <a href="{$config->root_url}/files/contracts/{$doc->filename}?{math equation='rand(100000,999999)'}" target="_blank">
                                {if $doc->type == 'contract'}Договор
                                {elseif $doc->type == 'application'}Заявление о предоставлении микрозайма
                                {elseif $doc->type == 'other'}Прочие сведения и заверения о клиенте
                                {elseif $doc->type == 'consent'}Согласие клиента на получение информации из бюро кредитных историй
                                {elseif $doc->type == 'statementprolongation'}Заявление о пролонгации договора микрозайма
                                {elseif $doc->type == 'prolongation'}Дополнительное соглашение
                                {elseif $doc->type == 'insure'}Полис-оферта комбинированного страхования
                                {elseif $doc->type == 'cession'}Уведомление по цессии
                                {else}Документ{/if}                                
                            </a>
                        </li>
                        {/foreach}
                        
                    {elseif $docs_bki}
                        <li>
                            <a href="{$config->root_url}/files/contracts/{$docs_bki[0]}" target="_blank">
                                Согласие клиента на получение информации из бюро кредитных историй
                            </a>
                        </li>

                    {elseif !count($crm_docs)}
					<h2>Что-то пошло не так</h2>
                    <br />
                    <h4>Попробуйте зайти на эту страницу позже.</h4>
                    
                    {/if}

                    {foreach $credit_rating_docs as $credit_rating_doc}
                        <li>
                            <a href="/user/docs?action=credit_rating&rating_id={$credit_rating_doc->id}" target="_blank">
                                {$credit_rating_doc->name|escape} {$credit_rating_doc->created|date}
                            </a>
                        </li>
                    {/foreach}

{*                    {foreach $asp_zaim_list as $asp_zaim}*}
{*                            <li>*}
{*                                <a href="files/asp/{$asp_zaim->file_name}" target="_blank">*}
{*                                   target="_blank">*}
{*                                    Согласие субъекта на иные способы и частоту взаимодействия от {$asp_zaim->date_added|date} по займу №{$asp_zaim->zaim_number}*}
{*                                </a>*}
{*                            </li>*}
{*                    {/foreach}*}

                    {foreach $crm_docs as $crm_doc}
                        <li>
                            <a href="{$config->root_url}/document/{$crm_doc->user_id}/{$crm_doc->id}.pdf" target="_blank">
                                {$crm_doc->name|escape}
                            </a> 
                        </li>
                    {/foreach}
                        {foreach $uploaded_docs as $doc}
                            <li>
                                <a href="{$config->front_url}/files/uploaded_files/{$doc->name}"
                                   target="_blank">
                                    {$doc->name}
                                </a>
                            </li>
                        {/foreach}


                        {if $user->id == 56219}
                        <li>
                            <a href="{$config->root_url}/files/specials/v8_6194_34.pdf" target="_blank">
                                Уведомление о цессии
                            </a> 
                        </li>
                        {/if}
                    
                    </ul>
                    
				</div>
				
			</div>
		</div>
	</div>
</section>