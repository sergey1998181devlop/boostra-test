<div class="form-container container shadow bg-white p-4 rounded-3 {$modificator}">
    <h5 class="text-center fw-bold">Займ под залог авто</h5>
    <form id="car-deposit-form" class="mt-4" method="post" id="newapplication" action="{$smarty.server.REQUEST_URI}">
        <h5>Заполните данные</h5>
        <div class="mb-3">
            <input name="name" type="text" class="form-control max-w-sm" placeholder="Фамилия Имя Отчество" required>
        </div>
        <div class="mb-3">
            <input  name="phone" type="tel" class="form-control max-w-sm" placeholder="Номер телефона" required>
        </div>
        <div class="mb-3">
            <input name="email" type="email" class="form-control max-w-sm" placeholder="E-mail (необязательно)">
        </div>
        <div class="mb-3">
            <input name="car_number" type="text" class="form-control max-w-sm" placeholder="Гос. номер тс" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">Отправить заявку</button>
    </form>
</div>