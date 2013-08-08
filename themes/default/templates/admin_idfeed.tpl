{if $results}{$results}{/if}
<form id="options" name="options" method="post" action="{$url}">
	<div class='block-header2'>Feeds</div>
	<table>
		<tr style='text-align: left;'>
			<th>Feed URL</th>
			<th>Last Kill</th>
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
				<i>Examples: http://killboard.domain.com/?a=idfeed<br /><br />
				http://zkillboard.com<br />
				Other forms of interaction with zKill are possible. This parser does not honor zKill's advertised expires header or cachedUntil value, so do not set an unreasonable polling frequency in your cron.<br /><br />
				Default parameters are automatically added to feed URLs if missing.<br /></i>
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
