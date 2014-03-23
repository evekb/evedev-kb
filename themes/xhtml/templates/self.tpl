<!-- self.tpl -->
<div id="owners">
{section name=i loop=$pilots}
<div style="float:left; width:356px; height:228px; text-align:center; border:2px red solid; vertical-align:top; margin: 10px;"
	 onclick="window.location.href='{$pilots[i].url}';">
	<div class="owner-title" style="height:70px;"><h1>{$pilots[i].name}</h1></div>
	<div><img src="{$pilots[i].portrait}" style="border:0px" width="128" height="128" alt="{$pilots[i].name}" /></div>
</div>
{/section}
{section name=i loop=$corps}
<div style="float:left; width:356px; height:228px; text-align:center; border:2px red solid; vertical-align:top; margin: 10px;"
	 onclick="window.location.href='{$corps[i].url}';">
	<div class="owner-title" style="height:70px"><h1>{$corps[i].name}</h1></div>
	<div><img src="{$corps[i].portrait}" style="border:0px" width="128" height="128" alt="{$corps[i].name}" /></div>
</div>
{/section}
{section name=i loop=$alliances}
<div style="float:left; width:356px; height:228px; text-align:center; border:2px red solid; vertical-align:top; margin: 10px;"
	 onclick="window.location.href='{$alliances[i].url}';">
	<div class="owner-title" style="height:70px"><h1>{$alliances[i].name}</h1></div>
	<div><img src="{$alliances[i].portrait}" style="border:0px" width="128" height="128" alt="{$alliances[i].name}" /></div>
</div>
{/section}
</div>
<!-- /self.tpl -->