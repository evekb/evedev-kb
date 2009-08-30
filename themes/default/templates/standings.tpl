<table class="kb-table">
{section name=opt loop=$standings}
  <tr class="kb-table-header"><td colspan="5">{$standings[opt].name}</td></tr>
  <tr class="kb-table-header"><td>&nbsp;</td><td>Name</td><td>Standing</td><td>Comment</td></tr>
{section name=idx loop=$standings[opt].list}
  <tr class="kb-table-row-even">
{if $standings[opt].name=='Alliances'}
    <td width="32" height="34" valign="top" align="right" style="background-image: url(?a=thumb&amp;type=alliance&amp;size=32&amp;id={$standings[opt].list[idx].pid});"><img src="img/sta_{$standings[opt].list[idx].icon}.png"/></td>
    <td><b><a href="?a=alliance_detail&amp;all_id={$standings[opt].list[idx].id}">{$standings[opt].list[idx].text}</a></b>{$standings[opt].list[idx].descr}</td>
{else}
    <td width="32" height="34" valign="top" align="right" style="background-image: url(?a=thumb&amp;type=corp&amp;size=32&amp;id={$standings[opt].list[idx].id});"><img src="img/sta_{$standings[opt].list[idx].icon}.png"/></td>
    <td><b><a href="?a=corp_detail&amp;crp_id={$standings[opt].list[idx].id}">{$standings[opt].list[idx].text}</a></b>{$standings[opt].list[idx].descr}</td>
{/if}
    <td align="right">{$standings[opt].list[idx].value}</td>
    <td align="center">{$standings[opt].list[idx].comment}</td>
{/section}
{sectionelse}
  <tr><td>None.</td></tr>
{/section}
</table>