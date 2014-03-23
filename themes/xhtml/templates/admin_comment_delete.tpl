{if $deleted}<p>Comment ID "{$id}" deleted!</p>
<p><a href="javascript:window.close();">[close]</a></p>
{elseif $id}Confirm deletion of Comment ID "{$id}":
<div class="comment-text">
	<a href="{$kb_host}/?a=search&amp;searchtype=pilot&amp;searchphrase={$name}">{$name}</a>:
	<p>{$comment}</p>
</div>
<br />
<form action='' method='post'>
	<input type='submit' name='confirm' value='Yes' />
	<button onclick="window.close();">No</button>
</form>
{else}
<p>Error: comment does not exist</p>
<form action='' method='post'>
	<button onclick="window.close();">Return</button>
</form>
{/if}
