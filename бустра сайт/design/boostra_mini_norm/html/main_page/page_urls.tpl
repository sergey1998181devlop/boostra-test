{assign var="page_urls" value=[
    ['name' => 'Займы на карту', 'url' => "{$config->root_url}/pages/zaym-na-kartu/"],
    ['name' => 'Займы на карту Мир', 'url' => "{$config->root_url}/pages/zaym-na-kartu-mir/"],
    ['name' => 'Займы круглосуточно', 'url' => "{$config->root_url}/pages/zaym-kruglosutochno/"],
    ['name' => 'Займы до зарплаты', 'url' => "{$config->root_url}/pages/zaym-do-zarplaty/"],
    ['name' => 'Займы по паспорту', 'url' => "{$config->root_url}/pages/zaym-po-pasportu/"],
    ['name' => 'Займы с плохой кредитной историей', 'url' => "{$config->root_url}/pages/zaym-s-plokhoy-kreditnoy-istoriey/"],
    ['name' => 'Займы 1000 руб', 'url' => "{$config->root_url}/pages/zaym-na-1000-rubley/"],
    ['name' => 'Займы 5000 руб', 'url' => "{$config->root_url}/pages/zaym-na-5000-rubley/"],
    ['name' => 'Займы 10000 руб', 'url' => "{$config->root_url}/pages/zaym-na-10000-rubley/"],
    ['name' => 'Статьи', 'url' => "{$config->root_url}/pages/articles/"],
    ['name' => 'Карта сайта', 'url' => "{$config->root_url}/pages/sitemap/"]
]}
<div id="page_urls">
    <ul>
        {foreach $page_urls as $page_url}
            <li>
                <a target="_blank" href="{$page_url.url}">{$page_url.name}</a>
            </li>
        {/foreach}
    </ul>
</div>
