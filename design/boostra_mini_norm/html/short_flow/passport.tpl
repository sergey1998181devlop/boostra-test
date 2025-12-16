{$meta_title = "Заявка на заём | Boostra" scope=parent}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/short_flow_cyberity.js?v=1.0003" type="module"></script>
{/capture}

<section id="worksheet">
    <div>
        <div class="box self_verification">
            <hgroup>
                <h1 class="{if !empty($stage) && $stage === Cyberity::STATUS_ADDED_PASSPORT}hidden{/if} passport">{$header1}</h1>
                <h1 class="{if empty($stage) || $stage !== Cyberity::STATUS_ADDED_PASSPORT}hidden{/if} selfie">{$header2}</h1>
                <h5 class="{if !empty($stage) && $stage === Cyberity::STATUS_ADDED_PASSPORT}hidden{/if} passport">{$header3}</h5>
                <h5 class="{if empty($stage) || $stage !== Cyberity::STATUS_ADDED_PASSPORT}hidden{/if} selfie">{$header4}</h5>
            </hgroup>

            <div style="margin-top: 20px;">
                <input type="hidden" id="applicantLevel" value="basic-kyc-level"/>
                <input type="hidden" id="callbackUrl" value="{$callbackUrl}"/>
                <div class="preloader preloader-show"></div>
                <div id="self-validation-container"></div>
            </div>

            {include 'modals/inactivity_modal.tpl'}
        </div>
    </div>
</section>
