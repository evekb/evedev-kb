<div id="source">
	{if $type == "URL"}<a href="{$source}">Fetched on {$postedDate}</a>
	{else if $type == "IP"}Manually posted on {$postedDate}{if $source}<br />from {$source}{/if}
	{else if $type == "API"}Sourced from API with CCP ID: {$source} on {$postedDate}{/if}
</div>