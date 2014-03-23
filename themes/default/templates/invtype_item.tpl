{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div class="invtype invitem">
	<div>
		<div class="block-header">{$item->item.typeName}</div>
		<table class="kb-table">
			<tr class="kb-table-row-even">
				<td class="description">
					<img class="logo" src="{$itemImage}" alt="{$item->item.typeName}"/>
					{$item->item.description|nl2br}
				</td>
			</tr>
		</table>
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
			<tr class="{cycle name=ccl}">
				<td class="item-icon"><img src="{$img_url}/items/32_32/icon07_12.png" alt="" /></td>
				<td>Baseprice</td>
				<td>{$item->item.basePrice|number_format} ISK</td>
			</tr>
		</table>
	</div>
	<div>
		{if $item->attrib}
			<div class="block-header">Attributes</div>
			<table class="kb-table">
				{foreach from=$item->attrib key=i item=key}
					<tr class="{cycle name=ccl}">
						<td class="item-icon"><img src="{$img_url}/items/32_32/icon{$key.icon}.png" alt="" /></td>
						<td>{$key.displayName}</td>
						<td>
							{if $key.unit == 'typeID'}<a href="{$kb_host}/?a=invtype&amp;id={$key.value}">{$item->resolveTypeID($key.value)}</a>
							{elseif $key.unit == 'groupID'}<a href="{$kb_host}/?a=groupdb&amp;id={$key.value}">{$item->resolveGroupID($key.value)}</a>
							{elseif $key.unit == 'attributeID'}{$item->resolveAttributeID($key.value)}
							{else}{$key.value} {$key.unit}{/if}
						</td>
					</tr>
				{/foreach}
			</table>
		{/if}
	</div>
</div>