<!-- about_mods -->
<div id="about_mods">
	<div class="block-header2">Mods used:</div>
{foreach from=$mods item=mod}
	<div class="modinfo">
		<div class="mod_name">{$mod.name}</div>
		<div class="mod_abstract">{$mod.abstract}</div>
		<div class="mod_about">{$mod.about}</div>
	</div>
{/foreach}
</div>
<!-- /about_mods -->