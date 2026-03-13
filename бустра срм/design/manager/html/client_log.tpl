<div class="client-log">
    <table>
        <tbody>
        {foreach $log as $changed_at => $data}
            <tr class="client-log__item">
                <td>{$data['label']}</td>
                <td>{$data['value']}</td>
                <td>изменено</td>
                <td>{$changed_at}</td>
                <td>{$data['manager']->name}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
