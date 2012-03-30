{if $results}{$results}{/if}
<form id="options" name="options" method="post" action="{$kb_host}/?a=admin_idfeedsyndication">
	<div class='block-header2'>Feeds</div>
	<table>
		<tr style='text-align: left;'>
			<th>Feed URL</th>
			<th>Last Kill</th>
			<th>Trusted</th>
			<th>Active</th>
			<th>Last Updated</th>
			<th>Delete</th>
		</tr>
{foreach from=$rows key=key item=i}
		<tr>
			<td>
				<input type='text' name='feed[{$i.id}][url]' size='50' class='password' value="{$i.uri}" />
			</td>
			<td>
				<input type='text' name='feed[{$i.id}][lastkill]' class='lastkill' size='10' value='{$i.lastkill}' />
			</td>
			<td>
				<input type='checkbox' name='feed[{$i.id}][trusted]' class='trusted' value='1' {if $i.trusted}checked="checked"{/if} />
			</td>
			<td>
				<input type='checkbox' name='feed[{$i.id}][active]' class='active' value='1' {if $i.active}checked="checked"{/if} />
			</td>
			<td>
				{$i.updated}
			</td>
			<td>
				<input type='checkbox' name='delete[]' class='delete' value='{$i.id}' />
			</td>
		</tr>
{/foreach}
		<tr>
			<td colspan='2'>
				<i>Example: http://killboard.domain.com/?a=idfeed</i>
			</td>
			<td>
			</td>
			<td>
			</td>
			<td>
			</td>
		</tr>
	</table>
	<br />
	<br />
	<input type='submit' id='submitOptions' name='submit' value="Save" /></form>
