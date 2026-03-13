<div class="user-container">
    <div class="user-column">
        <h2>Информация о пользователе (user_info) TinkoffId</h2>
        {if $user_info}
            <p><strong>Идентификатор (sub):</strong> {$user_info.sub}</p>
            <p><strong>ФИО (name):</strong> {$user_info.name}</p>
            <p><strong>Фамилия (family_name):</strong> {$user_info.family_name}</p>
            <p><strong>Имя (given_name):</strong> {$user_info.given_name}</p>
            <p><strong>Отчество (middle_name):</strong> {$user_info.middle_name}</p>
            <p><strong>Телефон (phone_number):</strong> {$user_info.phone_number}</p>
            <p><strong>Телефон подтвержден (phone_number_verified):</strong> {$user_info.phone_number_verified}</p>
            <p><strong>Пол (gender):</strong> {$user_info.gender}</p>
            <p><strong>Дата рождения (birthdate):</strong> {$user_info.birthdate}</p>
        {else}
            <p>Пользователь не найден</p>
        {/if}
        <br />
        <h2>Паспортные данные (passport_data) TinkoffId</h2>
        {if $user_info.passport_data}
            <p><strong>Серия и номер (serialNumber):</strong> {$user_info.passport_data.serialNumber}</p>
            <p><strong>Дата выдачи (issueDate):</strong> {$user_info.passport_data.issueDate}</p>
            <p><strong>Код подразделения (unitCode):</strong> {$user_info.passport_data.unitCode}</p>
            <p><strong>Кем выдан (unitName):</strong> {$user_info.passport_data.unitName}</p>
            <p><strong>Место рождения (birthPlace):</strong> {$user_info.passport_data.birthPlace}</p>
            <p><strong>Дата рождения (birthDate):</strong> {$user_info.passport_data.birthDate}</p>
            <p><strong>Гражданство (citizenship):</strong> {$user_info.passport_data.citizenship}</p>
            <p><strong>Семейное положение (maritalStatus):</strong> {$user_info.passport_data.maritalStatus}</p>
            <p><strong>Количество детей (numberOfChildren):</strong> {$user_info.passport_data.numberOfChildren}</p>
            <p><strong>Резидент (resident):</strong> {$user_info.passport_data.resident}</p>
        {else}
            <p>Паспортные данные не найдены</p>
        {/if}
        <br />
        <h2>Адреса (addresses) TinkoffId</h2>
        {if $user_info.addresses}
            {foreach $user_info.addresses as $address}
                <p><strong>Тип адреса (addressType):</strong> {$address.addressType}</p>
                <p><strong>Страна (country):</strong> {$address.country}</p>
                <p><strong>Индекс (zipCode):</strong> {$address.zipCode}</p>
                <p><strong>Регион (region):</strong> {$address.region}</p>
                <p><strong>Район (district):</strong> {$address.district}</p>
                <p><strong>Населенный пункт (settlement):</strong> {$address.settlement}</p>
                <p><strong>Дом (house):</strong> {$address.house}</p>
                <p><strong>Строение (building):</strong> {$address.building}</p>
                <p><strong>Основной (primary):</strong> {$address.primary}</p>
                <br />
            {/foreach}
        {else}
            <p>Адреса не найдены</p>
        {/if}

        <h2>Информация о пользователе (user) 1C</h2>
        <ul>
            {if $soap}
                {foreach from=$soap item=value key=key}
                    <li><strong>{$key}:</strong> {$value}</li>
                {/foreach}
            {else}
                <p>Пользователь не найден</p>
            {/if}
        </ul>
    </div>
    <div class="user-column">
        <h2>Информация о пользователе (user) Boostra</h2>
        <ul>
            {if $user_in}
                {foreach from=$user_in item=value key=key}
                    <li><strong>{$key}:</strong> {$value}</li>
                {/foreach}
            {else}
                <p>Пользователь не найден</p>
            {/if}
        </ul>
        <br />
        <h2> $_SESSION['user_info'] </h2>
        <pre>
            {$user_info|@print_r}
        </pre>
    </div>
</div>

<style>
.user-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}

.user-column {
    flex: 0 0 40%;
    box-sizing: border-box;
    padding: 10px;
}

@media (max-width: 768px) {
    .user-column {
        flex: 0 0 100%;
    }
}
</style>