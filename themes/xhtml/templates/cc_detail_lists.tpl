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
{else}
{section name=i loop=$targets}
<br />
<div class="kb-contract-target-header">Target {$targets[i].type} - {if $targets[i].type == 'region'}{$targets[i].name}{else}<a class="kb-contract" href="{$kb_host}/?a={$targets[i].type}_detail&amp;{if $targets[i].type == 'system'}sys{elseif $targets[i].type == 'corp'}crp{elseif $targets[i].type == 'alliance'}all{/if}_id={$targets[i].id}">{$targets[i].name}</a>{/if}
</div>
{$targets[i].summary}
<br />
<table class="kb-subtable" border="0" cellspacing="0" width="100%">
	<tr>
		<td>
			<table class="kb-table" cellspacing="1" border="0" width="100%">
				<tr class="kb-table-row-even">
					<td class="kb-table-cell" width="108"><b>Totals:</b></td>
					<td class="kl-kill-bg" width="60" align="center">{$targets[i].total_kills}</td>
					<td class="kl-kill-bg" width="60" align="center">{$targets[i].total_kill_isk}B</td>
					<td class="kl-loss-bg" width="64" align="center">{$targets[i].total_losses}</td>
					<td class="kl-loss-bg" width="60" align="center">{$targets[i].total_loss_isk}B</td>
				</tr>
			</table>
		</td>
		<td align="left">
			<table class="kb-table" cellspacing="1" border="0">
				<tr class="kb-table-row-even">
					<td class="kb-table-cell" width="108"><b>Efficiency:</b></td>
					<td class="kb-table-cell" align="center" colspan="2" width="120"><b>{$targets[i].efficiency}%</b></td>
					<td class="kb-table-cell" colspan="2" width="120">{$targets[i].bar}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
{/section}
{/if}