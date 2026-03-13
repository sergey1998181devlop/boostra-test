<link href="design/{$settings->theme}/css/partners.css?v=1.1" rel="stylesheet" type="text/css" >
<main class="unq-company-page">
    <section class="unq-card">
        <h1 class="unq-section-title">Партнёры</h1>
        <div class="unq-products-wrapper">
            {include file='main_page/partners_data.tpl'}

{*            <!-- Документы Лорд -->*}
{*            <div class="unq-product-card">*}
{*                <h2 class="blue-title">ООО МКК «Лорд»</h2>*}
{*                <ul class="unq-docs-list" style="display: none;">*}
{*                    {foreach $lord_docs as $doc}*}
{*                        <li><a href="{$doc.path}">{$doc.name}</a></li>*}
{*                    {/foreach}*}
{*                </ul>*}
{*            </div>*}

{*            <!-- Документы Фрида -->*}
{*            <div class="unq-product-card">*}
{*                <h2 class="blue-title">ООО МКК «Фрида»</h2>*}
{*                <ul class="unq-docs-list" style="display: none;">*}
{*                    {foreach $frida_docs as $doc}*}
{*                        <li><a href="{$doc.path}">{$doc.name}</a></li>*}
{*                    {/foreach}*}
{*                </ul>*}
{*            </div>*}

{*            <!-- Документы Русзайм -->*}
{*            <div class="unq-product-card">*}
{*                <h2 class="blue-title">ООО МКК «Русзаймсервис»</h2>*}
{*                <ul class="unq-docs-list" style="display: none;">*}
{*                    {foreach $rus_zaim_docs as $doc}*}
{*                        <li><a href="{$doc.path}">{$doc.name}</a></li>*}
{*                    {/foreach}*}
{*                </ul>*}
{*            </div>*}
        </div>
    </section>
</main>

{literal}
    <script lang="javascript">
        $(document).ready(function () {
            $('.unq-product-card h2.blue-title').each(function() {
                $(this).append('<span class="toggle-arrow">▶</span>');
            });

            $('.unq-product-card').click(function () {
                const $list = $(this).find('.unq-docs-list');
                const $arrow = $(this).find('.toggle-arrow');

                $list.slideToggle(300, function() {
                    if ($list.is(':visible')) {
                        $arrow.text('▼');
                        $list.parent().addClass('active');
                    } else {
                        $arrow.text('▶');
                        $list.parent().removeClass('active');
                    }
                });
            });
        });
    </script>
{/literal}