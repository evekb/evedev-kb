{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table class="kb-table kb-box">
  <tr>
    <td class="kb-table-header" colspan="2">Top Damage Dealer</td>
  </tr>
{foreach from=$topdamage key=key item=i}
  <tr class="{cycle name=ccl}">
    <td class="finalblow"><div class="menu-wrapper"><a href="{$i.pilotURL}"><img class="finalblow" src="{$i.portrait}" alt="{$i.pilotName}" title="{$i.pilotName}" /></a></div></td>
    <td class="finalblow"><div class="menu-wrapper" onclick="CCPEVE.showPreview({$i.shipID})"><img class="finalblow" src="{$i.shipImage}" alt="{$i.shipName}" title="{$i.shipName}" /></div></td>
  </tr>
{/foreach}
</table>
{if $finalblow}
<table class="kb-table kb-box">
  <tr>
    <td class="kb-table-header" colspan="2">Final Blow</td>
  </tr>
  <tr class="{cycle name=ccl}">
    <td class="finalblow"><div class="menu-wrapper"><a href="{$finalblow.pilotURL}"><img class="finalblow" src="{$finalblow.portrait}" alt="{$finalblow.pilotName}" title="{$finalblow.pilotName}" /></a></div></td>
    <td class="finalblow"><div class="menu-wrapper" onclick="CCPEVE.showPreview({$finalblow.shipID})"><img class="finalblow" src="{$finalblow.shipImage}" alt="{$i.shipName}" title="{$finalblow.shipName}" /></div></td>
  </tr>
</table>{/if}