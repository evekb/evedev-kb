<!-- summarytable.tpl -->{if $losses}{if $verbose}{assign var=columns value=2}{assign var=class_width value=140}{assign var=width value=50}
{else}{assign var=columns value=3}{assign var=class_width value=180}{assign var=width value=33}{/if}
{else}{if $verbose}{assign var=columns value=2}{assign var=class_width value=200}{assign var=width value=50}
{else}{assign var=columns value=3}{assign var=class_width value=130}{assign var=width value=33}{/if}{/if}
<div class="summarytable">
<table class="kb-subtable" width="760" border="0" cellspacing="0">
	<tr>
		<td valign="top" style="width:{$width}%;">
			<table class="kb-table" cellspacing="1" width="100%">
				<tr class="kb-table-header">
					<td class="kb-table-cell" style="width:{$class_width};">Ship class</td>
{if $verbose}
					<td class="kb-table-cell" style="width:60px; text-align:center">Kills</td>
					<td class="kb-table-cell" style="width:60px; text-align:center">ISK (M)</td>
	{if $losses}					<td class="kb-table-cell" style="width:60px; text-align:center">Losses</td>
					<td class="kb-table-cell" style="width:60px; text-align:center">ISK (M)</td>
	{/if}
{else}					<td class="kb-table-cell" style="width:30px; text-align:center">K</td>
	{if $losses}					<td class="kb-table-cell" style="width:30px; text-align:center">L</td>
	{/if}
{/if}	</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{assign var=classcount value=0}{foreach from=$summary item=i}{assign var=classcount value=$classcount+1}
{if $classcount > ceil($count/$columns)}{assign var=classcount value=1}{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
			</table></td><td style="width:{$width}%; vertical-align:top"><table class="kb-table" cellspacing="1" width="100%">
				<tr class="kb-table-header">
					<td class="kb-table-cell" style="width:{$class_width}px;">Ship class</td>
	{if $verbose}					<td class="kb-table-cell" style="width:60px; text-align:center">Kills</td>
					<td class="kb-table-cell" style="width:60px; text-align:center">ISK (M)</td>
		{if $losses}					<td class="kb-table-cell" style="width:60px; text-align:center">Losses</td>
					<td class="kb-table-cell" style="width:60px; text-align:center">ISK (M)</td>
		{/if}
	{else}					<td class="kb-table-cell" style="width:30px; text-align:center">K</td>
		{if $losses}					<td class="kb-table-cell" style="width:30px; text-align:center">L</td>
		{/if}
	{/if}				</tr>
{/if}
	<tr class="{cycle name=ccl}">
					<td style="white-space: nowrap" class="kb-table-cell"><b><a class="kb-shipclass{if $i.hl}-hl{/if}" href="?{$i.qry}&amp;scl_id={$i.id}">{$i.name}</a></b></td>
					<td class="kl-kill{if $i.kills == 0}-null{/if}" style="text-align:center">{$i.kills}</td>
{if $verbose}				<td class="kl-kill{if $i.kills == 0}-null{/if}" style="text-align:center">{$i.kisk}</td>
{/if}
{if $losses}					<td class="kl-loss{if $i.losses == 0}-null{/if}" style="text-align:center">{$i.losses}</td>
	{if $verbose}				<td class="kl-loss{if $i.losses == 0}-null{/if}" style="text-align:center">{$i.lisk}</td>
	{/if}
{/if}	</tr>
{/foreach}
			</table>
		</td>
	</tr>
</table>

{if $summarysummary}
	{if $efficiency && $losses}
<table style="text-align:center; width:90%;" border="0" cellspacing="2">
	<tr align="center">
		<td width='30%'><span style="text-align:right;" class="killcount">{$kcount} Ships killed ({if $kiskB > 1 || $liskB > 1}{$kiskB}B{else}{$kiskM}M{/if} ISK)</span></td>
		<td width='30%'><span style="text-align:center;" class="losscount">{$lcount} Ships lost ({if $kiskB > 1 || $liskB > 1}{$liskB}B{else}{$liskM}M{/if} ISK)</span></td>
		<td width='30%'><span style="text-align:left;" class="efficency">{$efficiency}% Efficiency (ISK)</span></td>
	</tr>
</table>
{else}
<table style="text-align:center; width:90%;" border="0" cellspacing="2">
	<tr align="center">
{if $losses}		<td width='51%'><span style="text-align:right;" class="killcount">{$kcount} Ships killed ({if $kiskB > 1 || $liskB > 1}{$kiskB}B{else}{$kiskM}M{/if} ISK)</span></td>
		<td width='49%'><span style="text-align:center;" class="losscount">{$lcount} Ships lost ({if $kiskB > 1 || $liskB > 1}{$liskB}B{else}{$liskM}M{/if} ISK)</span></td>
{else}		<td width='100%'><span style="text-align:right;" class="killcount">{$kcount} Ships killed ({if $kiskB > 1 || $liskB > 1}{$kiskB}B{else}{$kiskM}M{/if} ISK)</span></td>
{/if}	</tr>
</table>
	{/if}
{/if}
{if $clearfilter}<table align="center"><tr><td style="text-align:center; vertical-align:top" class="weeknav">[<a href="{$clearfilter}">clear filter</a>]</td></tr></table>{/if}
</div>
<!-- /summarytable.tpl -->
