<div class="block-header">Comments</div>
<table class="kb-table" width="360" border="0" cellspacing="1">
  <tr>
    <td width="100%" align="left" valign="top">
      <table width="100%" border="0" cellspacing="0">
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}{section name=i loop=$comments}
        <tr class="{cycle name=ccl}">
          <td>
            <div style="position: relative;"><a href="?a=search&amp;searchtype=pilot&amp;searchphrase={$comments[i].name}">{$comments[i].name}</a>:
{if $comments[i].time}
            <span style="position:absolute; right: 0px;">{$comments[i].time}</span>
{/if}
            <p>{$comments[i].comment}</p>
{if $page->isAdmin()}
<a href="javascript:openWindow('?a=admin_comments_delete&amp;c_id={$comments[i].id}', null, 480, 350, '' );">Delete Comment</a>
<span style="position:absolute; right: 0px;"><u>Posters IP:{$comments[i].ip}</u></span>
{/if} 
          </div></td>
        </tr>
{/section}
        <tr><td><form id="postform" name="postform" method="post" action=""><table><tr>
          <td align="center">
            <textarea class="comment" name="comment" cols="55" rows="5" wrap="virtual" style="width: 340px;" onkeyup="limitText(this.form.comment,document.getElementById('countdown'),200);" onkeypress="limitText(this.form.comment,document.getElementById('countdown'),200);"></textarea>
          </td>
        </tr>
        <tr>
          <td>
            <br/>
            <span title="countdown" id="countdown">200</span> Letters left<br/>
            <b>Name:</b>
            <input style="position:relative; right:-3px;" class="comment-button" name="name" type="text" size="24" maxlength="24">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{if $config->get('comments_pw') and !$page->isAdmin()}
            <br/>
            <b>Password:</b>
            <input type="password" name="password" size="19" class="comment-button">&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
            <input class="comment-button" name="submit" type="submit" value="Add Comment">
            
          </td></tr></table></form></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
