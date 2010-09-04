<!-- self.tpl -->
<div id="owners">
{section name=i loop=$pilots}
<div style="float:left; width:356px; height:228px; text-align:center; border:2px red solid; vertical-align:top; margin: 10px;"
	 onclick="window.location.href='{if $pilots[i].extid}?a=pilot_detail&amp;plt_ext_id={$pilots[i].extid}{else}?a=pilot_detail&amp;plt_id={$pilots[i].id}{/if}';">
	<div class="owner-title" style="height:70px;"><h1>{$pilots[i].name}</h1></div>
	<div><img src="{$pilots[i].portrait}" style="border:0px" width="128" height="128" alt="{$pilots[i].name}" /></div>
</div>
{/section}
{section name=i loop=$corps}
<div style="float:left; width:356px; height:228px; text-align:center; border:2px red solid; vertical-align:top; margin: 10px;"
	 onclick="window.location.href='{if $corps[i].extid}?a=corp_detail&amp;crp_ext_id={$corps[i].extid}{else}?a=corp_detail&amp;crp_id={$corps[i].id}{/if}';">
	<div class="owner-title" style="height:70px"><h1>{$corps[i].name}</h1></div>
	<div><img src="{$corps[i].portrait}" style="border:0px" width="128" height="128" alt="{$corps[i].name}" /></div>
</div>
{/section}
{section name=i loop=$alliances}
<div style="float:left; width:356px; height:228px; text-align:center; border:2px red solid; vertical-align:top; margin: 10px;"
	 onclick="window.location.href='{if $alliances[i].extid}?a=alliance_detail&amp;all_ext_id={$alliances[i].extid}{else}?a=alliance_detail&amp;all_id={$alliances[i].id}{/if}';">
	<div class="owner-title" style="height:70px"><h1>{$alliances[i].name}</h1></div>
	<div><img src="{$alliances[i].portrait}" style="border:0px" width="128" height="128" alt="{$alliances[i].name}" /></div>
</div>
{/section}
</div>
<!-- /self.tpl -->