<form id="search" action="{$kb_host}/?a=admin_value_editor" method="get">
<table class="kb-subtable">
	<tr>
    <td><input id="searchphrase" name="searchphrase" type="text" size="30"/></td>
    <td><select name='item_type'> {html_options options=$item_types selected=$type} </select></td>
    <td><input type="submit" name="submit" value="Search"/><input type='hidden' name='a' value='admin_value_editor'></td>
  </tr>
</table>
</form>
<div class="block-header2">Values</div>
{if $success}
<div style='background: #944; border: 2px solid red'>{$success}</div>
{/if}
<table class="kb-table">
  <tr class="kb-table-header"><td>Item</td><td colspan='2'>Current Value</td></tr>
{section name=opt loop=$results}
  <tr class="kb-table-row-{cycle values='odd,even'}">
	<td>{$results[opt].name}</td>
	<td><form method='post' action='{$kb_host}/?a=admin_value_editor'>
		<input type='text' value='{$results[opt].value}' name='value'>
		<input type='hidden' value='{$results[opt].id}' name='itm_id'>
		<input type='hidden' value='1' name='update_value'>
		<input type='hidden' value='{$type}' name='item_type'>
		&nbsp;&nbsp;<input type='submit' value='Save'>
		</form>
	</td>
	{if $eve_central_exists eq "1"}<td><a href='{$kb_host}/?a=admin_value_editor&amp;itm_id={$results[opt].id}&amp;d=eve_central&amp;item_type={$type}'>Sync to EVE Central</a></td>{/if}
	</td>
</tr>
{sectionelse}
  <tr><td colspan='3'>None.</td></tr>
{/section}
</table>
