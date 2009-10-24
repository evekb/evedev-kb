 <!-- killlistable.tpl -->
{section name=day loop=$killlist}
    {if $daybreak}
<div class="kb-date-header">{"l, F jS"|date:$killlist[day].date}</div><br />
    {/if}
<div style="text-align: center;"><table class="kb-table" style="width: 100%; margin-left: auto; margin-right: auto; text-align: left;" cellspacing="1">
    <tr class="kb-table-header">
        <td class="kb-table-header" colspan="2" align="center">Ship type</td>
        <td class="kb-table-header"{if $config->get('killlist_alogo')} colspan="2"{/if}>Victim</td>
        <td class="kb-table-header">Final blow</td>
        <td class="kb-table-header" align="center">System</td>
        {if $config->get('killlist_involved')}
			<td class="kb-table-header" align="center">Inv.</td>
		{/if}
        <td class="kb-table-header" align="center">Time</td>
    {if $comments_count}
        <td class="kb-table-header" align="center"><img src="{$img_url}/comment{$comment_white}.gif" alt="comments" /></td>
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
<tr class="{cycle advance=false name=ccl}" onmouseout="this.className='{cycle name=ccl}';" style="height: 34px; cursor: pointer;"
onmouseover="this.className='kb-table-row-hover';" onclick="window.location.href='?a=kill_detail&amp;kll_id={$k.id}';">
{/if}
        <td style="width: 32px" align="center"><img src="{$k.victimshipimage}" style="border-width: 0px; width: 32px; height: 32px;" alt="" /></td>
        <td style="height: 34px; width: 180px; vertical-align: middle;"><div class="kb-shiptype"><b>{$k.victimshipname|truncate:21:"...":true}</b><br />{$k.victimshipclass|truncate:24:"...":true}</div><div class="kb-shipicon"><img src="{$k.victimshipindicator}" style="border-width: 0px; width: 6px; height: 6px;" alt="" /></div></td>
        {if $config->get('killlist_alogo')}
            {if $k.allianceexists}
            <td style="width: 32px" align="center"><img src="{$k.victimallianceicon}" style="border-width: 0px; width: 32px; height: 32px;" title="{$k.victimalliancename}" alt="{$k.victimalliancename}" /></td>
            {elseif $k.victimalliancename != "None"}
            <td style="width: 32px" align="center"><img src="{$img_url}/alliances/default_32.png" style="border-width: 0px; width: 32px; height:32px;" title="{$k.victimalliancename}" alt="{$k.victimalliancename}" /></td>
            {else}
            <td style="width: 32px" align="center">&nbsp;</td>
            {/if}
        {/if}
{if $k.loss}
	<td style="width: 235px" class="kb-table-cell"><b>{$k.victim}</b><br />{if $config->get('corpalliance-name')}<b>Corp:</b> {$k.victimcorp|truncate:27}{else}{$k.victimcorp|truncate:31}{/if}</td>
{else}
        {if $config->get('corpalliance-name')}
			{if $k.victimalliancename != "None"}
			<td style="width: 235px" class="kb-table-cell"><b>{$k.victim}</b><br /><b>Alliance:</b> {$k.victimalliancename|truncate:27}</td>
			{else}
			<td style="width: 235px" class="kb-table-cell"><b>{$k.victim}</b><br /><b>Corp:</b> {$k.victimcorp|truncate:30}</td>
			{/if}
		{else}
			<td style="width: 235px" class="kb-table-cell"><b>{$k.victim}</b><br />{$k.victimcorp|truncate:35}</td>
		{/if}
{/if}
        <td style="width: 190px" class="kb-table-cell"><b>{$k.fb}</b><br />{$k.fbcorp|truncate:27}</td>
        <td style="width: 100px" class="kb-table-cell" align="center"><b>{$k.system|truncate:10}</b><br />({$k.systemsecurity|max:0|string_format:"%01.1f"})</td>
        {if $config->get('killlist_involved')}
			<td style="width: 30px" align="center" class="kb-table-cell"><b>{$k.inv}</b></td>
		{/if}
        {if $daybreak}
        <td class="kb-table-cell" align="center"><b>{$k.timestamp|date_format:"%H:%M"}</b></td>
        {else}
        <td class="kb-table-cell" align="center" width="80"><b>{$k.timestamp|date_format:"%Y-%m-%d"}<br />{$k.timestamp|date_format:"%H:%M"}</b></td>
        {/if}
        {if $comments_count}
        <td style="width: 10px" class="kb-table-cell" align="center"><b>{$k.commentcount}</b></td>
        {/if}
    </tr>
    {/section}
</table></div>
{sectionelse}
<p>No data.</p>
{/section} <!-- /killlistable.tpl -->