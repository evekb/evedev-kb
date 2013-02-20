<h2>Modifications Information</h2>

Additional Modifications can contain hooks that could cause problems. If you are diagnosing a problem with your board, consider turning off the following modifications:<br /><br />

{if $modifications}
<ul>
	{foreach from=$modifications item=i}
	<li>{$i}</li>
	{/foreach}
</ul>
{else}
No modifications Found
{/if}

<h2>Killboard Files Information</h2>
Checked {$count} files.<br /><br />
Missing: {$missing|@count} file(s)
{if $missing}
<ul>
	{foreach from=$missing item=i}
	<li>{$i}</li>
	{/foreach}
</ul>
{/if}
<br />
Invalid: {$invalid|@count} file(s)
{if $invalid}
<ul>
	{foreach from=$invalid key=k item=v}
	<li>{$k} (Expected {$v.0}, got {$v.1})</li>
	{/foreach}
</ul>
{/if}