<!-- awards.tpl -->
<div class="block-header2">{if $page_title}{$page_title}{else}Awards for {$month} {$year}{/if}</div>
<div id="awards">
{section name=i loop=$awardboxes}
	<div class="award_box">{$awardboxes[i]}</div>
{/section}
</div>
<!-- /awards.tpl -->