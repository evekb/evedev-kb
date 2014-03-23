<div id="post">
{if $error}
<div id="posterror">{$error}</div>
{elseif !$post_forbid && !$post_oog_forbid}
Paste the killmail from your EVEMail inbox into the box below. Make sure you post the <b>ENTIRE</b> mail.<br />
Posting fake or otherwise edited mails is not allowed. All posts are logged.<br />
<br />
Remember to post your losses as well.<br />
<br />
<b>Killmail:</b><br />
<form id="postform" name="postform" class="f_killmail" method="post" action="{$kb_host}/?a=post">
<textarea name="killmail" id="killmail" class="f_killmail" cols="70" rows="24">
</textarea>
{if !$isadmin}
<br /><br /><b>Password:</b><br /><input id="password" name="password" type="password" />
{/if}
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="submit" name="submit" type="submit" value="Process !" />
</form>
{elseif $post_oog_forbid}
Out of game posting is disabled, please use the ingame browser.<br />
{else}
Posting killmails is disabled<br />
{/if}
</div>
