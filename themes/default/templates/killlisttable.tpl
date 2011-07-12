<!-- killlistable.tpl -->
<div class="kltable">
{section name=day loop=$killlist}
	{if $daybreak}
	<div class="kb-date-header">{"l, F jS"|date:$killlist[day].date}</div><br />
	{/if}
{assign var=width_victim value=190}
{if !$config->get('killlist_alogo')}{assign var=width_victim value=$width_victim+33}{/if}
	<table class="kb-table kb-kl-table" style="margin-left: auto; margin-right: auto; text-align: left;" cellspacing="1">
		<tr class="kb-table-header">
			<td class="kb-table-header" colspan="2" style="width:170px; text-align:center">Ship type</td>
			<td class="kb-table-header"{if $config->get('killlist_alogo')} colspan="2"{/if} style="width:209px; text-align:center">Victim</td>
			<td class="kb-table-header" style="width:176px; text-align:center">Final blow</td>
			<td class="kb-table-header" style="width: 147px; text-align:center">Location</td>
		</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{section name=kill loop=$killlist[day].kills}
{assign var="k" value=$killlist[day].kills[kill]}
{if $k.loss}
		<tr class="{cycle name=ccl}-loss" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{elseif $k.kill}
		<tr class="{cycle name=ccl}-kill" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{else}
		<tr class="{cycle name=ccl}" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{/if}
			<td class="kb-table-imgcell">
				<img src='{$k.victimshipimage}' style="border: 0px; width: 32px; height: 32px;" alt="" />
			</td>
			<td class="kb-table-cell" style="width: 153px; vertical-align: middle;">
				<div class="no_stretch" style="width: 153px;">
					<b>{$k.victimshipname}</b>
					<br />
					{$k.victimshipclass}
				</div>
			</td>
			{if $config->get('killlist_alogo')}
				{if !$k.allianceexists}
			<td class="kb-table-imgcell">&nbsp;</td>
				{else}
			<td class="kb-table-imgcell"><img src="{$k.victimallianceicon}" style="border: 0px; width: 32px; height: 32px;" title="{$k.victimalliancename}" alt="{$k.victimalliancename}" /></td>
				{/if}
			{/if}
			<td style="width: {$width_victim}px" class="kb-table-cell">
				<div class="no_stretch" style="width: {$width_victim}px;">
	{if $k.loss}
					<a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.victimid}"><b>{$k.victim}</b></a>
					<br />
					<a href="{$kb_host}/?a=corp_detail&amp;crp_id={$k.victimcorpid}">{$k.victimcorp}</a>
	{else}
		{if $k.victimalliancename != "None" && $k.victimalliancename != "NONE"}
				<a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.victimid}"><b>{$k.victim}</b></a><br /><a href="{$kb_host}/?a=alliance_detail&amp;all_id={$k.victimallianceid}">{$k.victimalliancename}</a>
		{else}
				<a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.victimid}"><b>{$k.victim}</b></a><br /><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$k.victimcorpid}">{$k.victimcorp}</a>
		{/if}
	{/if}
				</div>
			</td>
			<td style="width: 180px" class="kb-table-cell">
				<div class="no_stretch" style="width: 180px;">
					<a href="{$kb_host}/?a=pilot_detail&amp;plt_id={$k.fbid}"><b>{$k.fb}</b></a>
					<br />
					<a href="{$kb_host}/?a=corp_detail&amp;crp_id={$k.fbcorpid}">{$k.fbcorp}</a>
				</div>
			</td>
			<td style="width: 160px" class="kb-table-cell">
			<div class="no_stretch" style="text-align:left; width: 160px; height:auto">{if $config->get('killlist_regionnames')} {$k.region}, {$k.system}{else}<b>{$k.system}</b>{/if} ({if $k.loss || $k.kill}{$k.systemsecurity|max:0|string_format:"%01.1f"}{else}<span style="color:{if $k.systemsecurity >= 0.5}green{elseif $k.systemsecurity < 0.05}red{else}orange{/if};">{$k.systemsecurity|max:0|string_format:"%01.1f"}</span>{/if})<br /></div>
	{if $config->get('killlist_involved') || $comments_count}
				<div style="float:left">
		{if $comments_count}<img style="vertical-align: middle"src="{$theme_url}/img/comment_white13_10.gif" alt="C:" /> {$k.commentcount}{/if}
		{if $config->get('killlist_involved')}<img src="{$theme_url}/img/involved10_10.png"  style="vertical-align: middle" alt="I:" /> {$k.inv}{/if}
				</div>{/if}
				<div style="float:right">
		{if $daybreak}
					<a href="{$kb_host}/?a=kill_related&amp;kll_id={$k.id}"><b>{$k.timestamp|date_format:"%H:%M"}</b></a>
		{else}
					<a href="{$kb_host}/?a=kill_related&amp;kll_id={$k.id}"><b>{$k.timestamp|date_format:"%d.%m.%y"} {$k.timestamp|date_format:"%H:%M"}</b></a>
		{/if}
				</div>
			</td>
		</tr>
	{/section}
	</table>
{sectionelse}
	<p>No data.</p>
{/section}
</div>
<!-- /killlistable.tpl -->