<?php
/**
 * Smarty plugin to add capture array
 * 
 * @package Smarty
 * @subpackage PluginsBlock
 * @author Zotov
 */

/**
 * @param $params
 * @param $content
 * @param Smarty $smarty
 * @param $repeat
 * @return void
 * @throws SmartyException
 */
function smarty_block_capture_array($params, $content, &$smarty, &$repeat)
{
    if (empty($params['key'])) {
        throw new SmartyException("key params is required.");
    }

    if (!empty($smarty->getTemplateVars($params['key']))) {
        $smarty->append(
            $params['key'],
            !empty($params['key_value']) ? [$params['key_value'] => $content] : $content,
            true
        );
        $smarty->assignGlobal($params['key'], $smarty->getTemplateVars($params['key']));
    } else {
        $smarty->assignGlobal($params['key'], $content);
    }
}
