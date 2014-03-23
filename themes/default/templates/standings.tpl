<table class="kb-table">
{section name=opt loop=$standings}
  <tr class="kb-table-header"><td colspan="5">{$standings[opt].name}</td></tr>
  <tr class="kb-table-header"><td>&nbsp;</td><td>Name</td><td>Standing</td><td>Comment</td></tr>
{section name=idx loop=$standings[opt].list}
  <tr class="kb-table-row-even">
{if $standings[opt].name=='Alliances'}
    <td style="width:32px; height:32px;">
		<div style="position: relative;">
			<img src="{$standings[opt].list[idx].all_img}" height="32px" width="32px" />
			<img style="position: absolute; top:0; right:0" src="{$img_url}/sta_{$standings[opt].list[idx].icon}.png" alt="alliance standings" />
		</div>
	</td>
    <td><b><a href="{$standings[opt].list[idx].all_url}">{$standings[opt].list[idx].text}</a></b>{$standings[opt].list[idx].descr}</td>
{else}
    <td style="width:32px; height:32px;">
		<div style="position: relative;">
			<img src="{$standings[opt].list[idx].crp_img}" height="32px" width="32px" />
			<img style="position: absolute; top:0; right:0" src="{$img_url}/sta_{$standings[opt].list[idx].icon}.png" alt="corp standings" /></td>
    <td><b><a href="{$standings[opt].list[idx].crp_url}">{$standings[opt].list[idx].text}</a></b>{$standings[opt].list[idx].descr}</td>
{/if}
    <td style="text-align:right">{$standings[opt].list[idx].value}</td>
    <td style="text-align:center">{$standings[opt].list[idx].comment}</td>
  </tr>
{/section}
{sectionelse}
  <tr><td>None.</td></tr>
{/section}
</table>