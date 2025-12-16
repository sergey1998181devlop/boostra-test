{literal}
    <style>
        .mini-stages {

        }
        .mini-stages ul {
            padding:0;
            margin:20px 0;;
            display: grid;
            grid-template: 1fr / repeat(auto-fit, minmax(50px, 1fr));
            grid-gap: 15px;
        }
        .mini-stages ul li{
            display:block;
            height:8px;
            box-sizing:border-box;
        }
        .mini-stages ul li span{
            display:block;
            background:#ccc;
            border: 2px solid #fff;
            height:6px;
            border-radius:5px;
        }
        .mini-stages ul li.current span{
            background:#222;
            border-color:#222;
        }
        @media(max-width:480px)
        {
            .mini-stages ul li {
                padding:0px 5px;
            }
        }
        
        @media screen and (max-width: 768px) {
            .mini-stages ul {
                grid-template: 1fr / repeat(auto-fit, minmax(35px, 1fr));
                grid-gap: 5px;
            }
        }

        .progressbar-percent {
            position: relative!important;
        }
    </style>
{/literal}

<div class="mini-stages">
    {if !in_array($module, ['LoanView', 'AccountView'])}
        <ul>
            {for $step = 1 to $total_step}
                <li {if $step <= $current}class="current"{/if}><span></span></li>
            {/for}
        </ul>
    {/if}
    <div class="progress-bs">
        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
             role="progressbar"
             aria-valuenow="{$percent}"
             aria-valuemin="0" aria-valuemax="100" style="width: {$percent}%">
        </div>
        <span>+ <span class="progressbar-percent">{$percent}</span>% к вероятности одобрения займа</span>
    </div>
</div>

{literal}
    <script>
        function setProgressBar(percent) {
            const $progressBar = $('.progress-bar');
            if ($progressBar.length === 0) return;

            percent = Math.max(0, Math.min(100, percent));

            $('.progressbar-percent').text(percent);
            $progressBar
                .attr('aria-valuenow', percent)
                .css('width', percent + '%');
        }
    </script>
{/literal}