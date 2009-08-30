<table class="kb-table" width="100%" border="0" cellspacing="1">
	<tr class="kb-table-row-even">
		<td rowspan="8" width="128" align="center" bgcolor="black">
			<img src="{$img_url}/alliances/{if $all_img == 'default'}default.gif{else}{$all_img}.png{/if}" alt="{$all_name}" width="128" height="128" border="0" />
		</td>
		<td class="kb-table-cell" width="180"><b>Kills:</b></td>
		<td class="kl-kill">{$totalkills}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Losses:</b></td>
		<td class="kl-loss">{$totallosses}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Damage done (ISK):</b></td>
		<td class="kl-kill">{$totalkisk}B</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Damage received (ISK):</b></td>
		<td class="kl-loss">{$totallisk}B</td>
	</tr>
	<tr class="kb-table-row-even">
		<td class="kb-table-cell"><b>Efficiency:</b></td>
		<td class="kb-table-cell"><b>{$efficiency}</b></td>
	</tr>
</table>
<br/>

{if $view == 'recent_activity'}
{$summary}
<div class="kb-kills-header">10 Most recent kills</div>
{$killtable}
<div class="kb-losses-header">10 Most recent losses</div>
{$losstable}
{elseif $view == 'kills'}
{$summary}
<div class="kb-kills-header">All kills</div>
{$killtable}
{$splitter}
{elseif $view == 'losses'}
{$summary}
<div class="kb-kills-header">All losses</div>
{$losstable}
{$splitter}
{elseif $view=='ships_weapons'}
<div class="block-header2">Ships &amp; weapons used</div>
<table class="kb-subtable">
	<tr>
		<td valign="top" width="400">
{$shiplisttable}
		</td>
		<td valign="top" align="right" width="400">
{$weaponlisttable}
		</td>
	</tr>
</table>
{elseif $view=='pilot_losses'}
<div class="block-header2">Top losers</div>
<table class="kb-subtable">
<tr>
<td valign="top" width="440">
<div class="block-header">{$monthname} {$year}</div>
{$losstable}
<table width="300" cellspacing="1"><tr><td><a href='?a=alliance_detail&amp;view=pilot_losses&amp;m={$pmonth}&amp;all_id={$all_id}&amp;y={$pyear}'>previous</a></td>
<td align='right'><a href='?a=alliance_detail&amp;view=pilot_losses&amp;all_id={$all_id}&amp;m={$nmonth}&amp;y={$nyear}'>next</a></td></tr></table>
</td><td valign="top" width="400">
<div class="block-header">All time</div>
{$totallosstable}
</td></tr></table>
{elseif $view=='corp_kills'}
<div class="block-header2">Top killers</div>
<table class="kb-subtable"><tr><td valign="top" width="440">
<div class="block-header">{$monthname} {$year}</div>
{$killtable}
<table width="300" cellspacing="1"><tr><td><a href='?a=alliance_detail&amp;view=corp_kills&amp;m={$pmonth}&amp;all_id={$all_id}&amp;y={$pyear}'>previous</a></td>
<td align='right'><a href='?a=alliance_detail&amp;view=corp_kills&amp;all_id={$all_id}&amp;m={$nmonth}&amp;y={$nyear}'>next</a></p></td></tr></table>
</td><td valign="top" width="400">
<div class="block-header">All time</div>
{$allkilltable}
</td></tr></table>
{elseif $view=='corp_losses'}
<div class="block-header2">Top losers</div>
<table class="kb-subtable"><tr><td valign="top" width="440">
<div class="block-header">{$monthname} {$year}</div>
{$losstable}
<table width="300" cellspacing="1"><tr><td><a href='?a=alliance_detail&amp;view=corp_kills&amp;m={$pmonth}&amp;all_id={$all_id}&amp;y={$pyear}'>previous</a></td>
<td align='right'><a href='?a=alliance_detail&amp;view=corp_kills&amp;all_id={$all_id}&amp;m={$nmonth}&amp;y={$nyear}'>next</a></p></td></tr></table>
</td><td valign="top" width="400">
<div class="block-header">All time</div>
{$alllosstable}
</td></tr></table>
{else}
{$html}
{/if}