<form id='search' action="?a=search" method='post'>
<table class='kb-subtable'>
	<tr>
		<td>Type:</td>
		<td>Text: (3 letters minimum)</td>
	</tr>
	<tr>
		<td>
			<select id='searchtype' name='searchtype'>
			<option value='pilot'>Pilot</option>
			<option value='corp'>Corporation</option>
			<option value='alliance'>Alliance</option>
			<option value='system'>System</option>
			<option value='item'>Items</option>
			</select>
		</td>
		<td>
			<input id='searchphrase' name='searchphrase' type='text' size='30' onkeyup="if(this.form.searchphrase.value.length > 3) xajax_doAjaxSearch(this.form.searchphrase.value, this.form.searchtype.value);"/>
		</td>
		<td>
			<input type='submit' name='submit' value='Search' />
		</td>
	</tr>
</table>
</form>
<div class='block-header'>Search results</div><div id='searchresults'>
{if !$results}No results.
{else}<table class='kb-table' width='450' cellspacing='1'>
	<tr class='kb-table-header'>
		<td>{$result_header}</td>
		<td>{$result_header_group}</td>
	</tr>
{section name=i loop=$results}
	<tr class='kb-table-row-even'>
		<td><a href="{$results[i].link}">{$results[i].name}</a></td>
		<td>{$results[i].type}</td>
	</tr>
{/section}
</table>
{/if}</div>