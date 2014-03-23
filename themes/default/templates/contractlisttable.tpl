<table class="kb-table contractlist kb-table-rows">
	<thead>
	<tr class="kb-table-header">
		<td>Name</td>
		<td class="kb-date">Start date</td>
{if $contract_getactive == "no"}
		<td class="kb-date">End date</td>
{/if}
		<td class="killcount">Kills</td>
		<td class="iskcount">ISK (B)</td>
		<td class="killcount">Losses</td>
		<td class="iskcount">ISK (B)</td>
		<td colspan="2">Efficiency</td>
	</tr>
	</thead>
	<tbody>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$contracts item=i}
	<tr onclick="window.location.href='{$i.url}';">
		<td class="name" >{$i.name}</td>
		<td class='kb-date'>{$i.startdate|date_format:"%Y-%m-%d"}</td>
{if $contract_getactive == "no"}
		<td class='kb-date'>
{if $i.enddate}{$i.enddate|date_format:"%Y-%m-%d"}{else}Active{/if}
		</td>
{/if}
		<td class="kl-kill">{$i.kills}</td>
		<td class="kl-kill">{($i.killisk/1000000)|string_format:"%.2f"}</td>
		<td class="kl-loss">{$i.losses}</td>
		<td class="kl-loss">{($i.lossisk/1000000)|string_format:"%.2f"}</td>
		<td class="efficiency_percent"><b>{$i.efficiency}</b></td>
		<td class="efficiency_graph">{$i.bar}</td>
	</tr>
{/foreach}
</tbody>
</table>