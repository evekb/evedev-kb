<div class="kl-detail-inv">
	<div class="block-header">Final Blow:</div>
	<table class="kb-table">
		<col class="logo" />
		<col class="logo" />
		<col class="attribute-data" />
		<tr class="kb-table-row-even">
			<td rowspan="5" class="logo">
				<a href="{$i.pilotURL}"><img src="{$i.portrait}" alt="inv portrait" /></a>
			</td>
			<td rowspan="5" class="logo">
				{if $i.shipURL}<a href="{$i.shipURL}">{/if}
					<img src='{$i.shipImage}' alt='{$i.shipName}' title='{$i.shipName}' />
				{if $i.shipURL}</a>{/if}
			</td>
			<td><a href="{$i.pilotURL}">{$i.pilotName}</a></td>
		</tr>
		<tr class="kb-table-row-even">
			<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.corpURL}">{$i.corpName}</a></td>
		</tr>
		<tr class="kb-table-row-even">
			<td>{if $i.alliURL}<a href="{$i.alliURL}">{$i.alliName}</a>{else}{$i.alliName}{/if}</td>
		</tr>
		<tr class="kb-table-row-even">
			{if $i.shipURL}<td><a href="{$i.shipURL}">{$i.shipName}</a> ({$i.shipClass})</td>
			{else}<td>{$i.shipName}</td>{/if}
		</tr>
		<tr class="kb-table-row-even">
			<td>{if $i.weaponID}<a href="{$i.weaponURL}">{$i.weaponName}</a>{else}{$i.weaponName}{/if}</td>
		</tr>
		<tr class="kb-table-row-even">
			<td colspan="2">Damage done:</td><td>{$i.damageDone} {if $victimDamageTaken > 0}({($i.damageDone/$victimDamageTaken*100)|string_format:"%.2f"}%){/if}</td>
		</tr>
	</table>
</div>
