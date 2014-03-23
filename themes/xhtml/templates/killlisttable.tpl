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
		<tr class="{cycle name=ccl}-loss" onclick="window.location.href='{$k.urldetail}';">
{elseif $k.kill}
		<tr class="{cycle name=ccl}-kill" onclick="window.location.href='{$k.urldetail}';">
{else}
		<tr class="{cycle name=ccl}" onclick="window.location.href='{$k.urldetail}';">
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
					<a href="{$k.urlvictim}"><b>{$k.victim}</b></a>
					<br />
					<a href="{$k.urlvictimcorp}">{$k.victimcorp}</a>
	{else}
		{if $k.victimalliancename != "None" && $k.victimalliancename != "NONE"}
				<a href="{$k.urlvictim}"><b>{$k.victim}</b></a><br /><a href="{$k.urlvictimall}">{$k.victimalliancename}</a>
		{else}
				<a href="{$k.urlvictim}"><b>{$k.victim}</b></a><br /><a href="{$k.urlvictimcorp}">{$k.victimcorp}</a>
		{/if}
	{/if}
				</div>
			</td>
			<td style="width: 180px" class="kb-table-cell">
				<div class="no_stretch" style="width: 180px;">
					<a href="{$k.urlfb}"><b>{$k.fb}</b></a>
					<br />
					<a href="{$k.urlfbcorp}">{$k.fbcorp}</a>
				</div>
			</td>
			<td style="width: 160px" class="kb-table-cell">
			<div class="no_stretch" style="text-align:left; width: 160px; height:auto">{if $config->get('killlist_regionnames')} {$k.region}, {$k.system}{else}<b>{$k.system}</b>{/if} ({if $k.loss || $k.kill}{$k.systemsecurity|max:0|string_format:"%01.1f"}{else}<span style="color:{if $k.systemsecurity >= 0.5}green{elseif $k.systemsecurity < 0.05}red{else}orange{/if};">{$k.systemsecurity|max:0|string_format:"%01.1f"}</span>{/if})<br /></div>
	{if $k.inv || $comments_count}
				<div style="float:left">
		{if $k.inv}<img src="{$theme_url}/img/involved10_10.png"  style="vertical-align: middle" alt="I:" /> {$k.inv}{/if}
		{if $comments_count}<span {if  !$k.commentcount}style="visibility: hidden"{/if}><img style="vertical-align: middle"src="{$theme_url}/img/comment_white13_10.gif" alt="C:" /> {$k.commentcount}</span>{/if}
				</div>{/if}
				<div style="float:right">
		{if $daybreak}
					{if $k.urlrelated}<a href="{$k.urlrelated}">{/if}<b>{$k.timestamp|date_format:"%H:%M"}</b>{if $k.urlrelated}</a>{/if}
		{else}
					{if $k.urlrelated}<a href="{$k.urlrelated}">{/if}<b>{$k.timestamp|date_format:"%y-%m-%d"} {$k.timestamp|date_format:"%H:%M"}</b>{if $k.urlrelated}</a>{/if}
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