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