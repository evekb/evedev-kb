<!-- corp_detail_stats -->
			<div class="stats">
			<table class="kb-table" width="100%" border="0" cellspacing="1">
				<tr class="kb-table-row-even">
					<td rowspan="8" style="width:128px; height:128px; text-align:center; background-color:black">
						<img src="{$portrait_url}" style="border:0px" alt="" />
					</td>
					<td class="kb-table-cell" style="width:150px">
						<b>Alliance:</b>
					</td>
					<td class="kb-table-cell">
{if $alliance_url}
						<a href="{$alliance_url}">{$alliance_name}</a>
{else}
						{$alliance_name}
{/if}
					</td>
					<td class="kb-table-cell" style="width:65px">
						<b>CEO:</b>
					</td>
					<td class="kb-table-cell">
						<a href="{$ceo_url}">{$ceo_name}</a>
					</td>
				</tr>
				<tr class="kb-table-row-even">
					<td class="kb-table-cell">
						<b>Kills:</b>
					</td>
					<td class="kl-kill">{$kill_count}</td>
					<td class="kb-table-cell">
						<b>HQ:</b>
					</td>
					<td class="kb-table-cell">{Corp->getStationID}</td>
				</tr>
				<tr class="kb-table-row-even">
					<td class="kb-table-cell">
						<b>Losses:</b>
					</td>
					<td class="kl-loss">{$loss_count}</td>
					<td class="kb-table-cell">
						<b>Members:</b>
					</td>
					<td class="kb-table-cell">{Corp->getMemberCount}</td>
				</tr>
				<tr class="kb-table-row-even">
					<td class="kb-table-cell">
						<b>Damage done (ISK):</b>
					</td>
					<td class="kl-kill">{$damage_done}B</td>
					<td class="kb-table-cell">
						<b>Shares:</b>
					</td>
					<td class="kb-table-cell">{Corp->getShares}</td>
				</tr>
				<tr class="kb-table-row-even">
					<td class="kb-table-cell">
						<b>Damage received (ISK):</b>
					</td>
					<td class="kl-loss">{$damage_received}B</td>
					<td class="kb-table-cell">
						<b>Tax Rate:</b>
					</td>
					<td class="kb-table-cell">{Corp->getTaxRate}%</td>
				</tr>
				<tr class="kb-table-row-even">
					<td class="kb-table-cell">
						<b>Efficiency:</b>
					</td>
					<td class="kb-table-cell">
						<b>{$efficiency}%</b>
					</td>
					<td class="kb-table-cell">
						<b>Website:</b>
					</td>
					<td class="kb-table-cell">
						{if {Corp->getURL}}<a href="{Corp->getURL}">{Corp->getURL}</a>{/if}
					</td>
				</tr>
			</table>
			<div class="kb-table-row-even" style='width:100%;height:100px;overflow:auto'>{Corp->getDescription}</div>
			</div>
<!-- /corp_detail_stats -->
