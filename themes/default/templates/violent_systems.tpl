<!-- violent_systems.tpl -->
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div class="violentsystems">
	<table class="kb-table">
		<tr class="kb-table-header"><td>#</td><td>System</td><td>Kills</td></tr>
			{section name=sys loop=$syslist}
				{assign var="s" value=$syslist[sys]}
				<tr class="{cycle name=ccl}">
					<td>{$s.counter}</td>
					<td><a href="{$s.url}">{$s.name}</a> ({$s.sec})</td>
					<td>{$s.kills}</td>
				</tr>
			{/section}
	</table>
</div>
<!-- /violent_systems.tpl -->