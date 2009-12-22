<table class="kb-table" width="100%" border="0" cellspacing="1">
	<tr class="kb-table-row-even">
		<td rowspan="8" width="128" align="center" bgcolor="black">
			<img src="{$img_url}/alliances/{if $all_img == 'default'}default.gif{else}{$all_img}.png{/if}" alt="{$all_name}" width="128" height="128" border="0" />
		</td>
		<td class="kb-table-cell" width="180"><b>Kills:</b></td>
		<td class="kl-kill">{$totalkills}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Losses:</b></td>
		<td class="kl-loss">{$totallosses}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Damage done (ISK):</b></td>
		<td class="kl-kill">{$totalkisk}B</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Damage received (ISK):</b></td>
		<td class="kl-loss">{$totallisk}B</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Efficiency:</b></td>
		<td class="kb-table-cell"><b>{$efficiency}%</b></td>
	</tr>
</table>
<br/>