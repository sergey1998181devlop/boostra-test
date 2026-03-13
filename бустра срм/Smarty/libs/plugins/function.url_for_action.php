<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 */

function smarty_function_url_for_action($params, Smarty_Internal_Template $template): string
{
    if (!isset($params[0]) && !isset($params['0']) && !isset($params['action'])) {
        return '';
    }

    $newAction = $params['action'] ?? $params[0] ?? $params['0'];

    $path = $_SERVER['PHP_SELF'];

    $query = $_GET;
    $query['action'] = $newAction;

    return htmlspecialchars($path . '?' . http_build_query($query), ENT_QUOTES);
}