<!-- pilot_detail_stats -->
<div class="stats pilot-detail">
	<table class='kb-table'>
		<col class="logo" />
		<col class="attribute-name" />
		<col class="attribute-data" />
		<tr class='kb-table-row-even'>
			<td rowspan='7'><img src="{$portrait_URL}" alt="portrait" /></td>
			<td>Corporation:</td>
			<td><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$corp_id}">{$corp_name}</a></td>
		</tr>
		<tr class='kb-table-row-even'>
			<td>Alliance:</td>
			<td><a href="{$kb_host}/?a=alliance_detail&amp;all_id={$all_id}">{$all_name}</a></td>
		</tr>
		<tr class='kb-table-row-even'>
			<td>Kills:</td>
			<td class='kl-kill'>{$klist_count}</td>
		</tr>
		<tr class='kb-table-row-even'>
			<td>Real kills:</td>
			<td class='kl-kill'>{$klist_real_count}</td>
		</tr>
		<tr class='kb-table-row-even'>
			<td>Losses:</td>
			<td class='kl-loss'>{$llist_count}</td>
		</tr>
		<tr class='kb-table-row-even'>
			<td>ISK destroyed:</td>
			<td class='kl-kill'>{$klist_isk_B}B</td>
		</tr>
		<tr class='kb-table-row-even'>
			<td>ISK lost:</td>
			<td class='kl-loss'>{$llist_isk_B}B</td>
		</tr>
		<tr class='kb-table-row-even'>
			<td rowspan="2">
			<td>Chance of enemy survival:</td>
			<td><span style="color:{if $pilot_survival >= 50}#00AA00{else}#AA0000{/if};">{$pilot_survival}%</span></td>
		</tr>
		<tr class='kb-table-row-even'>
			<td>Pilot Efficiency (ISK):</td>
			<td><span style="color:{if $pilot_efficiency >= 50}#00AA00{else}#AA0000{/if};">{$pilot_efficiency}%</span></td>
		</tr>
	</table>
</div>
<!-- /pilot_detail_stats -->