<!-- toplisttable.tpl -->
<table class='kb-table' style="border-spacing:1px; width:306px">
	<tr class='kb-table-header'>
		<td class='kb-table-cell' style="text-align:center; width:232px" colspan='2'>{$tl_name}</td>
		<td class='kb-table-cell' style="text-align:center; width:60px">{$tl_type}</td>
	</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{section name=i loop=$tl_rows}
	<tr class='{cycle name=ccl}' style="height:32px">
		<td style="width:32px; vertical-align:top; text-align: left">
			{if $tl_rows[i].portrait}<img src="{$tl_rows[i].portrait}" alt="{$tl_rows[i].name}" />
			{else}{$tl_rows[i].icon}{/if}
		</td>
		<td class='kb-table-cell' style="width:200px">
			{if $tl_rows[i].rank}<b>{$tl_rows[i].rank}.</b>&nbsp;{/if}{if $tl_rows[i].subname}<b>{/if}{if $tl_rows[i].uri}<a class='kb-shipclass' href="{$tl_rows[i].uri}">{$tl_rows[i].name}</a>{else}{$tl_rows[i].name}{/if}{if $tl_rows[i].subname}</b>{/if}
			{if $tl_rows[i].subname}<br />{$tl_rows[i].subname}{/if}
		</td>
		<td class='kb-table-cell' style="text-align:center">
			<b>{$tl_rows[i].count}</b>
		</td>
	</tr>
{/section}
</table>
<!-- /toplisttable.tpl -->
