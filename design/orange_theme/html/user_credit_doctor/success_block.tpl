<div class="success-content">
    <h3><b><span class="text-green">Поздравляем с удачной инвестицией!</span></b></h3>
    <p>теперь ваши долги расстают, как лед в Африке )</p>
    <button class="orange-btn btn btn-flex" onclick="copyLink('{$href}');" >
        <img src="design/{$settings->theme}/img/user_credit_doctor/link_icon.png" alt="Материал" />
        Копировать ссылку
    </button>
    <div class="form-email">
        <div class="form-control">
            <input type="email" value="" placeholder="Введите ваш e-mail" />
        </div>
        <div>
            <button class="orange-btn">Отправить e-mail</button>
        </div>
    </div>
    <p><small><a href="info@kreditoff-net.ru">info@kreditoff-net.ru</a> телефон горячей линии <a href="tel:+79379297482">89379297482</a></small></p>
</div>

<script>
    function copyLink(href) {
        navigator.clipboard.writeText(href)
            .then(() => {
                alert('Ссылка успешно скопирована в буфер');
            })
            .catch(err => {
                alert('Error copy link');
                console.log('Error copy link', err);
            });
    }
</script>
