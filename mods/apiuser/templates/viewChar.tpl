	<style type="text/css" media="all">
  		      @import url("/kb/mods/apiuser/style.css");
	</style>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr><td colspan="4"><br><br><br></td></tr>
	<tr>
	<td align="center" valign="top" width="45%">
<img src="http://img.eve.is/serv.asp?s=256&c={$basexml->result->characterID}" style="border: 1px solid gray;" border="0">

</td>
	<td width="45%" valign=top>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr>
	<td  valign="top">

	<table border="0" cellpadding="4" cellspacing="1" width="100%">
	<tbody><tr><td class="mbHead" colspan="2" align="center" valign="middle"><b>Character Profile</b></td></tr>
	<tr>
	    <td class="mbForum" align="right"  width="30" valign=top>Character:</td>
	    <td class="mbForum" align="left" valign="center">{$basexml->result->name}
		{foreach from=$listalt item=unalt}
		 <br /> <a href="index.php?a=viewChar&charID={$unalt.charID}">{$unalt.charName}</a>
		{/foreach}
		</td>
	</tr>
	<tr>
	    <td class="mbForum" align="right" valign="center" width="30">Race:</td>
	    <td class="mbForum" align="left" valign="center">{$basexml->result->race}</td>
	</tr>
	<tr>
	    <td class="mbForum" align="right" valign="center" width="30">Bloodline:</td>
	    <td class="mbForum" align="left" valign="center">{$basexml->result->bloodLine}</td>
	</tr>
	<tr>
	    <td class="mbForum" align="right" valign="center" width="30">Gender:</td>
	    <td class="mbForum" align="left" valign="center">{$basexml->result->gender}</td>
	</tr>
	<tr>
	    <td class="mbForum" align="right" valign="center" width="30">Corporation:</td>
	    <td class="mbForum" align="left" valign="center">{$basexml->result->corporationName}</td>
	</tr>
	<tr>
	    <td class="mbForum" align="right" valign="center" width="30">Isk:</td>
	    <td class="mbForum" align="left" valign="center">{$basexml->result->balance|number_format} isk</td>
	</tr>
	<tr>
	    <td class="mbForum" align="right" valign="center" width="30">Skills:</td>
	    <td class="mbForum" align="left" valign="center">{$total_skill|number_format} with {$total_sp|number_format} points</td>
	</tr>
		</tbody></table>
<center><a href="index.php?a=user_management">return to the user management</a></center>
	</td>
	</tr>
        			</tbody></table>
	</td>
        <td width="5%">&nbsp;</td>
	</tr>
	</tbody></table>
	<br>
	<br>
</div><br><br>
<table border="0" width="100%">
<tr>
	<td colspan="4">
{assign var='oldCat' value=''}

{foreach from=$xml item=ligne}


{if $oldCat!=$ligne.groupID}
	{if $oldCat!=''}
		<tr>
			<td width="5%">&nbsp;</td>
			<td colspan="2" align="left" width="90%"<font color="#ffff00"><b>{$nb_skill}</b> skills trained for a total of <b>{$cat_sp|number_format}</b> skillpoints</font></td>
			<td width="5%">&nbsp;</td>
		</tr>
	</table>
	{/if}
	{assign var='cat_sp' value=0}
	{assign var='nb_skill' value=0}
	<div style="border-top: 1px solid rgb(67, 67, 67); border-bottom: 1px solid rgb(67, 67, 67); background: rgb(44, 44, 56) url(mods/apiuser/images/{$ligne.groupID}.jpg) no-repeat scroll 74px 5px; margin-bottom: 10px; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; height: 21px;"></div>
	<img class="newsTitleImage" style="width: 64px; height: 64px; top: -52px;" src="mods/apiuser/images/{$ligne.groupID}.gif" alt="">
<table border="0" cellpadding="1" cellspacing="1" width="100%">
 {assign var='oldCat' value=$ligne.groupID}
{/if}
{assign var='cat_sp' value=$ligne.skillpoints+$cat_sp}
{assign var='nb_skill' value=$nb_skill+1}

<tr bgcolor="">
	<td width="5%">&nbsp;</td>
	<td align="left" nowrap="nowrap" width="65%">&nbsp;	{$ligne.typeName} </td>
	<td align="right" width="25%"><img src="mods/apiuser/images/level{$ligne.level}.gif" alt="{$ligne.typeName} {$ligne.level}" border="0">&nbsp;</td>
	<td  width="5%">&nbsp;</td>
</tr>
{/foreach}
{assign var='total_sp' value=$ligne.skillpoints+$total_sp}
{assign var='cat_sp' value=$ligne.skillpoints+$cat_sp}
{assign var='nb_skill' value=$nb_skill+1}
{assign var='total_skill' value=$total_skill+1}
<tr>
	<td width="5%">&nbsp;</td>
	<td colspan="2" align="left" width="90%"><font color="#ffff00">{$nb_skill} skills trained for a total of {$cat_sp|number_format} skillpoints</font></td>
	<td width="5%">&nbsp;</td>
</tr>

</table>
</td>
</tr>
</table>
