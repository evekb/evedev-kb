<!-- admin_troubleshooting -->
<div id="admin">
{foreach from=$sections item="section" key="i"}
	<div class="admin-troubleshooting-section">
		<div class="block-header2">{$i}</div>
{foreach from=$trouble.$i item="module"}
		<div class="admin-troubleshooting-body admin-troubleshooting-{if $module.passed}working{else}error{/if}">
			<img src="{$img_url}/panel/{if $module.passed}working{else}error{/if}.png" alt="" />
			{$module.text}
		</div>
{/foreach}
	</div>
{/foreach}
</div>
<!-- /admin_troubleshooting -->