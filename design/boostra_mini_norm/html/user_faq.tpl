{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

{capture name=page_scripts}
    <script src="design/{$settings->theme|escape}/js/user.js?v=1.45" type="text/javascript"></script>
{/capture}

<section id="private">

    <div>
        <div class="tabs ">

            {include file='user_nav.tpl' current='faq'}

            <div class="panel faq" style="text-align: left; padding: 30px 20px;">
                <main class="faq-grid-container">
                    <div class="faq-search">
                        <input type="text" id="faq-search-input" class="faq-search-input" placeholder="Поиск по разделу" autocomplete="off" />
                    </div>
                    <h1 class="faq-main-title">Основные разделы</h1>

                    <div class="faq-grid">
                        {foreach $faq_sections as $section}
                            <div class="faq-card">
                                <h2 class="faq-card-title">{$section.section_name}</h2>
                                <ul class="faq-card-list">
                                    {foreach $section.faqs|@array_slice:0:5 as $item}
                                        <li class="faq-card-question">
                                            <a href="/user/faq?action=user_section&section_id={$section.section_id}&q={$item->id}">
                                                {$item->question}
                                            </a>
                                        </li>
                                    {/foreach}
                                </ul>
                                <div class="faq-card-footer">
                                    <a href="/user/faq?action=user_section&section_id={$section.section_id}" class="faq-show-all">Все ответы →</a>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </main>
            </div>
        </div>
    </div>
</section>

<style>
    .faq-grid-container {
        /* padding: 40px 20px; */
        max-width: 1200px;
        margin: 0 auto;
    }

    .panel.faq .faq-main-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 30px;
        text-align: left !important;
        color: #222;
    }

    .faq-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
    }

    .faq-card {
        width: calc(33.333% - 16px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background-color: #f9fafa;
        padding: 20px;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        box-sizing: border-box;
        min-height: 320px;
    }

    @media (max-width: 992px) {
        .faq-card {
            width: calc(50% - 12px);
        }
    }

    @media (max-width: 640px) {
        .faq-card {
            width: 100%;
        }
    }

    .faq-card-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #000;
    }

    .faq-card-list {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
    }

    .faq-card-question {
        font-size: 15px;
        color: #000;
        margin-bottom: 8px;
        padding-left: 18px;
        position: relative;
    }

    .faq-card-question::before {
        content: "❓";
        position: absolute;
        left: 0;
        top: 0;
    }

    .faq-card-question a {
        color: #000;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .faq-card-question a:hover {
        color: #000;
        text-decoration: underline;
    }

    .faq-card-footer {
        margin-top: auto;
        padding-top: 15px;
    }

    .faq-show-all {
        font-size: 14px;
        color: #000;
        font-weight: 600;
        text-decoration: none;
    }

    .faq-show-all:hover {
        text-decoration: underline;
    }
</style>

<link rel="stylesheet" href="/js/autocomplete/styles.css" />
<link rel="stylesheet" href="/design/boostra_mini_norm/css/faq-autocomplete.css" />
<script src="/js/autocomplete/jquery.autocomplete-min.js"></script>
<script src="/design/boostra_mini_norm/js/faq-autocomplete.js"></script>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        const goalId = '{$block_goal_id|escape:"html"}';
        console.log(goalId);
        if (goalId) {
            sendMetric('reachGoal', goalId);
        }
    });
</script>
