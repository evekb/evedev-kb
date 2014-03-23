<form action="{$kb_host}/?a=admin_mods" method="post">
	<input type="hidden" name="set_mods" value="1" />
	<table class="kb-table" width="99%" align="center" cellspacing="1">
		<tr class="kb-table-header">
			<td class="kb-table-header">Name</td>
			<td class="kb-table-header" align="center">Active</td>
		</tr>
{foreach from=$rows key=key item=i}
		<tr class='kb-table-row-odd' style="height: 34px;">
			<td>{$i.name}{if $i.settings} [<a href="{$i.url}">settings</a>]{/if}</td>
			<td align='center'>
				<input name="mod_{$i.name}" type="checkbox" {if $i.checked}checked="checked" {/if}/>
			</td>
		</tr>
{/foreach}
		<tr>
			<td colspan='2' align='center'>
				<input type='submit' name='submit' value="Save" />
			</td>
		</tr>
	</table>
</form>

