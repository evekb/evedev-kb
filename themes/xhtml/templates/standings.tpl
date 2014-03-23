<table class="kb-table">
{section name=opt loop=$standings}
  <tr class="kb-table-header"><td colspan="5">{$standings[opt].name}</td></tr>
  <tr class="kb-table-header"><td>&nbsp;</td><td>Name</td><td>Standing</td><td>Comment</td></tr>
{section name=idx loop=$standings[opt].list}
  <tr class="kb-table-row-even">
{if $standings[opt].name=='Alliances'}
    <td style="width:32px; height:34px; vertical-align:top; text-align:right; background-image: url(?a=thumb&amp;type=alliance&amp;size=32&amp;id={$standings[opt].list[idx].pid});"><img src="img/sta_{$standings[opt].list[idx].icon}.png" alt="alliance standings" /></td>
    <td><b><a href="{$kb_host}/?a=alliance_detail&amp;all_id={$standings[opt].list[idx].id}">{$standings[opt].list[idx].text}</a></b>{$standings[opt].list[idx].descr}</td>
{else}
    <td style="width:32px; height:34px; vertical-align:top; text-align:right; background-image: url(?a=thumb&amp;type=corp&amp;size=32&amp;id={$standings[opt].list[idx].id});"><img src="img/sta_{$standings[opt].list[idx].icon}.png" alt="corp standings" /></td>
    <td><b><a href="{$kb_host}/?a=corp_detail&amp;crp_id={$standings[opt].list[idx].id}">{$standings[opt].list[idx].text}</a></b>{$standings[opt].list[idx].descr}</td>
{/if}
    <td style="text-align:right">{$standings[opt].list[idx].value}</td>
    <td style="text-align:center">{$standings[opt].list[idx].comment}</td>
{/section}
{sectionelse}
  <tr><td>None.</td></tr>
{/section}
</table>