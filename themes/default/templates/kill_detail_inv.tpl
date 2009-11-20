{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table class="kb-table" width="360" border="0" cellspacing="1">

	{foreach from=$involved key="key" item="i"}
		{if $IsAlly eq true}
			<tr class="{cycle name="ccl"}">
				<td rowspan="5" width="64">{if $is_IGB}<a href="javascript:CCPEVE.showInfo(1377, {$i.ext_id})">{/if}<img {if $i.FB == "true"}class="finalblow"{/if} height="64" width="64" src="{$i.portrait}" border="0" alt="inv portrait" />{if $is_IGB}</a>{/if}</td>
				<td rowspan="5" width="64"><a href="{if !$is_IGB}?a=invtype&amp;id={$i.ShipID}{else}javascript:CCPEVE.showInfo({$i.ShipID}){/if}"><img {if $i.FB == "true"}class="finalblow"{/if} height="64" width="64" src="{$i.shipImage}" border="0" alt="{$i.ShipName}" /></a></td>

				<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.PilotURL}">{$i.PilotName}</a></td>
			</tr>
			<tr class="{cycle name="ccl"}">
			   {if $AllyCorps[$i.CorpName] eq ""}
					<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.CorpURL}">{$i.CorpName}</a></td>
			   {else}
					<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.CorpURL}">{$i.CorpName}</a></td>
			   {/if}
			</tr>
			<tr class="{cycle name="ccl"}">
				{if $i.AlliName eq $HomeName}
					<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.AlliURL}">{$i.AlliName}</a></td>
				{else}
					<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.AlliURL}">{$i.AlliName}</a></td>
				{/if}
			</tr>
		{else}
			<tr class="{cycle name="ccl"}">
				<td rowspan="5" width="64"><img {if $i.FB == "true"}class="finalblow"{/if} height="64" width="64" src="{$i.portrait}" border="0" /></td>
				<td rowspan="5" width="64"><img {if $i.FB == "true"}class="finalblow"{/if} height="64" width="64" src="{$i.shipImage}" border="0" /></td>

				{if $i.CorpName eq $HomeName}
					<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px; background-color: #707000;"><a href="{$i.PilotURL}">{$i.PilotName}</a></td>
				{else}
					<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.PilotURL}">{$i.PilotName}</a></td>
				{/if}
			</tr>
			<tr class="{cycle name="ccl"}">
				<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.CorpURL}">{$i.CorpName}</a></td>
			</tr>
			<tr class="{cycle name="ccl"}">
				<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.AlliURL}">{$i.AlliName}</a></td>
			</tr>
		{/if}

		<tr class="{cycle name="ccl"}">
			<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><b><a href="{if !$is_IGB}?a=invtype&amp;id={$i.ShipID}{else}javascript:CCPEVE.showInfo({$i.ShipID}){/if}">{$i.ShipName}</a></b> ({$i.shipClass})</td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;">{if $i.weaponID}<a href="{if !$is_IGB}?a=invtype&amp;id={$i.weaponID}{else}javascript:CCPEVE.showInfo({$i.weaponID}){/if}">{$i.weaponName}</a>{else}{$i.weaponName}{/if}</td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td colspan="2" class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;">Damage done:</td><td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;">{$i.damageDone|number_format} {if $VictimDamageTaken > 0}({$i.damageDone/$VictimDamageTaken*100|number_format}%){/if}</td>
		</tr>
	{/foreach}
{if $limited}<tr class="{cycle name="ccl"}">
	<td colspan="3" class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;">{$moreInvolved} pilot{if $moreInvolved > 1}s{/if} not shown. <a href="{$unlimitURL}">Show all involved pilots</a></td>
</tr>
{/if}
</table>