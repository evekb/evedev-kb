{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
	<tr class="{cycle name="ccl"}">
		<td width="64" height="64" rowspan="3"><img src="{$VictimShipImg}" width="64" height="64" alt="{$ShipName}" /></td>
		<td class="kb-table-cell"><b>Ship:</b></td>
		<td class="kb-table-cell"><b><a href="{if !$is_IGB}?a=invtype&amp;id={$i.ShipID}{else}javascript:CCPEVE.showInfo({$i.ShipID}{/if}">{$ShipName}</a></b> ({$ClassName})</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td class="kb-table-cell"><b>Location:</b></td>
		<td class="kb-table-cell"><b><a href="{$SystemURL}">{$System}</a></b> ({$SystemSecurity})</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td class="kb-table-cell"><b>Date:</b></td>
		<td class="kb-table-cell">{$TimeStamp}</td>
	</tr>
	{if $showiskd}
	<tr class="{cycle name="ccl"}">
		<td colspan="2" class="kb-table-cell"><b>Total ISK Loss:</b></td>
		<td class="kb-table-cell">{$TotalLoss}</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td colspan="2" class="kb-table-cell"><b>Total Damage Taken:</b></td>
		<td class="kb-table-cell">{$VictimDamageTaken|number_format}</td>
	</tr>
	{/if}
</table>
<br />
