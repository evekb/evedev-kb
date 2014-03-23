<div class="alliance-detail-corps">
	<table class='kb-table kb-table-rows'>
		<thead>
			<tr class='kb-table-header'>
				<td class="alliance-detail-corpname">Corporation Name</td>
				<td class="alliance-detail-ticker">Ticker</td>
				<td class="alliance-detail-members">Members</td>
				<td class="alliance-detail-joined">Join Date</td>
				<td class="alliance-detail-tax">Tax Rate</td>
				<td class="alliance-detail-site">Website</td>
			</tr>
		</thead>
		<tbody>
			{section name=sys loop=$corps}
				{assign var="c" value=$corps[sys]}
				<tr>
					<td><a href="{$kb_host}/?a=corp_detail&amp;crp_ext_id={$c.corpExternalID}">{$c.corpName}</a></td>
					<td>{$c.ticker}</td>
					<td>{$c.members}</td>
					<td>{$c.joinDate}</td>
					<td>{$c.taxRate}</td>
					{if $c.url}
						<td>
							<div class="no_stretch alliance-detail-site" >
								<a href="{$c.url}">{$c.url}</a>
							</div>
						</td>
					{else}
						<td></td>
					{/if}
				</tr>
			{/section}
		</tbody>
	</table>
</div>