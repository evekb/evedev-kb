<span class="item-icon" style="position:relative; border: none; height:64px; width:64px; top: -1px">
    <img style="position: absolute; height:64px; width:64px;" src='{$victimShipImage}' alt='{$victimShipName}' title='{$victimShipName}' />
    {if $victimShipTechLevel > 1}
	<img style="position:absolute; height:16px; width:16px;" src='{$img_url}/items/64_64/t{$victimShipTechLevel}.png' title="T{$victimShipTechLevel}" alt="T{$victimShipTechLevel}" />
    {elseif $victimShipIsFaction == 1}
	<img style="position:absolute; height:16px; width:16px;" src='{$img_url}/items/64_64/fac.png' title="Faction" alt="Faction" />
    {/if}
</span>