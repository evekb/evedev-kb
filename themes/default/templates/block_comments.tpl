<div class="kl-detail-comments">
	<div class="block-header">Comments</div>
	<table class="kb-table">
		<tr>
			<td class="kl-detail-comments-outer" >
				<table class="kl-detail-comments-inner">
					<tr>
						<td>
							<div id="kl-detail-comment-list">
								{section name=i loop=$comments}
								<div class="comment-posted"><a href="{$kb_host}/?a=search&amp;searchtype=pilot&amp;searchphrase={$comments[i].encoded_name}">{$comments[i].name}</a>:
						{if $comments[i].time}
									<span class="comment-time">{$comments[i].time}</span>
						{/if}
									<p>{$comments[i].comment}</p>
						{if $page->isAdmin()}
									<a href='{$kb_host}/?a=admin_comments_delete&amp;c_id={$comments[i].id}' onclick="openWindow('?a=admin_comments_delete&amp;c_id={$comments[i].id}', null, 480, 350, '' ); return false;">Delete Comment</a>
									<span class="comment-IP">Posters IP:{$comments[i].ip}</span>
						{/if}
								</div>
								{/section}
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<form id="postform" method="post" action="{$formURL}">
								<table>
									<tr>
										<td>
											<textarea class="comment" name="comment" cols="55" rows="5" style="width:97%" onkeyup="limitText(this.form.comment,document.getElementById('countdown'),500);" onkeypress="limitText(this.form.comment,document.getElementById('countdown'),500);"></textarea>
										</td>
									</tr>
									<tr>
										<td>
											<br/>
											<span title="countdown" id="countdown">500</span> Letters left<br/>
											<b>Name:</b>
											<input style="position:relative; right:-3px;" class="comment-button" name="name" type="text" size="24" maxlength="24" {if $username}value="{$username}" {/if}/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{if $config->get('comments_pw') and !$page->isAdmin()}
											<br/>
											<b>Password:</b>
											<input type="password" name="password" size="19" class="comment-button" />&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
											<input class="comment-button" name="submit" type="submit" value="Add Comment" />

										</td>
									</tr>
								</table>
							</form>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>