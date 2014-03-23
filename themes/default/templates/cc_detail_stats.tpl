<!-- cc_detail_stats.tpl -->
<table class="kb-table contract-stats">
	<tr class="kb-table-row-even">
		<td class="contract-logo" rowspan="4">
			<img src="{$img_url}/campaign-big.png" alt="" />
		</td>
		<td class="contract-data-name">Start date:</td>
		<td class="kb-date">{$contract_startdate}</td>
		<td class="contract-data-name">End date:</td>
		<td class="kb-date">{$contract_enddate}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td>Kills:</td>
		<td class="kl-kill">{$kill_count}</td>
		<td>Losses:</td>
		<td class="kl-loss">{$loss_count}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td>Damage done (ISK):</td>
		<td class="kl-kill">{$kill_isk}B</td>
		<td>Damage received (ISK):</td>
		<td class="kl-loss">{$loss_isk}B</td>
	</tr>
	<tr class="kb-table-row-even">
		<td>Runtime:</td>
		<td>{$contract_runtime} days</td>
		<td>Efficiency:</td>
		<td>{$contract_efficiency}%</td>
	</tr>
</table>
<!-- cc_detail_stats.tpl -->