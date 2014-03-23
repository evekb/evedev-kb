<!-- corp_detail_stats -->
<div class="stats corp-detail">
	<table class="kb-table">
		<col class="logo" />
		<col class="attribute-name" />
		<col class="attribute-data-short" />
		<col class="attribute-name" />
		<col class="attribute-data-long" />
		<tr class="kb-table-row-even">
			<td class="logo" rowspan="6">
				<img src="{$portrait_url}" alt="" />
			</td>
			<td>Alliance: </td>
			<td>
{if $alliance_url}
				<a href="{$alliance_url}">{$alliance_name}</a>
{else}
						{$alliance_name}
{/if}
			</td>
			<td>CEO: </td>
			<td><a href="{$ceo_url}">{$ceo_name}</a> </td>
		</tr>
		<tr class="kb-table-row-even">
			<td>Kills: </td>
			<td class="kl-kill">{$kill_count}</td>
			<td>HQ: </td>
			<td>{$HQ_location}</td>
		</tr>
		<tr class="kb-table-row-even">
			<td>Losses: </td>
			<td class="kl-loss">{$loss_count}</td>
			<td>Members: </td>
			<td>{$member_count}</td>
		</tr>
		<tr class="kb-table-row-even">
			<td>ISK destroyed:</td>
			<td class="kl-kill">{$damage_done}B</td>
			<td>Shares: </td>
			<td>{$share_count}</td>
		</tr>
		<tr class="kb-table-row-even">
			<td>ISK lost:</td>
			<td class="kl-loss">{$damage_received}B</td>
			<td>Tax Rate: </td>
			<td>{$tax_rate}%</td>
		</tr>
		<tr class="kb-table-row-even">
			<td>Efficiency:</td>
			<td>{$efficiency}%</td>
			<td>Website:</td>
			<td>
				{if {$external_url}}<a href="{$external_url}">{$external_url}</a>{/if}
			</td>
		</tr>
	</table>
	<div class="kb-table-row-even description">{$corp_description}</div>
</div>
<!-- /corp_detail_stats -->
