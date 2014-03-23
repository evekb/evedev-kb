<br/>
<div class="kb-kills-header">Battle Summary for {$system}, {$firstts|date_format:"%Y-%m-%d %H:%M"} - {$lastts|date_format:"%H:%M"}</div>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr><td width="49%" valign="top">
<div class="kb-date-header">Friendly ({$friendlycnt})</div>
<br/>

{assign var='loop' value=$pilots_a}
<table class="kb-table" width="95%" align="center">
    <tr class="kb-table-header">
      <td class="kb-table-header" colspan="2" align="center">Pilot/Ship</td>
      <td class="kb-table-header" align="center" style="min-width: 45%; width: 45%; max-width: 45%;">Corp/Alliance</td>
    </tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$loop item=a key=pilot}
{foreach from=$a item=i key=b}
    <tr class="{cycle name=ccl}"{if $i.destroyed} style="background-color: #EE4444;"{/if}>
      <td width="32" height="32" style="max-width: 32px;">
{if $i.destroyed}
		  <a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.kll_id}"><img src="{$i.spic}" width="32" height="32" border="0" alt="" /></a>
{else}
        <img src="{$i.spic}" width="32" height="32" border="0" alt="" />
{/if}
      </td>
{if $i.podded}
    {if $config->get('bs_podlink')}
      <td class="kb-table-cell">
        <b><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$pilot}">{$i.name}</a>&nbsp;<a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.podid}">[Pod]</a></b><br/>{$i.ship}
      </td>
    {else}
      <td class="kb-table-cell"><div style="position: relative;"><b><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$pilot}">{$i.name}</a></b><br/>{$i.ship}
	<div style="position: absolute; right:0px; top:-6px; width:32px; height:32px; z-index:1;"><a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.podid}"><img src="{$podpic}" alt="" /></a></div>
        </div>
    </td>
    {/if}
{else}
      <td class="kb-table-cell"><b><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$pilot}">{$i.name}</a></b><br/>{$i.ship}</td>
{/if}
      <td class="kb-table-cell"><b><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$i.cid}">{$i.corp}</a></b><br/><a href="{$kb_host}/?a=alliance_detail&amp;all_id={$i.aid}" style="font-weight: normal;">{$i.alliance}</a></td>
    </tr>
{/foreach}
{/foreach}
</table>

</td><td width="55%" valign="top">
<div class="kb-date-header">Hostile ({$hostilecnt})</div>
<br/>

{assign var='loop' value=$pilots_e}
<table class="kb-table" width="95%" align="center">
    <tr class="kb-table-header">
      <td class="kb-table-header" colspan="2" align="center">Pilot/Ship</td>
      <td class="kb-table-header" align="center" style="min-width: 45%; width: 45%; max-width: 45%;">Corp/Alliance</td>
    </tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$loop item=a key=pilot}
{foreach from=$a item=i key=b}
    <tr class="{cycle name=ccl}"{if $i.destroyed} style="background-color: #EE4444;"{/if}>
      <td width="32" height="32" style="max-width: 32px;">
{if $i.destroyed}
        <a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.kll_id}"><img src="{$i.spic}" width="32" height="32" border="0" alt="" /></a>
{else}
        <img src="{$i.spic}" width="32" height="32" border="0" alt="" />
{/if}
      </td>
{if $i.podded}
    {if $config->get('bs_podlink')}
      <td class="kb-table-cell">
        <b><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$pilot}">{$i.name}</a>&nbsp;<a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.podid}">[Pod]</a></b><br/>{$i.ship}
      </td>
    {else}
    <td class="kb-table-cell"><div style="position: relative;"><b><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$pilot}">{$i.name}</a></b><br/>{$i.ship}
	    <div style="position: absolute; right:0px; top:-6px; width:32px; height:32px; z-index:1;"><a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.podid}"><img src="{$podpic}" alt="" /></a></div>
        </div>
    </td>
    {/if}
{else}
      <td class="kb-table-cell"><b><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$pilot}">{$i.name}</a></b><br/>{$i.ship}</td>
{/if}
      <td class="kb-table-cell"><b><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$i.cid}">{$i.corp}</a></b><br/><a href="{$kb_host}/?a=alliance_detail&amp;all_id={$i.aid}" style="font-weight: normal;">{$i.alliance}</a></td>
    </tr>
{/foreach}
{/foreach}
</table>
</td>
</tr>
</table>
<br/>