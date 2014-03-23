<!-- toplisttable.tpl -->
<table class='kb-table toplist-table'>
	<col class="kb-table-imgcell"/>
	<col class="toplist-name"/>
	<col class="toplist-rank"/>
	<tr class='kb-table-header'>
		<td colspan='2'>{$tl_name}</td>
		<td>{$tl_type}</td>
	</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{section name=i loop=$tl_rows}
	<tr class='{cycle name=ccl}'>
		<td class="kb-table-imgcell">
			{if $tl_rows[i].portrait}<img src="{$tl_rows[i].portrait}" alt="{$tl_rows[i].name}" />
			{else}{$tl_rows[i].icon}{/if}
		</td>
		<td>
			{if $tl_rows[i].rank}{$tl_rows[i].rank}.&nbsp;{/if}{if $tl_rows[i].subname}{/if}{if $tl_rows[i].uri}<a class='kb-shipclass' href="{$tl_rows[i].uri}">{$tl_rows[i].name}</a>{else}{$tl_rows[i].name}{/if}{if $tl_rows[i].subname}{/if}
			{if $tl_rows[i].subname}<br />{$tl_rows[i].subname}{/if}
		</td>
		<td>
			{$tl_rows[i].count}
		</td>
	</tr>
{/section}
</table>
<!-- /toplisttable.tpl -->
