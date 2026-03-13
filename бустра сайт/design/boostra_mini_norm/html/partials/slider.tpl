<link rel="stylesheet" href="design/{$settings->theme}/css/components/slider.css?v=1.0" />

{assign var=id    value=$id|default:''}
{assign var=name  value=$name|default:$id}
{assign var=value value=$value|default:$min|default:0}
{assign var=min   value=$min|default:0}
{assign var=max   value=$max|default:100}
{assign var=step  value=$step|default:1}
{assign var=wrapperClassName value=$wrapperClassName|default:''}
{assign var=sliderClassName value=$sliderClassName|default:''}
{assign var=style value=$style|default:''}
{assign var=data  value=$data|default:[]}
{assign var=attrs value=$attrs|default:[]}

<div class="range {$wrapperClassName|escape}"{if $style} style="{$style|escape}"{/if}>
<input
    type="range"
    name="{$name|escape}"
    {if $id}id="{$id|escape}"{/if}
    value="{$value|escape}"
    min="{$min|escape}"
    max="{$max|escape}"
    step="{$step|escape}"
    {foreach $data as $k => $v} data-{$k|escape}="{$v|escape}"{/foreach}
    {foreach $attrs as $k => $v} {$k|escape}="{$v|escape}"{/foreach}
/>
</div>