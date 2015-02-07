{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div class="invtype invship">
	<div>
		<div class="block-header">{$item->item.typeName}</div>
		<table class="kb-table">
			<tr class="kb-table-row-even">
				<td class="description">
					<img class="logo" src="{$shipImage}" alt="{$item->item.typeName}" />
					{$item->item.description|nl2br}
                                        <br/><br/>
                                        {foreach from=$traits key=skillName item=traitsBySkill}
                                            {if $skillName != 'Role'}
                                                <b>{$skillName} bonuses (per skill level):</b>
                                            {else}
                                                <b>Role Bonus:</b>
                                            {/if}
                                            <br/>
                                            {foreach from=$traitsBySkill item=bonusText}
                                                {$bonusText}<br/>
                                            {/foreach}
                                            <br/>
                                        {/foreach}
				</td>
			</tr>
		</table>
	</div>
        <div>
		<div class="block-header">Astronautic</div>
		<table class="kb-table">
			<tr class="{cycle name=ccl}">
				<td class="item-icon"><img src="{$img_url}/items/32_32/icon03_13.png" alt="" /></td>
				<td>Cargo capacity</td>
				<td>{$item->item.capacity} m3</td>
			</tr>
			<tr class="{cycle name=ccl}">
				<td class="item-icon"><img src="{$img_url}/items/32_32/icon02_10.png" alt="" /></td>
				<td>Mass</td>
				<td>{$item->item.mass|number_format} kg</td>
			</tr>
			<tr class="{cycle name=ccl}">
				<td class="item-icon"><img src="{$img_url}/items/32_32/icon02_09.png" alt="" /></td>
				<td>Volume</td>
				<td>{$item->item.volume} m3</td>
			</tr>
			{foreach from=$navigation key=i item=key}
				<tr class="{cycle name=ccl}">
					<td class="item-icon"><img src="{$img_url}/items/32_32/icon{$item->attrib.$key.icon}.png" alt="" /></td>
					<td>{$item->attrib.$key.displayName}</td>
					<td>{$item->attrib.$key.value} {$item->attrib.$key.unit}</td>
				</tr>
			{/foreach}
			<tr class="{cycle name=ccl}">
				<td class="item-icon"><img src="{$img_url}/items/32_32/icon07_12.png" alt="" /></td>
				<td>Baseprice</td>
				<td>{$item->item.basePrice|number_format} ISK</td>
			</tr>
		</table>
	</div>
</div>