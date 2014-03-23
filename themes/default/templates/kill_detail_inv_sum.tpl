<div class="kl-detail-invsum">
	<div class="block-header">Involved parties: {$involvedPartyCount}</div>
	{if $showext && $involvedPartyCount > 4}
		<table class="kb-table">
			<tr class="kb-table-row-even" >
				<td class="invcorps">
					<div class="no_stretch">
						{foreach from=$invAllies key="key" item="l"}
							<div class="kb-table-header">
								({$l.quantity}) {$key}
							</div>
							<div>
								{foreach from=$l.corps key="key1" item="l1"}
									({$l1}) {$key1|truncate:35:"...":true} <br/>
								{/foreach}
							</div>
						{/foreach}
					</div>
				</td>
				<td class="invships">
					<div class="no_stretch">
					{foreach from=$invShips key="key2" item="l2"}
						({$l2}) {$key2} <br/>
					{/foreach}
					</div>
				</td>
			</tr>
		</table>
	{/if}
</div>
