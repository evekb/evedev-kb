{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div class="kl-detail-inv">
	<table class="kb-table">
		<col class="logo" />
		<col class="logo" />
		<col class="attribute-data" />
{foreach from=$involved key="key" item="i"}
		<tr class="{cycle name="ccl"}">
			<td rowspan="5" class="logo" onclick="CCPEVE.showInfo({$i.typeID}, {$i.externalID})">
				<img {if $i.finalBlow == "true"}class="finalblow"{/if} src="{$i.portrait}" alt="inv portrait" />
			</td>
			<td rowspan="5" class="logo" onclick="CCPEVE.showPreview({$i.shipID})">
				<img {if $i.finalBlow == "true"}class="finalblow"{/if} src="{$i.shipImage}" alt="{$i.shipName}" />
			</td>
			<td><a href="{$i.pilotURL}">{$i.pilotName}</a></td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td><a href="{$i.corpURL}">{$i.corpName}</a></td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td><a href="{$i.alliURL}">{$i.alliName}</a></td>
		</tr>
	<tr class="{cycle name="ccl"}">
		<td><a href="javascript:CCPEVE.showInfo({$i.shipID})">{$i.shipName}</a> ({$i.shipClass})</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td>{if $i.weaponID}<a href="javascript:CCPEVE.showInfo({$i.weaponID})">{$i.weaponName}</a>{else}{$i.weaponName}{/if}</td>
	</tr>
		<tr class="{cycle name="ccl"}">
			<td colspan="2">Damage done:</td><td>{$i.damageDone} {if $victimDamageTaken > 0}({($i.damageDone/$victimDamageTaken*100)|string_format:"%.2f"}%){/if}</td>
		</tr>
{/foreach}
{if $limited}<tr class="{cycle name="ccl"}">
			<td colspan="3">{$moreInvolved} pilot{if $moreInvolved > 1}s{/if} not shown. <a href="{$unlimitURL}">Show all involved pilots</a></td>
		</tr>
{/if}
	</table>
</div>
