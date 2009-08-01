<div class="block-header">Comments</div>
<table class="kb-table" width="360" border="0" cellspacing="1" border="0">
  <tr>
    <td width="100%" align="left" valign="top">
      <table width="100%" border="0" cellspacing="0" border="0">
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}{section name=i loop=$comments}
        <tr class="{cycle name=ccl}">
          <td>
            <div style="position: relative;"><a href="?a=search&searchtype=pilot&searchphrase={$comments[i].name}">{$comments[i].name}</a>:
{if $comments[i].time}
            <span style="position:absolute; right: 0px;">{$comments[i].time}</span>
{/if}
            <p>{$comments[i].comment}</p>
{if $page->isAdmin()}
            <a href="javascript:openWindow('?a=admin_comments_delete&c_id={$comments[i].id}', null, 480, 350, '' );">Delete Comment</a>
{/if}
          </td>
        </tr>
{/section}
{if (!$allowedToPost)}
{if !(!$valUser and $apiuserEnable) }
        <tr>
          <td align="center"><form id="postform" name="postform" method="post" action="">
            <textarea class="comment" name="comment" cols="55" rows="5" wrap="PHYSICAL" onkeyup="limitText(this.form.comment,document.getElementById('countdown'),200);" onkeypress="limitText(this.form.comment,document.getElementById('countdown'),200);"></textarea>
          </td>
        </tr>
{/if}
        <tr>
          <td>
            <br/>
{if !$apiuserEnable}
            <span name="countdown" id="countdown">200</span> Letters left <br/>
            <b>Name: {$valUser}</b>
            <input style="position:relative; right:-3px;" class="comment-button" name="name" type="text" size="24" maxlength="24">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{if $config->get('comments_pw') and !$page->isAdmin()}
            <br/>
            <b>Password:</b>
            <input type="password" name="password" size="19" class="comment-button">&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{/if}

{if (!$valUser and $apiuserEnable==1) }
	<b>You Need to identify yourself to post comment</b>
{else }
            <input class="comment-button" name="submit" type="submit" value="Add Comment">
{/if}
{/if}

            </form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>