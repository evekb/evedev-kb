<!-- killlistable.tpl -->
<div class="kltable">
{section name=day loop=$killlist}
    {if $daybreak}
<div class="kb-date-header">{"l, F jS"|date:$killlist[day].date}</div><br />
    {/if}
<table class="kb-table kb-kl-table" style="margin-left: auto; margin-right: auto; text-align: left;" cellspacing="1">
    <tr class="kb-table-header">
        <td class="kb-table-header" colspan="2" style="width:188px; text-align:center">Ship type</td>
        <td class="kb-table-header"{if $config->get('killlist_alogo')} colspan="2"{/if} style="width:183px; text-align:center">Victim</td>
        <td class="kb-table-header" style="width:158px">Final blow</td>
        <td class="kb-table-header" style="width: 58px; text-align:center">System</td>
        {if $config->get('killlist_involved')}
			<td class="kb-table-header" style="width: 28px; text-align:center">Inv.</td>
		{/if}
        <td class="kb-table-header" style="width: 88px; text-align:center">Time</td>
    {if $comments_count}
        <td class="kb-table-header" style="width: 28px; text-align:center"><img src="{$img_url}/comment{$comment_white}.gif" alt="comments" /></td>
    {/if}
    </tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{section name=kill loop=$killlist[day].kills}
{assign var="k" value=$killlist[day].kills[kill]}
{if $config->get('killlist_involved')}{assign var="inv" value=30}{else}{assign var="inv" value=0}{/if}
{if $comments_count}{assign var="inv" value=30}{else}{assign var="inv" value=0}{/if}
{if $k.loss}
<tr class="{cycle name=ccl}-loss" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{elseif $k.kill}
<tr class="{cycle name=ccl}-kill" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{else}
<tr class="{cycle advance=false name=ccl}" onmouseout="this.className='{cycle name=ccl}';" style="height: 34px; cursor: pointer;"
onmouseover="this.className='kb-table-row-hover';" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{/if}
        <td style="text-align:center; width: 32px" ><img src="{$k.victimshipimage}" style="border: 0px; width: 32px; height: 32px;" alt="" /></td>
        <td style="height: 34px; width: 158px; vertical-align: middle;"><div class="kb-shiptype"><b>{$k.victimshipname|truncate:21:"...":true}</b><br />{$k.victimshipclass|truncate:24:"...":true}</div>{if 0}<div class="kb-shipicon"><img src="{$k.victimshipindicator}" style="border-width: 0px; width: 6px; height: 6px;" alt="" /></div>{/if}</td>
        {if $config->get('killlist_alogo')}
            {if !$k.allianceexists}
            <td style="text-align:center; width: 32px">&nbsp;</td>
            {else}
            <td style="text-align:center; width: 32px"><img src="{$k.victimallianceicon}" style="border: 0px; width: 32px; height: 32px;" title="{$k.victimalliancename}" alt="{$k.victimalliancename}" /></td>
            {/if}{assign var=victim_width value=163}
        {else}{assign var=victim_width value=195}{/if}
{if $k.loss}
	<td style="width: {$victim_width}px" class="kb-table-cell"><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.victimid}"><b>{$k.victim}</b></a><br /><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$k.victimcorpid}">{$k.victimcorp|truncate:35}</a></td>
{else}
		{if $k.victimalliancename != "None"}
		<td style="width: {$victim_width}px" class="kb-table-cell"><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.victimid}"><b>{$k.victim}</b></a><br /><a href="{$kb_host}/?a=alliance_detail&amp;all_id={$k.victimallianceid}">{$k.victimalliancename|truncate:35}</a></td>
		{else}
		<td style="width: {$victim_width}px" class="kb-table-cell"><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.victimid}"><b>{$k.victim}</b></a><br /><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$k.victimcorpid}">{$k.victimcorp|truncate:35}</a></td>
		{/if}
{/if}
        <td style="width: 160px" class="kb-table-cell"><a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.fbid}"><b>{$k.fb}</b></a><br /><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$k.fbcorpid}">{$k.fbcorp|truncate:35}</a></td>
        <td style="text-align:center; width: 60px" class="kb-table-cell"><b>{$k.system|truncate:10}</b>{if $config->get('killlist_regionnames')} {else}<br />{/if} ({if $k.loss || $k.kill}{$k.systemsecurity|max:0|string_format:"%01.1f"}{else}<span style="color:{if $k.systemsecurity >= 0.5}green{elseif $k.systemsecurity < 0.05}red{else}orange{/if};">{$k.systemsecurity|max:0|string_format:"%01.1f"}</span>{/if}){if $config->get('killlist_regionnames')}<br />{$k.region|truncate:14}{/if}</td>
        {if $config->get('killlist_involved')}
			<td style="text-align:center; width: 30px;" class="kb-table-cell"><b>{$k.inv}</b></td>
		{/if}
        {if $daybreak}
        <td class="kb-table-cell" style="text-align:center; width:90px"><a href="{$kb_host}/?a=kill_related&amp;kll_id={$k.id}"><b>{$k.timestamp|date_format:"%H:%M"}</b></a></td>
        {else}
        <td class="kb-table-cell" style="text-align:center; width:90px"><a href="{$kb_host}/?a=kill_related&amp;kll_id={$k.id}"><b>{$k.timestamp|date_format:"%Y-%m-%d"}<br />{$k.timestamp|date_format:"%H:%M"}</b></a></td>
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