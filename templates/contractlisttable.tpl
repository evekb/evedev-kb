<table class="kb-table" width="98%" align="center" cellspacing="1">
 <tr class="kb-table-header"><td class="kb-table-cell" width="180">Name</td>
  <td class="kb-table-cell" width="80" align=center>Start date</td>
{if $contract_getactive == "no"}
  <td class="kb-table-cell" width="80" align="center">End date</td>
{/if}
  <td class="kb-table-cell" width="50" align="center">Kills</td>
  <td class="kb-table-cell" width="70" align="center">ISK (B)</td>
  <td class="kb-table-cell" width="50" align="center">Losses</td>
  <td class="kb-table-cell" width="70" align="center">ISK (B)</td>
  <td class="kb-table-cell" width="70" align="center" colspan=2>Efficiency</td>
 </tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$contracts item=i}
 <tr class="{cycle advance=false name=ccl}" onmouseover="this.className='kb-table-row-hover';"
    onmouseout="this.className='{cycle name=ccl}';" onClick="window.location.href='?a=cc_detail&amp;ctr_id={$i.id}';">
  <td class="kb-table-cell"><b>{$i.name}</b></td>
  <td class="kb-table-cell" align="center">{$i.startdate|truncate:10:""}</td>
{if $contract_getactive == "no"}
 {if $i.enddate}
  <td class="kb-table-cell" align="center">{$i.enddate|truncate:10:""}</td>
 {else}
  <td class="kb-table-cell" align="center">Active</td>
 {/if}
{/if}
  <td class="kl-kill" align="center">{$i.kills}</td>
  <td class="kl-kill" align="center">{$i.killisk/1000000|string_format:"%.2f"}</td>
  <td class="kl-loss" align="center">{$i.losses}</td>
  <td class="kl-loss" align="center">{$i.lossisk/1000000|string_format:"%.2f"}</td>
  <td class="kb-table-cell" align="center" width="40"><b>{$i.efficiency}</b></td>
  <td class="kb-table-cell" align="left" width="75">{$i.bar}</td>
 </tr>
{/foreach}
</table>