{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}

{capture name=page_scripts}
{/capture}

<section id="worksheet">
	<div>
		<div class="box">
			<hgroup>
				<h1>Подтвердите реквизиты вашей карты</h1>
			</hgroup>

            <div>
				<br/>
				<a onclick="reloadPage(this)" href="/user?action=verify_card" id="js-partner-btn" target="_blank" class="button medium">Подтвердить карту</a>
				<br/>

            </div>
		</div>
	</div>
</section>

<script>
    var reloadPage = _this => {
        _this.disabled = true
        setTimeout(() => window.location.reload(), 1000);
    }
</script>