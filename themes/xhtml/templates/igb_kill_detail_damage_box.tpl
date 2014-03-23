{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table class="kb-table" width="150" cellspacing="1">
  <tr>
    <td class="kb-table-header" colspan="2" align="center">Top Damage Dealer</td>
  </tr>
{foreach from=$topdamage key=key item=i}
  <tr class="{cycle name=ccl}">
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper"><a href="{$i.pilotURL}"><img class="finalblow" height="64" width="64" src="{$i.portrait}" alt="{$i.pilotName}" title="{$i.pilotName}" border="0" /></a></div></td>
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper" onclick="CCPEVE.showPreview({$i.shipID})"><img class="finalblow" height="64" width="64" src="{$i.shipImage}" alt="{$i.shipName}" title="{$i.shipName}" border="0" /></div></td>
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
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper" onclick="CCPEVE.showPreview({$finalblow.shipID})"><img class="finalblow" height="64" width="64" src="{$finalblow.shipImage}" alt="{$i.shipName}" title="{$finalblow.shipName}" border="0" /></div></td>
  </tr>
</table>
<br />{/if}