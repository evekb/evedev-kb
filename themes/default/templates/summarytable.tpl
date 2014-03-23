<!-- summarytable.tpl -->
{if $losses}{if $verbose}{assign var=columns value=2}{assign var=width value=50}
{else}{assign var=columns value=3}{assign var=width value=33}{/if}
{else}{if $verbose}{assign var=columns value=2}{assign var=width value=50}
{else}{assign var=columns value=3}{assign var=width value=33}{/if}{/if}
<div class="summarytable">
	<table class="kb-subtable">
		<tr>
			<td style="width:{$width}%;">
				<table class="kb-table summarysubtable kb-table-rows">
					<thead>
					<tr class="kb-table-header">
						<td class="summarytable-class">Ship class</td>
{if $verbose}
						<td class="summarytable-verbose">Kills</td>
						<td class="summarytable-verbose">ISK (M)</td>
	{if $losses}					<td class="summarytable-verbose">Losses</td>
						<td class="summarytable-verbose">ISK (M)</td>
	{/if}
{else}					<td class="summarytable-brief">K</td>
	{if $losses}					<td class="summarytable-brief">L</td>
	{/if}
{/if}				</tr>
					</thead>
					<tbody>
{assign var=classcount value=0}{foreach from=$summary item=i}{assign var=classcount value=$classcount+1}
{if $classcount > ceil($count/$columns)}{assign var=classcount value=1}
					</tbody>
				</table>
			</td>
			<td style="width:{$width}%;">
				<table class="kb-table summarysubtable kb-table-rows">
					<thead>
					<tr class="kb-table-header">
						<td>Ship class</td>
	{if $verbose}					<td class="summarytable-verbose">Kills</td>
						<td class="summarytable-verbose">ISK (M)</td>
		{if $losses}					<td class="summarytable-verbose">Losses</td>
						<td class="summarytable-verbose">ISK (M)</td>
		{/if}
	{else}					<td class="summarytable-brief">K</td>
		{if $losses}					<td class="summarytable-brief">L</td>
		{/if}
	{/if}				</tr>
					</thead>
					<tbody>
{/if}
					<tr>
						<td><b><a class="kb-shipclass{if $i.hl}-hl{/if}" href="{$i.qry}scl_id={$i.id}">{$i.name}</a></b></td>
						<td class="kl-kill{if $i.kills == 0}-null{/if}">{$i.kills}</td>
{if $verbose}				<td class="kl-kill{if $i.kills == 0}-null{/if}">{$i.kisk}</td>
{/if}
{if $losses}					<td class="kl-loss{if $i.losses == 0}-null{/if}">{$i.losses}</td>
	{if $verbose}				<td class="kl-loss{if $i.losses == 0}-null{/if}">{$i.lisk}</td>
	{/if}
{/if}	</tr>
{/foreach}
					</tbody>
				</table>
			</td>
		</tr>
	</table>

{if $summarysummary}
	<div class="kb-summary-summary">
		<div class="killcount">{$kcount} Ships killed ({if $kiskB > 1 || $liskB > 1}{$kiskB}B{else}{$kiskM}M{/if} ISK)</div>
		{if $losses}<div class="losscount">{$lcount} Ships lost ({if $kiskB > 1 || $liskB > 1}{$liskB}B{else}{$liskM}M{/if} ISK)</div>{/if}
		{if $efficiency}<div class="efficiency">{$efficiency}% Efficiency (ISK)</div>{/if}
	</div>
{/if}
	{if $clearfilter}<div class="kb-summary-clear">[<a href="{$clearfilter}">clear filter</a>]</div>{/if}
</div>
<!-- /summarytable.tpl -->