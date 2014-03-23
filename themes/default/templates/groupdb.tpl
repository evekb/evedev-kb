<!-- groupdb.tpl -->
<table class='kb-table' cellspacing='1'>
	<tr class='kb-table-header'>
		<td style="width: 400px">Item Name</td>
	</tr>
    {foreach from=$rows item='row'}
	<tr class='kb-table-row-odd'>
		<td><a href="{$kb_host}/?a=invtype&amp;id={$row.typeID}">{$row.typeName}</a></td>
	</tr>
	{/foreach}
</table>
<!-- /groupdb.tpl -->