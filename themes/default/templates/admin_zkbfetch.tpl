{literal}<script language="JavaScript" type='text/javascript'>
function checkAll(checkname, exby)
{
	var elements = document.getElementsByName(checkname);
	for (i = 0; i < elements.length; i++) elements[i].checked = exby.checked? true:false
}
</script>{/literal}
{if $results}{$results}{/if}
<form id="options" name="options" method="post" action="{$kb_host}/?a=admin_zkbfetch">
	<div class='block-header2'>Feeds</div>
	<input type='submit' id='submitFetch' name='fetch' value="Fetch!" /> 
	<br />
	<br />
	<table>
		<tr style='text-align: left;'>
			<th>zKB API URL</th>
			<th>Last Kill Timestamp</th>
			<th>Fetch</th>
			<th>Delete</th>
		</tr>
{foreach from=$rows item=i}
		<tr>
			<td>
				<input type='text' name='{$i.id}' size='50' class='password' value="{$i.uri}" />
			</td>
			<td>
				<input type='text' name='lastKillTimestamp{$i.id}' class='lastkill' size='20' value='{$i.lastKillTimestmap}' />
			</td>
			<td>
				<input type='checkbox' name='fetchApi[]' class='fetch' value='{$i.id}' {if $i.fetch}checked="checked"{/if} />
			</td>
			<td>
				<input type='checkbox' name='delete[]' class='delete' value='{$i.id}' />
			</td>
		</tr>
{/foreach}
		<tr>
			<td colspan='2'></td>
			<td>
				<input type='checkbox' name='allt' onclick='checkAll("fetchApi[]",this)' />
				<i>all/none</i>
			</td>
			<td>
				<input type='checkbox' name='allf' onclick='checkAll("delete[]",this)' />
				<i>all/none</i>
			</td>
			<td>
			</td>
		</tr>
	</table>
        <br />
        <div class='block-header2'>Add</div><table>
        <table>
		<tr style='text-align: left;'>
			<th>Feed URL</th>
			<th>Fetch Begin Timestamp</th>
                        <th>&nbsp;</th>
		</tr>
                <tr>
			<td>
				<input type='text' name='newFetchUrl' size='50' class='password' value="" />
			</td>
			<td>
				<input type='text' name='newFetchTimestamp' class='lastkill' size='20' value='{$currentTimeUtc}' />
			</td>
                        <td>
                                <input type='submit' id='submitOptions' name='add' value="Add" />             
                        </td>
		</tr>
                <tr>
			<td colspan='3'>
				<i>Example: https://zkillboard.com/api/combined/corporationID/12345567890/</i>
			</td>
                </tr>
        </table>
        
	<br />
        <div class='block-header2'>Options</div><table>
        <table>
            <tr>
                <td style='height:30px; width:250px'>Ignore NPC only deaths?</td>
                <td><input type='checkbox' name='post_no_npc_only_zkb' id='post_no_npc_only_feed' {if $post_no_npc_only_zkb}checked="checked"{/if} /></td>
            </tr>
            <tr>
                <td style='height:30px; width:250px'>Negative last kill timestamp offset [h]:</td>
                <td><input type='text' size="5" name='killTimestampOffset' id='killTimestampOffset' value="{if isset($killTimestampOffset)}{$killTimestampOffset}{/if}" /></td>
            </tr>
        </table>
	<br />
	<input type='submit' id='submitOptions' name='submit' value="Save" /></form>
