{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table class="kb-table" width="150" cellspacing="1">
  <tr>
    <td class="kb-table-header" colspan="2" align="center">Top Damage Dealer</td>
  </tr>
{foreach from=$topdamage key=key item=i}
  <tr class="{cycle name=ccl}">
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper"><a href="{$i.pilotURL}"><img class="finalblow" height="64" width="64" src="{$i.portrait}" alt="{$i.PilotName}" title="{$i.PilotName}" border="0" /></a></div></td>
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper"><a href="{$kb_host}/?a=invtype&amp;id={$i.shipID}"><img class="finalblow" height="64" width="64" src="{$i.shipImage}" alt="{$i.shipName}" title="{$i.shipName}" border="0" /></a></div></td>
  </tr>
{/foreach}
</table>
<br />
{if $finalblow}
<table class="kb-table" width="150" cellspacing="1">
  <tr>
    <td class="kb-table-header" colspan="2" align="center">Final Blow</td>
  </tr>
  <tr class="{cycle name=ccl}">
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper"><a href="{$finalblow.pilotURL}"><img class="finalblow" height="64" width="64" src="{$finalblow.portrait}" alt="{$finalblow.pilotName}" title="{$finalblow.pilotName}" border="0" /></a></div></td>
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper"><a href="{$kb_host}/?a=invtype&amp;id={$finalblow.shipID}"><img class="finalblow" height="64" width="64" src="{$finalblow.shipImage}" alt="{$i.shipName}" title="{$finalblow.ShipName}" border="0" /></a></div></td>
  </tr>
</table>
<br />{/if}
