<!-- cc_detail_lists.tpl -->
<div class="contract-list">
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
<div class="kb-contract-target-header">Target {$targets[i].type} - {if $targets[i].type == 'region'}{$targets[i].name}{else}<a class="kb-contract" href="{$kb_host}/?a={$targets[i].type}_detail&amp;{if $targets[i].type == 'system'}sys{elseif $targets[i].type == 'corp'}crp{elseif $targets[i].type == 'alliance'}all{/if}_id={$targets[i].id}">{$targets[i].name}</a>{/if}
</div>
{$targets[i].summary}
<table class="kb-subtable contract-list-summary">
	<tr>
		<td>
			<table class="kb-table contract-list-totals">
				<tr class="kb-table-row-even">
					<td class="contract-summary-name">Totals:</td>
					<td class="kl-kill-bg">{$targets[i].total_kills}</td>
					<td class="kl-kill-bg">{$targets[i].total_kill_isk}B</td>
					<td class="kl-loss-bg">{$targets[i].total_losses}</td>
					<td class="kl-loss-bg">{$targets[i].total_loss_isk}B</td>
				</tr>
			</table>
		</td>
		<td>
			<table class="kb-table contract-list-efficiency">
				<tr class="kb-table-row-even">
					<td class="contract-summary-name">Efficiency:</td>
					<td class="efficiency_percent">{$targets[i].efficiency}%</td>
					<td class="efficiency_bar">{$targets[i].bar}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
{/section}
{/if}
</div>
<!-- /cc_detial_lists.tpl -->