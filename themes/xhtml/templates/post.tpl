<div id="post">
{if $error}
<div id="posterror">{$error}</div>
{else}
  {if $post_forbid && $post_crest_forbid}
    Posting kills is currently disabled!
  {else}
    {if !$post_crest_forbid}
      <b><u>Post ESI Link:</u></b>
      <p>Paste the ESI link from your kill report (Copy External Kill Link) into the box below.<br />
      Remember to post your losses as well.<br /></p>
      <br />
      <b>ESI-Link:</b>
      <form id="postform" name="postform" class="f_killmail" method="post" action="{$kb_host}/?a=post">
      <input type="text" name="crest_url" id="crest_url" class="f_killmail" size="100">
      {if !$isadmin && $crest_pw_needed}
        <br /><br /><b>Password:</b><br /><input id="password_crest" name="password_crest" type="password" />
      {/if}
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="submit_crest" name="submit_crest" type="submit" value="Process !" />
      </form>
      <br/><hr><br/>
    {/if}

    {if !$post_forbid}
      <b><u>Post Kill Mail:</u></b>
      <p>Paste the killmail from your EVEMail inbox into the box below. Make sure you post the <b>ENTIRE</b> mail.<br />
      Posting fake or otherwise edited mails is not allowed. All posts are logged.<br />
      Remember to post your losses as well.<br /></p>
      <br />
      <b>Killmail:</b><br />
      <form id="postform" name="postform" class="f_killmail" method="post" action="{$kb_host}/?a=post">
      <textarea name="killmail" id="killmail" class="f_killmail" cols="70" rows="24"></textarea>
      {if !$isadmin}
        <br /><br /><b>Password:</b><br /><input id="password" name="password" type="password" />
      {/if}
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="submit" name="submit" type="submit" value="Process !" />
      </form>
    {/if}
  {/if}
{/if}
</div>

