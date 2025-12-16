{if ($organization_id == $ORGANIZATION_FRIDA)}
    <td style="border: none; padding: 50px 0 10px 0;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/stamp_frida.jpg" width="80" height="80" style="vertical-align: middle;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/signature_frida.jpg" width="50" height="50" style="margin-left: -40px; vertical-align: middle;">
    </td>
{elseif ($organization_id == $ORGANIZATION_RZS)}
    <td style="border: none; padding: 50px 0 10px 0;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/stamp_rzs.jpg" width="80" height="80" style="vertical-align: middle;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/signature_rzs.jpg" width="50" height="50" style="margin-left: -40px; vertical-align: middle;">
    </td>
{elseif ($organization_id == $ORGANIZATION_LORD)}
    <td style="border: none; padding: 50px 0 10px 0;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/stamp_lord.jpg" width="80" height="80" style="vertical-align: middle;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/signature_lord.jpg" width="50" height="50" style="margin-left: -40px; vertical-align: middle;">
    </td>
{elseif ($organization_id == $ORGANIZATION_AKVARIUS)}
    <td style="border: none; padding: 50px 0 10px 0;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/stamp-akvarius-new.jpg" width="80" height="80" style="vertical-align: middle;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/signature-akvarius-new.jpg" width="50" height="50" style="margin-left: -40px; vertical-align: middle;">
    </td>
{else}
    <td style="border: none; padding: 50px 0 10px 0;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/stamp-akvarius-new.jpg" width="80" height="80" style="vertical-align: middle;">
        <img src="{$config->root_dir}design/boostra_mini_norm/html/pdf/i/signature-akvarius-new.jpg" width="50" height="50" style="margin-left: -40px; vertical-align: middle;">
    </td>
{/if}
