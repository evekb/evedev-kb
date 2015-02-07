{literal}<script language="JavaScript" type='text/javascript'>
function checkAll(checkname, exby)
{
	var elements = document.getElementsByName(checkname);
	for (i = 0; i < elements.length; i++) elements[i].checked = exby.checked? true:false
}
</script>{/literal}
{if $results}{$results}{/if}
<form id="options" name="options" method="post" action="{$kb_host}/?a=admin_idfeedsyndication">
	<div class='block-header2'>Feeds</div>
	<input type='submit' id='submitFetch' name='fetch' value="Fetch!" /> 
	<br />
	<br />
	<table>
		<tr style='text-align: left;'>
			<th>Feed URL</th>
			<th>Last Kill</th>
			<th>Fetch</th>
			<th>Delete</th>
		</tr>
{foreach from=$rows key=key item=i}
		<tr>
			<td>
				<input type='text' name='{$i.name}' size='50' class='password' value="{$i.uri}" />
			</td>
			<td>
				<input type='text' name='lastkill{$i.name}' class='lastkill' size='10' value='{$i.lastkill}' />
			</td>
			<td>
				<input type='checkbox' name='fetch_feed[]' class='fetch' value='{$i.name}' {if $i.fetch}checked="checked"{/if} />
			</td>
			<td>
				<input type='checkbox' name='delete[]' class='delete' value='{$i.name}' />
			</td>
		</tr>
{/foreach}
		<tr>
			<td colspan='2'>
				<i>Example: http://killboard.domain.com/?a=idfeed</i>
			</td>
			<td>
				<input type='checkbox' name='allf' onclick='checkAll("fetch_feed[]",this)' />
				<i>all/none</i>
			</td>
			<td>
			</td>
		</tr>
	</table>
	<br />
        <div class='block-header2'>Options</div><table>
        <table>
            <tr>
                <td style='height:30px; width:150px'>Ignore NPC only deaths?</td>
                <td><input type='checkbox' name='post_no_npc_only_feed' id='post_no_npc_only_feed' {if $post_no_npc_only_feed}checked="checked"{/if} /></td>
            </tr>
        </table>
	<br />
	<input type='submit' id='submitOptions' name='submit' value="Save" /></form>
