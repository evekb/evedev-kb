<div id="kl-detail-vicship">
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
	<table class="kb-table" width="100%" cellspacing="1">
		<tr class="{cycle name="ccl"}" >
			<td style="width:64px; height:64px; vertical-align:top" rowspan="3"><img src="{$victimShipImage}" alt="{$victimShipName}"/> </td>
			<td class="kb-table-cell" style="height:17px"><b>Ship:</b></td>
			<td class="kb-table-cell"><b><a href="{$kb_host}/?a=invtype&amp;id={$victimShipID}">{$victimShipName}</a></b> ({$victimShipClassName})</td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td class="kb-table-cell" style="height:17px"><b>Location:</b></td>
			<td class="kb-table-cell"><b><a href="{$systemURL}">{$system}</a></b> ({$systemSecurity})</td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td class="kb-table-cell"><b>Date:</b></td>
			<td class="kb-table-cell">{$timeStamp}</td>
		</tr>
	{if $showiskd}
		<tr class="{cycle name="ccl"}">
			<td colspan="2" class="kb-table-cell"><b>ISK Loss at time of kill:</b></td>
			<td class="kb-table-cell">{$totalLoss}</td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td colspan="2" class="kb-table-cell"><b>Total Damage Taken:</b></td>
			<td class="kb-table-cell">{$victimDamageTaken|number_format}</td>
		</tr>
	{/if}
	</table>
</div>
