{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table class="kb-table" width="398" cellspacing="1">
	<tr class="{cycle name="ccl"}">
		<td width="64" height="64" rowspan="3" onClick="CCPEVE.showPreview({$victimShipID})"><img src="{$victimShipImg}" width="64" height="64" alt="{$victimShipName}" /></td>
		<td><b>Ship:</b></td>
		<td><b><a href="javascript:CCPEVE.showInfo({$victimShipID})">{$victimShipName}</a></b> ({$victimShipClassName})</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td><b>Location:</b></td>
		<td><b><a href="{$systemURL}">{$system}</a></b> ({$systemSecurity})</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td><b>Date:</b></td>
		<td>{$timeStamp}</td>
	</tr>
	{if $showiskd}
	<tr class="{cycle name="ccl"}">
		<td colspan="2"><b>Total ISK Loss:</b></td>
		<td>{$totalLoss}</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td colspan="2"><b>Total Damage Taken:</b></td>
		<td>{$victimDamageTaken|number_format}</td>
	</tr>
	{/if}
</table>
<br />
