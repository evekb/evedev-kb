<!-- awards.tpl --><div class="block-header2">Awards for {$month} {$year}</div>
<div id="awards">
{assign var="count" value="1"} {section name=i loop=$awardboxes}
	<div style="vertical-align:top; text-align:center; float:left; width:150px; height:460px; margin-left:20px; margin-right:20px;">{$awardboxes[i]}</div>
{/section}
</div>
<!-- /awards.tpl -->