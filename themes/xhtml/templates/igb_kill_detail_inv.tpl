{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table class="kb-table" width="360" border="0" cellspacing="1">
{foreach from=$involved key="key" item="i"}
	<tr class="{cycle name="ccl"}">
		<td rowspan="5" width="64" onclick="CCPEVE.showInfo({$i.typeID}, {$i.externalID})"><img {if $i.finalBlow == "true"}class="finalblow"{/if} height="64" width="64" src="{$i.portrait}" border="0" alt="inv portrait" /></td>
		<td rowspan="5" width="64" onclick="CCPEVE.showPreview({$i.shipID})"><img {if $i.finalBlow == "true"}class="finalblow"{/if} height="64" width="64" src="{$i.shipImage}" border="0" alt="{$i.shipName}" /></td>

		<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.pilotURL}">{$i.pilotName}</a></td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.corpURL}">{$i.corpName}</a></td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.alliURL}">{$i.alliName}</a></td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><b><a href="javascript:CCPEVE.showInfo({$i.shipID})">{$i.shipName}</a></b> ({$i.shipClass})</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;">{if $i.weaponID}<a href="javascript:CCPEVE.showInfo({$i.weaponID})">{$i.weaponName}</a>{else}{$i.weaponName}{/if}</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td colspan="2" class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;">Damage done:</td><td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;">{$i.damageDone} {if $victimDamageTaken > 0}({($i.damageDone/$victimDamageTaken*100)|string_format:"%.2f"}%){/if}</td>
	</tr>
{/foreach}
{if $limited}<tr class="{cycle name="ccl"}">
	<td colspan="3" class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;">{$moreInvolved} pilot{if $moreInvolved > 1}s{/if} not shown. <a href="{$unlimitURL}">Show all involved pilots</a></td>
</tr>
{/if}
</table>