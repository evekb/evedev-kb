<!-- menu.tpl -->
<table class="navigation">
<tr class="kb-table-row-odd">
{section name=item loop=$menu}
<td><a class="link" href="{$menu[item].link}">{$menu[item].text}</a></td>
{/section}
</tr>
</table>
