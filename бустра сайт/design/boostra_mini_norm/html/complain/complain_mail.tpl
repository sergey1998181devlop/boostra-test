<style>
    table {
        width: 100%;
        margin-bottom: 20px;
        border: 15px solid #F2F8F8;
        border-top: 5px solid #F2F8F8;
        border-collapse: collapse;
    }
    table th {
        font-weight: bold;
        padding: 5px;
        background: #F2F8F8;
        border: 5px solid #F2F8F8;
    }
    table td {
        padding: 5px;
        border: 5px solid #F2F8F8;
    }
    table td, table th {
        vertical-align: middle;
        text-align: center;
    }
</style>

<h2>Новая заявка с формы обратной связи - Жалобы</h2>
<h4><b>Информация о пользователе</b></h4>
<table>
    <thead>
        <tr>
            <th>Имя</th>
            <th>Email</th>
            <th>Договор</th>
            <th>Сообщение</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{$user_data.user_name}</td>
            <td>{$user_data.user_email}</td>
            <td>{$user_data.user_contract}</td>
            <td>{$user_data.user_message}</td>
        </tr>
    </tbody>
</table>
