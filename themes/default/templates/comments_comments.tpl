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
