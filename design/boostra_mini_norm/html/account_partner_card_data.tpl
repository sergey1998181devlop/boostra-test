{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}

{capture name=page_scripts}
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Добавьте карту, на которую<br>мы переведём деньги</h1>
			</hgroup>

            <div>
				<br/>
				<a href="{$partner_url}" id="js-partner-btn" target="_blank" class="button medium">Добавить карту</a>
				<br/>

            </div>
		</div>
	</div>
</section>

<script>
	window.inactivityPopupEnabled = false;

	$('#js-partner-btn').click(function () {
		sendMetric('reachGoal', 'bonon_card');
		$.ajax({
			url: '/ajax/check_scorings_nk.php',
			data: {
				action: 'partnerClicked'
			},
			success: function (data) {
				let result = data.result;
				if (result.refresh) {
					location.reload();
				}
			}
		});
	});
</script>