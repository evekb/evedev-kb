<div class="kill-related">
	<div class="kb-kills-header">Battle Summary for {$system}, {$firstts|date_format:"%Y-%m-%d %H:%M"} - {$lastts|date_format:"%H:%M"}</div>
	<div class="kill-related-friendly">
		<div class="kb-date-header">Friendly ({$friendlycnt})</div>
		{assign var='loop' value=$pilots_a}
		<table class="kb-table kb-table-rows">
			<thead>
				<tr class="kb-table-header">
					<td class="kb-table-header" colspan="2">Pilot/Ship</td>
					<td class="kb-table-header">Corp/Alliance</td>
				</tr>
			</thead>
			<tbody>
				{foreach from=$loop item=a key=pilot}
					{foreach from=$a item=i key=b}
						<tr{if $i.destroyed} class="destroyed"{/if}>
							<td class="kb-table-imgcell">
								{if $i.destroyed}
									<a href="{$i.kll_url}"><img src="{$i.spic}" alt="" /></a>
								{else}
									<img src="{$i.spic}" alt="" />
								{/if}
							</td>
							{if $i.podded}
								{if $config->get('bs_podlink')}
									<td>
										<b><a href="{$i.plt_url}">{$i.name}</a>&nbsp;<a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.podid}">[Pod]</a></b><br/>{$i.ship}
									</td>
								{else}
									<td>
										<div style="position: relative;"><a href="{$i.plt_url}">{$i.name}</a><br/>
											<span class="kill-related-subsection">{$i.ship}</span>
											<div style="position: absolute; right:0px; top:-6px; width:32px; height:32px; z-index:1;">
												<a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.podid}"><img src="{$podpic}" alt="" /></a>
											</div>
										</div>
									</td>
								{/if}
							{else}
								<td>
									<a href="{$i.plt_url}">{$i.name}</a><br/>
									<span class="kill-related-subsection">{$i.ship}</span>
								</td>
								{/if}
							<td>
								<a href="{$i.crp_url}">{$i.corp}</a><br/>
								<a href="{$i.all_url}" class="kill-related-subsection">{$i.alliance}</a>
							</td>
						</tr>
					{/foreach}
				{/foreach}
		</table>
	</div>
	<div class="kill-related-hostile">
		<div class="kb-date-header">Hostile ({$hostilecnt})</div>
		{assign var='loop' value=$pilots_e}
		<table class="kb-table kb-table-rows">
			<thead>
				<tr class="kb-table-header">
					<td class="kb-table-header" colspan="2">Pilot/Ship</td>
					<td class="kb-table-header">Corp/Alliance</td>
				</tr>
			</thead>
			<tbody>
				{foreach from=$loop item=a key=pilot}
					{foreach from=$a item=i key=b}
						<tr {if $i.destroyed} class="destroyed"{/if}>
							<td class="kb-table-imgcell">
								{if $i.destroyed}
									<a href="{$i.kll_url}"><img src="{$i.spic}" alt="" /></a>
									{else}
									<img src="{$i.spic}" alt="" />
								{/if}
							</td>
							{if $i.podded}
								{if $config->get('bs_podlink')}
									<td>
										<a href="{$i.plt_url}">{$i.name}</a>&nbsp;<a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.podid}">[Pod]</a><br/>{$i.ship}
									</td>
								{else}
									<td>
										<div style="position: relative;"><a href="{$i.plt_url}">{$i.name}</a><br/>
											<span class="kill-related-subsection">{$i.ship}</span>
											<div style="position: absolute; right:0px; top:-6px; width:32px; height:32px; z-index:1;"><a href="{$kb_host}/?a=kill_detail&amp;kll_id={$i.podid}"><img src="{$podpic}" alt="" /></a></div>
										</div>
									</td>
								{/if}
							{else}
								<td>
									<a href="{$i.plt_url}">{$i.name}</a><br/>
									<span class="kill-related-subsection">{$i.ship}</span>
								</td>
								{/if}
							<td>
								<a href="{$i.crp_url}">{$i.corp}</a><br/>
								<a href="{$i.all_url}" class="kill-related-subsection">{$i.alliance}</a>
							</td>
						</tr>
					{/foreach}
				{/foreach}
		</table>
	</div>
</div>