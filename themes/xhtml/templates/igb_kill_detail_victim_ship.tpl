{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table class="kb-table" width="398" cellspacing="1">
	<tr class="{cycle name="ccl"}">
		<td width="64" height="64" rowspan="3" onClick="CCPEVE.showPreview({$victimShipID})"><img src="{$victimShipImg}" width="64" height="64" alt="{$victimShipName}" /></td>
		<td class="kb-table-cell"><b>Ship:</b></td>
		<td class="kb-table-cell"><b><a href="javascript:CCPEVE.showInfo({$victimShipID})">{$victimShipName}</a></b> ({$victimShipClassName})</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td class="kb-table-cell"><b>Location:</b></td>
		<td class="kb-table-cell"><b><a href="{$systemURL}">{$system}</a></b> ({$systemSecurity})</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td class="kb-table-cell"><b>Date:</b></td>
		<td class="kb-table-cell">{$timeStamp}</td>
	</tr>
	{if $showiskd}
	<tr class="{cycle name="ccl"}">
		<td colspan="2" class="kb-table-cell"><b>Total ISK Loss:</b></td>
		<td class="kb-table-cell">{$totalLoss}</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td colspan="2" class="kb-table-cell"><b>Total Damage Taken:</b></td>
		<td class="kb-table-cell">{$victimDamageTaken|number_format}</td>
	</tr>
	{/if}
</table>
<br />
