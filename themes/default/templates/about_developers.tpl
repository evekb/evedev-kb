<!-- about_developers -->
<b>Current developers:</b>
<br />{section name=i loop=$current_developer}{$current_developer[i]}{if ($smarty.section.i.index + 1) < count($current_developer)}, {/if}{/section}
<br />
<b>Previous developers:</b>
<br />{section name=i loop=$developer}{$developer[i]}{if ($smarty.section.i.index + 1) < count($developer)}, {/if}{/section}
<br />
<b>Contributors:</b>
<br />{section name=i loop=$contributor}{$contributor[i]}{if ($smarty.section.i.index + 1) < count($contributor)}, {/if}{/section}<br />
<br />
<!-- /about_developers -->