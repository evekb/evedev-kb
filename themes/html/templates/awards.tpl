<!-- awards.tpl --><div class=block-header2>Awards for {$month} {$year}</div>
	<table height=600 width="100%">
		<tr>
{assign var="count" value="1"} {section name=i loop=$awardboxes}
			<td valign=top align=center>{$awardboxes[i]}</td>
{if $count++ >= ($boxcount/2)}{assign var="count" value="0"}
		</tr>
		<tr>{/if}
{/section}
	</tr>
</table>
<!-- /awards.tpl -->