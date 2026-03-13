<div class="nav">
    <ul>
        <li><a href="/user?user_id={$user->id}" {if $current=='user'}class="current"{/if}>Текущий заём</a></li>
        {if $restricted_mode !== 1}
            <li><a href="/user/upload" {if $current=='upload'}class="current"{/if}>Мои файлы</a></li>
            <li><a href="/user/docs" {if $current=='docs'}class="current"{/if}>Документы</a></li>
            {if $show_chat_dop}
                <li><a href="https://finansdoctor.ru/embed/?license={$fd_license_key|escape:'url'}" target="_blank">Финансовый консультант</a></li>
            {/if}
            <li><a id="faq-link" href="/user/faq" {if $current=='faq'}class="current"{/if}>Вопросы и ответы</a></li>
            <li class="nav-tickets">
                <a href="/user/tickets" {if $current=='tickets'}class="current"{/if}>
                    Форма обращения
                    <span class="nav-alert" id="operator-alert" title="Есть непрочитанные комментарии"></span>
                </a>
            </li>
            {* <li><a href="user/logout">Выйти</a></li> *}
        {/if}
    </ul>
</div>

{literal}
    <script>
        document.addEventListener('DOMContentLoaded', updateTicketsUnreadCommentsAlert);
    </script>
{/literal}

<script>
    initFaqHighlight({
        enabled: Boolean({$faq_highlight_enabled|default:0}),
        delay: {$faq_highlight_delay|default:10}
    });
</script>
