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
                            <a href="/faq/section/{$section.section_id}?q={$item->id}">❓ {$item->question}</a>
                        </li>
                    {/foreach}
                </ul>
                <div class="faq-card-footer">
                    <a href="/faq/section/{$section.section_id}" class="faq-show-all">Все ответы →</a>
                </div>
            </div>
        {/foreach}
    </div>
</main>

<style>
    .faq-grid-container {
        padding: 40px 40px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .faq-main-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 30px;
        text-align: left;
        color: #222;
    }

    .faq-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    @media (max-width: 992px) {
        .faq-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .faq-grid {
            grid-template-columns: 1fr;
        }
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
        min-height: 320px;
        background-color: #f9fafa;
        padding: 20px;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        box-sizing: border-box;
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

    .faq-card-list {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
    }

    .faq-card-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #000;
    }

    .faq-card-question {
        font-size: 15px;
        color: #000;
        margin-bottom: 8px;
    }

    .faq-card-question a {
        color: #000;
        text-decoration: none;
    }

    .faq-card-question a:hover {
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
        if (goalId) {
            sendMetric('reachGoal', goalId);
        }
    });

    document.querySelectorAll('.faq-question').forEach(function (el) {
        el.addEventListener('click', function () {
            document.querySelectorAll('.faq-question').forEach(q => q.classList.remove('active'));
            el.classList.add('active');

            const question = el.dataset.question;
            const answer = el.dataset.answer;

            document.getElementById('faq-selected-question').innerHTML = question;
            document.getElementById('faq-selected-answer').innerHTML = answer;
        });
    });

    const first = document.querySelector('.faq-question');
    if (first) {
        first.click();
    }
</script>