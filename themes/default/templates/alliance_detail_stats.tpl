<!-- alliance_detail_stats.tpl -->
<div class="stats alliance-detail">
	<table class="kb-table">
		<col class="logo" />
		<col class="attribute-name" />
		<col class="attribute-data-short" />
		<col class="attribute-name" />
		<col class="attribute-data-long" />
		<tr class="kb-table-row-even">
			<td class="logo" rowspan="5">
				<img src="{$all_img}" alt="{$all_name}" />
			</td>
			<td >Kills:</td>
			<td class="kl-kill" >{$totalkills}</td>
			<td>Executor:</td>
			<td>
			{if $myAlliance.executorCorpID}<a href="{$kb_host}/?a=corp_detail&amp;crp_ext_id={$myAlliance.executorCorpID}">{$myAlliance.executorCorpName}</a>{/if}
			</td>
		</tr>
		<tr class="kb-table-row-even">
			<td>Losses:</td>
			<td class="kl-loss">{$totallosses}</td>
			<td>Members:</td>
			<td>{$myAlliance.memberCount}</td>
		</tr>
		<tr class="kb-table-row-even">
			<td>ISK destroyed:</td>
			<td class="kl-kill">{$totalkisk}B</td>
			<td>Start Date:</td>
			<td>{$myAlliance.startDate}</td>
		</tr>
		<tr class="kb-table-row-even">
			<td>ISK lost:</td>
			<td class="kl-loss">{$totallisk}B</td>
			<td>Number of Corps:</td>
			<td>{$memberCorpCount}</td>
		</tr>
		<tr class="kb-table-row-even">
			<td>Efficiency:</td>
			<td>{$efficiency}%</td>
			<td></td>
			<td></td>
		</tr>
	</table>
</div>
<!-- /alliance_detail_stats.tpl -->
