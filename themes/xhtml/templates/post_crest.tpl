<div id="post">
{if $error}
<div id="posterror">{$error}</div>
{elseif !$post_forbid && !$post_oog_forbid}
Paste the CREST link from your kill report (Copy external link) into the box below.<br />
<br />
Remember to post your losses as well.<br />
<br />
<b>CREST-Link:</b>
<form id="postform" name="postform" class="f_killmail" method="post" action="{$kb_host}/?a=post_crest">
<input type="text" name="crest_url" id="crest_url" class="f_killmail" size="100">
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
