<!-- summarytable.tpl --><table class=kb-subtable width="100%" border="0" cellspacing=0>
	<tr>
		<td valign=top width="{$width}%">
			<table class=kb-table cellspacing="1" width="100%">
				<tr class=kb-table-header>
					<td class=kb-table-cell width={$width_abs}>Ship class</td>
{if $verbose}
					<td class=kb-table-cell width=60 align=center>Kills</td>
					<td class=kb-table-cell width=60 align=center>ISK (M)</td>
	{if $losses}					<td class=kb-table-cell width=60 align=center>Losses</td>
					<td class=kb-table-cell width=60 align=center>ISK (M)</td>
	{/if}
{else}					<td class=kb-table-cell width=30 align=center>K</td>
	{if $losses}					<td class=kb-table-cell width=30 align=center>L</td>
	{/if}
{/if}	</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$summary item=i}
{if $i.break }{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
			</table></td><td valign=top width="{$width}%"><table class=kb-table cellspacing="1" width="100%">
				<tr class=kb-table-header>
					<td class=kb-table-cell width={$width_abs}>Ship class</td>
	{if $verbose}					<td class=kb-table-cell width=60 align=center>Kills</td>
					<td class=kb-table-cell width=60 align=center>ISK (M)</td>
		{if $losses}					<td class=kb-table-cell width=60 align=center>Losses</td>
					<td class=kb-table-cell width=60 align=center>ISK (M)</td>
		{/if}
	{else}					<td class=kb-table-cell width=30 align=center>K</td>
		{if $losses}					<td class=kb-table-cell width=30 align=center>L</td>
		{/if}
	{/if}				</tr>
{/if}
	<tr class={cycle name=ccl}>
					<td nowrap class=kb-table-cell><b><a class=kb-shipclass{if $i.hl}-hl{/if} href="?{$i.qry}&amp;scl_id={$i.id}">{$i.name}</a></b></td>
					<td class=kl-kill{if $i.kills == 0}-null{/if} align=center>{$i.kills}</td>
{if $verbose}				<td class=kl-kill{if $i.kills == 0}-null{/if} align=center>{$i.kisk}</td>
{/if}
{if $losses}					<td class=kl-loss{if $i.losses == 0}-null{/if} align=center>{$i.losses}</td>
	{if $verbose}				<td class=kl-loss{if $i.losses == 0}-null{/if} align=center>{$i.lisk}</td>
	{/if}
{/if}	</tr>
{/foreach}
			</table>
		</td>
	</tr>
</table>

{if $summarysummary}
	{if $efficiency && $losses}
<table style="text-align:center; width:90%;" border=0 cellspacing=2>
	<tr align=center>
		<td width='30%'><span style="text-align:right;" class="killcount">{$kcount} Ships killed ({if $kiskB > 1 || $liskB > 1}{$kiskB}B{else}{$kiskM}M{/if} ISK)</span></td>
		<td width='30%'><span style="text-align:center;" class="losscount">{$lcount} Ships lost ({if $kiskB > 1 || $liskB > 1}{$liskB}B{else}{$liskM}M{/if} ISK)</span></td>
		<td width='30%'><span style="text-align:left;" class="efficency">{$efficiency}% Efficiency (ISK)</span></td>
	</tr>
</table>
{else}
<table style="text-align:center; width:90%;" border=0 cellspacing=2>
	<tr align=center>
{if $losses}		<td width='51%'><span style="text-align:right;" class="killcount">{$kcount} Ships killed ({if $kiskB > 1 || $liskB > 1}{$kiskB}B{else}{$kiskM}M{/if} ISK)</span></td>
		<td width='49%'><span style="text-align:center;" class="losscount">{$lcount} Ships lost ({if $kiskB > 1 || $liskB > 1}{$liskB}B{else}{$liskM}M{/if} ISK)</span></td>
{else}		<td width='100%'><span style="text-align:right;" class="killcount">{$kcount} Ships killed ({if $kiskB > 1 || $liskB > 1}{$kiskB}B{else}{$kiskM}M{/if} ISK)</span></td>
{/if}	</tr>
</table>
	{/if}
{/if}
{if $clearfilter}<table align=center><tr><td align=center valign=top class=weeknav>[<a href="{$clearfilter}">clear filter</a>]</td></tr></table>{/if}
<!-- /summarytable.tpl -->