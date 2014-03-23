<!-- box.tpl -->
<table class="kb-table" width="150" cellspacing="1">
	<tr>
		<td class="kb-table-header" align="center">{$title}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td align="left">
          <div class="menu-wrapper">
{foreach from=$items key=key item=i}
		{strip}
			{if $i.type == "caption"}
				<div class="menu-caption">{$i.name}</div>
			{elseif $i.type == "link"}
				<div class="menu-item">
                {if isset($icon)}
                    <img src="{$icon}" border="0" width="6" height="6" alt="menu item" />
                {/if}
                &nbsp;<a href="{$i.url}"{if $i.onclick} onclick="{$i.onclick}; return false;"{/if}>{$i.name}</a><br />
				</div>
			{elseif $i.type == "img"}
                {if $i.url}<a href="{$i.url}"><img src="{$i.name}" border="0" alt="" width="{$i.width}" height="{$i.height}"/></a>
                {else}<img src="{$i.name}" border="0" alt="" width="{$i.width}" height="{$i.height}"/>{/if}
            {elseif $i.type == "points"}
				<div class="kill-points">{$i.name}</div>
			{/if}
		{/strip}
{/foreach}
          </div>
        </td>
	</tr>
</table>
<br />
<!-- /box.tpl -->