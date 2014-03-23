{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div id="kl-detail-inv">
	<table class="kb-table" width="100%" border="0" cellspacing="1">
{foreach from=$involved key="key" item="i"}
		<tr class="{cycle name="ccl"}">
			<td rowspan="5" style="width:64px; vertical-align:middle">
			    <img {if $i.finalBlow == "true"} class="finalblow" {/if}
				style="width:64px; height:64px" src="{$i.portrait}" alt="inv portrait" />
			</td>
			<td rowspan="5" style="width:64px; vertical-align:middle">
				<div class="item-icon" style="height:64px; width:64px;">
			    <a href="{$kb_host}/?a=invtype&amp;id={$i.shipID}">
					<img {if $i.finalBlow == "true"} class="finalblow"{/if}
					    style="{if $i.finalBlow != "true"}border:none; {/if}position: absolute; height:64px; width:64px;" src='{$i.shipImage}' alt='{$i.shipName}' title='{$i.shipName}' />
			    </a>
				</div>
			</td>

			<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.pilotURL}">{$i.pilotName}</a></td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.corpURL}">{$i.corpName}</a></td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.alliURL}">{$i.alliName}</a></td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;"><b><a href="{$kb_host}/?a=invtype&amp;id={$i.shipID}">{$i.shipName}</a></b> ({$i.shipClass})</td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td class="kb-table-cell" style="padding-top: 1px; padding-bottom: 1px;">{if $i.weaponID}<a href="{$kb_host}/?a=invtype&amp;id={$i.weaponID}">{$i.weaponName}</a>{else}{$i.weaponName}{/if}</td>
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
</div>
