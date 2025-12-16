{$canonical="/user/faq" scope=parent}
{$body_class = "gray" scope=parent}
{$add_order_css_js = true scope=parent}

{capture name=page_scripts}
    <script src="design/{$settings->theme|escape}/js/user.js?v=1.36" type="text/javascript"></script>
{/capture}

<section id="private">
    <div>
        <div class="tabs">
            {include file='user_nav.tpl' current='faq'}

            <div class="panel faq" style="text-align: left; padding: 30px 20px;">

                <nav class="breadcrumb" style="margin-bottom: 20px;">
                    <a href="/">Главная</a> ›
                    <a href="/user/faq">Вопросы и ответы</a> ›
                    <span>{$section_name}</span>
                </nav>
                <div class="faq-search">
                    <input type="text" id="faq-search-input" class="faq-search-input" placeholder="Поиск по разделу" autocomplete="off" />
                </div>
                <main class="faq-wrapper">
                    <div class="faq-sidebar">
                        <h2>{$section_name}</h2>
                        <ul class="faq-list">
                            {foreach $faqs as $item}
                                <li class="faq-link {if $item->question == $selected_question}active{/if}">
                                    <a href="/user/faq?action=user_section&section_id={$item->section_id}&q={$item->id}">{$item->question}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>

                    <div class="faq-content">
                        <h1>{$selected_question}</h1>
                        <div class="faq-answer">
                            {$selected_answer nofilter}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</section>

<style>
    .faq-wrapper {
        display: flex;
        gap: 40px;
        padding: 0 20px;
        max-width: 1200px;
        margin: auto;
    }

    .faq-sidebar {
        width: 30%;
        background: #f9fafa;
        border-radius: 12px;
        padding: 20px;
        max-height: 500px;
        overflow-y: auto;
    }

    .faq-sidebar h2 {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #222;
    }

    .faq-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .faq-link {
        margin-bottom: 10px;
    }

    .faq-link a {
        text-decoration: none;
        color: #000;
        font-size: 15px;
        display: inline-block;
        padding: 6px 8px;
        border-radius: 6px;
        transition: background 0.2s, color 0.2s;
    }

    .faq-link a:hover {
        background: #e0e0e0;
        color: #000;
    }

    .faq-link.active a {
        font-weight: bold;
        color: #000;
        background: #dcdcdc;
    }

    .faq-content {
        flex: 1;
        background: #fff;
        padding: 10px 30px 30px;
        border-radius: 12px;
        border: 1px solid #eee;
    }

    .breadcrumb {
        font-size: 14px;
        margin: 0 auto 20px;
        color: #666;
        max-width: 1200px;
        padding: 0 20px;
    }

    .breadcrumb a {
        color: #000;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .faq-wrapper {
            flex-direction: column;
        }

        .faq-sidebar {
            width: 100%;
            max-height: 240px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .faq-list {
            max-height: 100%;
            overflow-y: auto;
        }

        .faq-content {
            padding: 20px;
        }
    }
</style>

<link rel="stylesheet" href="/js/autocomplete/styles.css" />
<link rel="stylesheet" href="/design/boostra_mini_norm/css/faq-autocomplete.css" />
<script src="/js/autocomplete/jquery.autocomplete-min.js"></script>
<script src="/design/boostra_mini_norm/js/faq-autocomplete.js"></script>