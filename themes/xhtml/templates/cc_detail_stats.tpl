<table align="center" class="kb-table" width="100%" border="0" cellspacing="1">
	<tr class="kb-table-row-even">
		<td rowspan="5" align="center" width="80" height="80">
			<img src="{$img_url}/campaign-big.png" align="middle" alt="" />
		</td>
		<td class="kb-table-cell"><b>Start date:</b></td>
		<td class="kb-table-cell" width="120"><b>{$contract_startdate}</b></td>
		<td class="kb-table-cell"><b>End date:</b></td>
		<td class="kb-table-cell" width="120"><b>{$contract_enddate}</b></td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Kills:</b></td>
		<td class="kl-kill">{$kill_count}</td>
		<td class="kb-table-cell"><b>Losses:</b></td>
		<td class="kl-loss">{$loss_count}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Damage done (ISK):</b></td>
		<td class="kl-kill">{$kill_isk}B</td>
		<td class="kb-table-cell"><b>Damage received (ISK):</b></td>
		<td class="kl-loss">{$loss_isk}B</td>
</tr>
<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Runtime:</b></td>
		<td class="kb-table-cell"><b>{$contract_runtime} days</b></td>
		<td class="kb-table-cell"><b>Efficiency:</b></td>
		<td class="kb-table-cell"><b>{$contract_efficiency}%</b></td>
	</tr>
</table>
<br />