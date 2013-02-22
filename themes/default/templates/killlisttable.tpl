<!-- killlistable.tpl -->
<div class="kltable edk-section-main ui-widget ui-helper-reset">
	{section name=day loop=$killlist}
		<table class="kl-table">
			<thead>
				<tr class="kb-table-header ui-widget-header">
					<th style="display: none"></th>
					<th class="kl-timestamp">Timestamp</th>
					<th class="kl-shiptype">Ship type</th>
					<th class="kl-victim">Victim</th>
					<th class="kl-finalblow">Final blow</th>
					<th class="kl-location">Location</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{section name=kill loop=$killlist[day].kills}
					{assign var="k" value=$killlist[day].kills[kill]}
					{if $k.loss}
						<tr class="kb-table-row-loss ui-state-default" onclick="window.location.href='{$k.urldetail}';">
						{elseif $k.kill}
						<tr class="kb-table-row-kill ui-state-default" onclick="window.location.href='{$k.urldetail}';">
						{else}
						<tr onclick="window.location.href='{$k.urldetail}';">
						{/if}
						<td style="display: none">{$k.id}</td>
						<td>
                            <div class="kl-date">
                                {if $daybreak}
                                    {if $k.urlrelated}
                                        <a href="{$k.urlrelated}"><b>{$k.timestamp|date_format:"%H:%M"}</b></a>
                                        {else}
                                        <b>{$k.timestamp|date_format:"%H:%M"}</b>
                                    {/if}
                                    {else}
                                    {if $k.urlrelated}
                                        <a href="{$k.urlrelated}"><b>{$k.timestamp|date_format:"%y-%m-%d"} {$k.timestamp|date_format:"%H:%M"}</b></a>
                                        {else}
                                        <b>{$k.timestamp|date_format:"%y-%m-%d"} {$k.timestamp|date_format:"%H:%M"}</b>
                                    {/if}
                                {/if}
                            </div>
                        </td>
						<td>
							<div>
								<img src='{$k.victimshipimage}' class="kl-img" alt="" />
								<div class="no_stretch kl-shiptype-text">
									<b>{$k.victimshipname}</b>
									<br />
									{$k.victimshipclass}
								</div>
							</div>
						</td>
						<td>
							<div>
								{if !$k.allianceexists}
								&nbsp;
								{else}
								<img src="{$k.victimallianceicon}" class="kl-img" title="{$k.victimalliancename}" alt="{$k.victimalliancename}" />
								{/if}
								<div class="no_stretch kl-victim-text">
									{if $k.loss}
										<a href="{$k.urlvictim}"><b>{$k.victim}</b></a>
										<br />
										<a href="{$k.urlvictimcorp}">{$k.victimcorp}</a>
									{else}
										{if $k.victimalliancename != "None" && $k.victimalliancename != "NONE"}
											<a href="{$k.urlvictim}"><b>{$k.victim}</b></a><br /><a href="{$k.urlvictimall}">{$k.victimalliancename}</a>
										{else}
											<a href="{$k.urlvictim}"><b>{$k.victim}</b></a><br /><a href="{$k.urlvictimcorp}">{$k.victimcorp}</a>
										{/if}
									{/if}
								</div>
							</div>
						</td>
						<td>
							<div class="no_stretch kl-finalblow">
								<a href="{$k.urlfb}"><b>{$k.fb}</b></a>
								<br />
								<a href="{$k.urlfbcorp}">{$k.fbcorp}</a>
							</div>
						</td>
						<td>
							<div class="no_stretch kl-location">
								{if $config->get('killlist_regionnames')} {$k.region}<br/>{$k.system}{else}<b>{$k.system}</b>{/if} ({if $k.loss || $k.kill}{$k.systemsecurity|max:0|string_format:"%01.1f"}{else}<span style="color:{if $k.systemsecurity >= 0.5}green{elseif $k.systemsecurity < 0.05}red{else}orange{/if};">{$k.systemsecurity|max:0|string_format:"%01.1f"}</span>{/if})
							</div>
						</td>
						{if $k.inv || $comments_count}
							<td>
								<div class="kl-inv-comm">
									{if $k.inv}<img src="{$theme_url}/img/involved10_10.png" alt="I:" /> {$k.inv}{/if}
									{if $comments_count}<span {if !$k.commentcount}style="visibility: hidden"{/if}><img src="{$theme_url}/img/comment_white13_10.gif" alt="C:" class="kl-comm" /> {$k.commentcount}</span>{/if}
								</div>
							</td>
						{/if}
					</tr>
				{/section}
			</tbody>
		</table>
	{sectionelse}
		<p>No data.</p>
	{/section}
</div>
<!-- /killlistable.tpl -->