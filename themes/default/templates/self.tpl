<!-- self.tpl -->
<div id="owners">
{section name=i loop=$pilots}
<div onclick="window.location.href='{$pilots[i].url}';">
	<div class="owner-title owner-pilot">{$pilots[i].name}</div>
	<img src="{$pilots[i].portrait}" alt="{$pilots[i].name}" />
</div>
{/section}
{section name=i loop=$corps}
<div onclick="window.location.href='{$corps[i].url}';">
	<div class="owner-title owner-corp">{$corps[i].name}</div>
	<img src="{$corps[i].portrait}" alt="{$corps[i].name}" />
</div>
{/section}
{section name=i loop=$alliances}
<div onclick="window.location.href='{$alliances[i].url}';">
	<div class="owner-title owner-alliance">{$alliances[i].name}</div>
	<img src="{$alliances[i].portrait}" alt="{$alliances[i].name}" />
</div>
{/section}
</div>
<!-- /self.tpl -->