<!-- killlistable.tpl -->
<div class="kltable">
{section name=day loop=$killlist}
    {if $daybreak}
<div class="kb-date-header">{"l, F jS"|date:$killlist[day].date}</div><br />
    {/if}
{assign  var="size_boost" value=0}
{if !$comments_count}{assign  var="size_boost" value=$size_boost+11}{/if}
{if !$config->get('killlist_involved')}{assign  var="size_boost" value=$size_boost+11}{/if}
{assign var=width_victimh value=190+$size_boost}
{assign var=width_finalh value=157+$size_boost}
{assign var=width_shiph value=169+$size_boost}
{assign var=width_victim value=161+$size_boost}
{assign var=width_final value=161+$size_boost}
{assign var=width_ship value=142+$size_boost}
{if !$config->get('killlist_alogo')}{assign var=width_victim value=$width_victim+33}{/if}
<table class="kb-table kb-kl-table" style="margin-left: auto; margin-right: auto; text-align: left;" cellspacing="1">
    <tr class="kb-table-header">
        <td class="kb-table-header" colspan="2" style="width:{$width_shiph}px; text-align:center">Ship type</td>
        <td class="kb-table-header"{if $config->get('killlist_alogo')} colspan="2"{/if} style="width:{$width_victimh}px; text-align:center">Victim</td>
        <td class="kb-table-header" style="width:{$width_finalh}px">Final blow</td>
        <td class="kb-table-header" style="width: 56px; text-align:center">System</td>
        {if $config->get('killlist_involved')}
			<td class="kb-table-header" style="width: 26px; text-align:center">Inv</td>
		{/if}
        <td class="kb-table-header" style="width: 66px; text-align:center">Time</td>
    {if $comments_count}
        <td class="kb-table-header" style="width: 26px; text-align:center"><img src="{$img_url}/comment{$comment_white}.gif" alt="comments" /></td>
    {/if}
    </tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{section name=kill loop=$killlist[day].kills}
{assign var="k" value=$killlist[day].kills[kill]}
{if $k.loss}
	<tr class="{cycle name=ccl}-loss" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{elseif $k.kill}
	<tr class="{cycle name=ccl}-kill" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{else}
	<tr class="{cycle advance=false name=ccl}" onmouseout="this.className='{cycle name=ccl}';" style="cursor: pointer;"
onmouseover="this.className='kb-table-row-hover';" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{/if}
        <td class="kb-table-imgcell">
	    <img src='{$k.victimshipimage}' style="position: absolute; border: 0px; width: 32px; height: 32px;" alt="" />
		{if $k.victimshiptechlevel > 1}
		    <img src='{$img_url}/items/64_64/t{$k.victimshiptechlevel}.png' style="position: absolute; border: 0px; width: 12px; height: 12px;" alt="" />
		{elseif $k.victimshipisfaction == 1}
		    <img src='{$img_url}/items/64_64/fac.png' style="position: absolute; border: 0px; width: 12px; height: 12px;" alt="" />
		{/if}
		</td>
        <td style="width: {$width_ship}px; vertical-align: middle;"><div class="kb-shiptype"><b>{$k.victimshipname|truncate:21:"...":true}</b><br />{$k.victimshipclass|truncate:24:"...":true}</div>{if 0}<div class="kb-shipicon"><img src="{$k.victimshipindicator}" style="border-width: 0px; width: 6px; height: 6px;" alt="" /></div>{/if}</td>
        {if $config->get('killlist_alogo')}
            {if !$k.allianceexists}
		<td class="kb-table-imgcell">&nbsp;</td>
            {else}
		<td class="kb-table-imgcell"><img src="{$k.victimallianceicon}" style="border: 0px; width: 32px; height: 32px;" title="{$k.victimalliancename}" alt="{$k.victimalliancename}" /></td>
            {/if}
        {/if}
{if $k.loss}
		<td style="width: {$width_victim}px" class="kb-table-cell"><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.victimid}"><b>{$k.victim}</b></a><br /><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$k.victimcorpid}">{$k.victimcorp|truncate:35}</a></td>
{else}
	{if $k.victimalliancename != "None" && $k.victimalliancename != "NONE"}
		<td style="width: {$width_victim}px" class="kb-table-cell"><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.victimid}"><b>{$k.victim}</b></a><br /><a href="{$kb_host}/?a=alliance_detail&amp;all_id={$k.victimallianceid}">{$k.victimalliancename|truncate:35}</a></td>
	{else}
		<td style="width: {$width_victim}px" class="kb-table-cell"><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.victimid}"><b>{$k.victim}</b></a><br /><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$k.victimcorpid}">{$k.victimcorp|truncate:35}</a></td>
	{/if}
{/if}
        <td style="width: {$width_final}px" class="kb-table-cell"><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.fbid}"><b>{$k.fb}</b></a><br /><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$k.fbcorpid}">{$k.fbcorp|truncate:35}</a></td>
        <td style="text-align:center; width: 60px" class="kb-table-cell"><b>{$k.system|truncate:10}</b>{if $config->get('killlist_regionnames')} {else}<br />{/if} ({if $k.loss || $k.kill}{$k.systemsecurity|max:0|string_format:"%01.1f"}{else}<span style="color:{if $k.systemsecurity >= 0.5}green{elseif $k.systemsecurity < 0.05}red{else}orange{/if};">{$k.systemsecurity|max:0|string_format:"%01.1f"}</span>{/if}){if $config->get('killlist_regionnames')}<br />{$k.region|truncate:14}{/if}</td>
        {if $config->get('killlist_involved')}
		<td style="text-align:center; width: 30px;" class="kb-table-cell"><b>{$k.inv}</b></td>
		{/if}
        {if $daybreak}
        <td class="kb-table-cell" style="text-align:center; width:70px"><a href="{$kb_host}/?a=kill_related&amp;kll_id={$k.id}"><b>{$k.timestamp|date_format:"%H:%M"}</b></a></td>
        {else}
        <td class="kb-table-cell" style="text-align:center; width:70px"><a href="{$kb_host}/?a=kill_related&amp;kll_id={$k.id}"><b>{$k.timestamp|date_format:"%Y-%m-%d"}<br />{$k.timestamp|date_format:"%H:%M"}</b></a></td>
        {/if}
        {if $comments_count}
        <td class="kb-table-cell" style="text-align:center; width: 30px"><b>{$k.commentcount}</b></td>
        {/if}
    </tr>
    {/section}
</table>
{sectionelse}
<p>No data.</p>
{/section}
</div>
<!-- /killlistable.tpl -->