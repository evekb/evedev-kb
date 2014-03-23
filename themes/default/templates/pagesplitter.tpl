<!-- /pagesplitter.tpl -->
<div class="klsplitter"><br /><b>[</b> Page: {assign var=dotted value=0}{assign var=ldotted value=0}{section name=i loop=$splitter_endpage}
{if $smarty.section.i.iteration == $splitter_page}<b>{$smarty.section.i.iteration}</b>&nbsp;
{else}{if $smarty.section.i.iteration == 1 ||
	$smarty.section.i.iteration == $splitter_endpage ||
	(($smarty.section.i.iteration >= $splitter_page - 1 && $smarty.section.i.iteration <= $splitter_page + 1))}
{if $smarty.section.i.iteration != 1}<a href="{$splitter_url}page={$smarty.section.i.iteration}">{$smarty.section.i.iteration}</a>&nbsp;
{else}<a href="{$splitter_url}">{$smarty.section.i.iteration}</a>&nbsp;
{/if}
{elseif $smarty.section.i.iteration < $splitter_page && $dotted == 0}{assign var=dotted value=1}
<b>..&nbsp;</b>
{elseif $smarty.section.i.iteration > $splitter_page && $ldotted == 0}{assign var=ldotted value=1}<b>..&nbsp;</b>
{/if}{/if}{/section}<b>]</b>
</div>
<!-- /pagesplitter.tpl -->