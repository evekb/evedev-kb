<!-- alliance_detail_stats.tpl -->
<table class="kb-table" width="100%" border="0" cellspacing="1">
	<colgroup>
		<col width="128" />
		<col width="160" />
		<col width="110" />
		<col width="100" />
		<col width="*" />
	</colgroup>
	<tr class="kb-table-row-even">
		<td rowspan="5" align="center" bgcolor="black">
			<img src="{$all_img}" alt="{$all_name}" width="128" height="128" border="0" />
		</td>
		<td class="kb-table-cell" ><b>Kills:</b></td>
		<td class="kl-kill" >{$totalkills}</td>
		<td class='kb-table-cell'>
			<b>Executor:</b>
		</td>
		<td class='kb-table-cell'>
			{if $myAlliance.executorCorpID}<a href="{$kb_host}/?a=corp_detail&amp;crp_ext_id={$myAlliance.executorCorpID}">{$myAlliance.executorCorpName}</a>{/if}
		</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Losses:</b></td>
		<td class="kl-loss">{$totallosses}</td>
		<td class='kb-table-cell'>
			<b>Members:</b>
		</td>
		<td class='kb-table-cell'>{$myAlliance.memberCount}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Damage done (ISK):</b></td>
		<td class="kl-kill">{$totalkisk}B</td>
		<td class='kb-table-cell'>
			<b>Start Date:</b>
		</td>
		<td class='kb-table-cell'>{$myAlliance.startDate}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Damage received (ISK):</b></td>
		<td class="kl-loss">{$totallisk}B</td>
		<td class='kb-table-cell'>
			<b>Number of Corps:</b>
		</td>
		<td class='kb-table-cell'>{$memberCorpCount}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Efficiency:</b></td>
		<td class="kb-table-cell"><b>{$efficiency}%</b></td>
	</tr>
</table>
<br/>
<!-- /alliance_detail_stats.tpl -->
