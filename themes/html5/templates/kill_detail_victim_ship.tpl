<div id="kl-detail-vicship">
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
	<table class="kb-table" width="100%" cellspacing="1">
		<tr class="{cycle name="ccl"}" >
			<td style="width:64px; height:64px; vertical-align:top" rowspan="3"><img src="{$victimShipImage}" alt="{$victimShipName}"/> </td>
			<td style="height:17px"><b>Ship:</b></td>
			<td><b><a href="{$kb_host}/?a=invtype&amp;id={$victimShipID}">{$victimShipName}</a></b> ({$victimShipClassName})</td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td style="height:17px"><b>Location:</b></td>
			<td><b><a href="{$systemURL}">{$system}</a></b> ({$systemSecurity})</td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td><b>Date:</b></td>
			<td>{$timeStamp}</td>
		</tr>
	{if $showiskd}
		<tr class="{cycle name="ccl"}">
			<td colspan="2"><b>ISK Loss at time of kill:</b></td>
			<td>{$totalLoss}</td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td colspan="2"><b>Total Damage Taken:</b></td>
			<td>{$victimDamageTaken|number_format}</td>
		</tr>
	{/if}
	</table>
</div>
