<!-- violent_systems.tpl -->
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div class="violentsystems">
	<table class="kb-table">
		<tr class="kb-table-header"><td width="25">#</td><td width="215">System</td><td width="60" align="center">Kills</td></tr>
{section name=sys loop=$syslist}
        {assign var="s" value=$syslist[sys]}
		<tr class="{cycle name=ccl}">
			<td><b>{$s.counter}</b></td>
			<td class="kb-table-cell"><b><a href="{$s.url}">{$s.name}</a></b> ({$s.sec})</td>
			<td align="center">{$s.kills}</td>
		</tr>
{/section}
	</table>
</div>
<!-- /violent_systems.tpl -->