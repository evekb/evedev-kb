<div id="kl-detail-invsum">
	<div class="block-header">Involved parties: {$involvedPartyCount}</div>

            {if $showext && $involvedPartyCount > 4}
	<table class="kb-table" width="100%" border="0" cellspacing="1">
                {assign var="first" value="true"}

                {foreach from=$invAllies key="key" item="l"}
		<tr class="kb-table-row-even" >
                        {if 1 || $alliesCount > 0}
			<th class="kb-table-header">
				({$l.quantity}) {$key|truncate:30:"...":true}
			</th>
                        {/if}
                        {if $first == "true"}
			<td rowspan="{$alliesCount * 2}" class="kb-table-cell" style="white-space: nowrap">
                                {foreach from=$invShips key="key2" item="l2"}
				({$l2}) {$key2|truncate:22:"...":true} <br/>
                                {/foreach}
			</td>
                            {assign var="first" value="false"}
                        {/if}
		</tr>
		<tr class="kb-table-row-even">
			<td class="kb-table-cell">
			{foreach from=$l.corps key="key1" item="l1"}
				({$l1}) {$key1|truncate:35:"...":true} <br/>
			{/foreach}
			</td>
		</tr>
                {/foreach}

	</table>
	<br/>
            {/if}
</div>
