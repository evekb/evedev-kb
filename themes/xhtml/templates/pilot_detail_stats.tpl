<table class='kb-table' cellspacing='1' width="100%">
	<tr class='kb-table-row-even'>
		<td rowspan='8' width='128'><img src="{$portrait_URL}" border="0" width="128" height="128" alt="portrait" /></td>
		<td class='kb-table-cell' width='160'><b>Corporation:</b></td>
		<td class='kb-table-cell'><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$corp_id}">{$corp_name}</a></td>
	</tr>
	<tr class='kb-table-row-even'>
		<td class='kb-table-cell'><b>Alliance:</b></td>
		<td class='kb-table-cell'><a href="{$kb_host}/?a=alliance_detail&amp;all_id={$all_id}">{$all_name}</a></td>
	</tr>
	<tr class='kb-table-row-even'>
		<td class='kb-table-cell'><b>Kills:</b></td>
		<td class='kl-kill'>{$klist_count}</td>
	</tr>
	<tr class='kb-table-row-even'>
		<td class='kb-table-cell'><b>Real kills:</b></td>
		<td class='kl-kill'>{$klist_real_count}</td>
	</tr>
	<tr class='kb-table-row-even'>
		<td class='kb-table-cell'><b>Losses:</b></td>
		<td class='kl-loss'>{$llist_count}</td>
	</tr>
	<tr class='kb-table-row-even'>
		<td class='kb-table-cell'><b>Damage done (ISK):</b></td>
		<td class='kl-kill'>{$klist_isk_B}B</td>
	</tr>
	<tr class='kb-table-row-even'>
		<td class='kb-table-cell'><b>Damage received (ISK):</b></td>
		<td class='kl-loss'>{$llist_isk_B}B</td>
	</tr>
	<tr class='kb-table-row-even'>
		<td class='kb-table-cell'><b>Chance of enemy survival:</b></td>
		<td class='kb-table-cell'><b><span style="color:{if $pilot_survival >= 50}#00AA00{else}#AA0000{/if};">{$pilot_survival}%</span></b></td>
	</tr>
	<tr class='kb-table-row-even'>
		<td class='kb-table-cell'></td>
		<td class='kb-table-cell'><b>Pilot Efficiency (ISK):</b></td>
		<td class='kb-table-cell'><b><span style="color:{if $pilot_efficiency >= 50}#00AA00{else}#AA0000{/if};">{$pilot_efficiency}%</span></b></td>
	</tr>
</table>
<br/>