{if $view == 'recent_activity'}
<div class="kb-kills-header">10 Most recent kills</div>
{$killtable}
<div class="kb-losses-header">10 Most recent losses</div>
{$losstable}
{elseif $view == 'kills'}
<div class="kb-kills-header">All kills</div>
{$splitter}<br /><br />
{$killtable}
{$splitter}
{elseif $view == 'losses'}
<div class="kb-kills-header">All losses</div>
{$splitter}<br /><br />
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
<td align='right'><a href='?a=alliance_detail&amp;view=corp_kills&amp;all_id={$all_id}&amp;m={$nmonth}&amp;y={$nyear}'>next</a></td></tr></table>
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
<td align='right'><a href='?a=alliance_detail&amp;view=corp_kills&amp;all_id={$all_id}&amp;m={$nmonth}&amp;y={$nyear}'>next</a></td></tr></table>
</td><td valign="top" width="400">
<div class="block-header">All time</div>
{$alllosstable}
</td></tr></table>
{else}
{$html}
{/if}