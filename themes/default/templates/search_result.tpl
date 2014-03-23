{if $nonajax}<div class='block-header'>Search results</div>
	<div id='searchresults'>{/if}
{if !$results}No results.
{else}<table class='kb-table'>
	<tr class='kb-table-header'>
		<td>{$result_header}</td>
		<td>{$result_header_group}</td>
	</tr>
{section name=i loop=$results}
	<tr class='kb-table-row-even'>
		<td><a href="{$results[i].link}">{$results[i].name}</a></td>
		<td>{$results[i].type}</td>
	</tr>
{/section}
</table>
{/if}{if $nonajax}</div>{/if}
