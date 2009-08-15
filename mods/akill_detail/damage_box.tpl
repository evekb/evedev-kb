{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table class="kb-table" width="150" cellspacing="1">
  <tr>
    <td class="kb-table-header" colspan="2" align="center">Top Damage Dealer</td>
  </tr>
{foreach from=$topdamage key=key item=i}
  <tr class={cycle name=ccl}>
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper"><a href="{$i.PilotURL}"><img class=finalblow height="64" width="64" src="{$i.portrait}" alt="{$i.PilotName}" title="{$i.PilotName}" border="0"></a></div></td>
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper"><a href="?a=invtype&id={$i.ShipID}"><img class=finalblow height="64" width="64" src="{$i.shipImage}" alt="{$i.ShipName}" title="{$i.ShipName}" border="0"></a></div></td>
{/foreach}
</table>
<br>

<table class="kb-table" width="150" cellspacing="1">
  <tr>
    <td class="kb-table-header" colspan="2" align="center">Final Blow</td>
  </tr>
{foreach from=$involved key=key item=i}
{if $i.FB == "true"}
  <tr class={cycle name=ccl}>
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper"><a href="{$i.PilotURL}"><img class=finalblow height="64" width="64" src="{$i.portrait}" alt="{$i.PilotName}" title="{$i.PilotName}" border="0"></a></div></td>
    <td class="finalblow" align="center" width="64"><div class="menu-wrapper"><a href="?a=invtype&id={$i.ShipID}"><img class=finalblow height="64" width="64" src="{$i.shipImage}" alt="{$i.ShipName}" title="{$i.ShipName}" border="0"></a></div></td>
  </tr>
{/if}
{/foreach}
</table>
<br>