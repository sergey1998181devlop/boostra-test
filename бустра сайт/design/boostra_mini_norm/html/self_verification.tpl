{$meta_title = "Заявка на заём | Boostra" scope=parent}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
	<script src="design/{$settings->theme}/js/self_verification.js?v=1.000" type="module"></script>
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Идентификация</h1>
			</hgroup>

			<div class="preloader preloader-show"></div>
            <div id="self-validation-container"></div>
		</div>
	</div>
</section>
