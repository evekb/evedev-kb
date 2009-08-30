<!-- menu.tpl -->
<table class="navigation" width="100%" style="height:25px;" border="0" cellspacing="1">
<tr class="kb-table-row-odd">
{section name=item loop=$menu}
<td width="{$menu_w}" align="center"><a class="link" style="display: block;" href="{$menu[item].link}">{$menu[item].text}</a></td>
{/section}
</tr>
</table>
