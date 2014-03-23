{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<br />
<table class='kb-table' width="100%" border="0" cellspacing='1'>
	<tr class='kb-table-header'>
		<td class='kb-table-cell' width="200"><b>Corporation Name</b></td>
		<td class='kb-table-cell' width="60" align='center'><b>Ticker</b></td>
		<td class='kb-table-cell' width="50" align='center'><b>Members</b></td>
		<td class='kb-table-cell' width="90" align='center'><b>Join Date</b></td>
		<td class='kb-table-cell' width="50" align='center'><b>Tax Rate</b></td>
		<td class='kb-table-cell' width="250"><b>Website</b></td>
	</tr>
{section name=sys loop=$corps}
	{assign var="c" value=$corps[sys]}
	<tr class='kb-table-row-even'>
		<td class='kb-table-cell'><a href="{$kb_host}/?a=corp_detail&amp;crp_ext_id={$c.corpExternalID}">{$c.corpName}</a></td>
		<td class='kb-table-cell' align='center'>{$c.ticker}</td>
		<td class='kb-table-cell' align='center'>{$c.members}</td>
		<td class='kb-table-cell' align='center'>{$c.joinDate}</td>
		<td class='kb-table-cell' align='center'>{$c.taxRate}</td>
{if $c.url}
		<td class='kb-table-cell'><span style="overflow: hidden; width: 200px"><a href="{$c.url}">{$c.url|truncate:40}</a></span></td>
{else}
		<td class='kb-table-cell'></td>
{/if}
	</tr>
{/section}
</table>
<br />