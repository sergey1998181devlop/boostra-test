{$meta_title = "Заявка на заём | Boostra" scope=parent}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/short_flow_cyberity_face_scan.js?v=1.00011" type="module"></script>
{/capture}

<section id="worksheet">
    <div id="steps">
        <div class="box">
            <hgroup>
                <h1>Завершите оформление и получите деньги</h1>
                <h5>Решение по Вашей заявке уже готово</h5>
                {include file='display_stages.tpl' current=6 percent=65 total_step=6}
                <script>
                    {* Лёгкая моральная поддержка сидящего над анкетой клиента *}
                    setTimeout(() => {
                        setProgressBar(80)
                    }, 15_000);
                </script>
            </hgroup>

            <div style="margin-top: 20px;">
                <input type="hidden" id="applicantLevel" value="selfy-kys-level"/>
                <input type="hidden" id="callbackUrl" value="{$callbackUrl}"/>
                <div class="preloader preloader-show"></div>
                <div id="self-validation-container"></div>
            </div>
        </div>
    </div>
</section>
