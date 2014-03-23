{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table cellpadding="0" cellspacing="1" border="0">
    <tr>
        <td width="360" align="left" valign="top">
            <div class="block-header">{$item->item.typeName}</div>
            <table class="kb-table" width="360" cellpadding="0" cellspacing="1" border="0">
                <tr class="kb-table-row-even">
                    <td>
						<img style="float: left; margin-right: 10px;" src="{$itemImage}" alt="{$item->item.typeName}"/>
						{$item->item.description|nl2br}
					</td>
                </tr>
            </table>
            <div class="block-header">Astronautic</div>
            <table class="kb-table" width="360" border="0" cellspacing="1">
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon03_13.png" border="0" alt="" /></td>
                    <td class="kb-table-cell"><b>Cargo capacity</b></td>
                    <td class="kb-table-cell" align="right">{$item->item.capacity} m3</td>
                </tr>
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon02_10.png" border="0" alt="" /></td>
                    <td class="kb-table-cell"><b>Mass</b></td>
                    <td class="kb-table-cell" align="right">{$item->item.mass|number_format} kg</td>
                </tr>
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon02_09.png" border="0" alt="" /></td>
                    <td class="kb-table-cell"><b>Volume</b></td>
                    <td class="kb-table-cell" align="right">{$item->item.volume} m3</td>
                </tr>
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon07_12.png" border="0" alt="" /></td>
                    <td class="kb-table-cell"><b>Baseprice</b></td>
                    <td class="kb-table-cell" align="right">{$item->item.basePrice|number_format} ISK</td>
                </tr>
            </table>

            </td>
        <td width="50">&nbsp;</td>
        <td align="left" valign="top" width="360">
			{if $item->attrib}
            <div class="block-header">Attributes</div>
            <table class="kb-table" width="360" border="0" cellspacing="1">
            {foreach from=$item->attrib key=i item=key}
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon{$key.icon}.png" border="0" alt="" /></td>
                    <td class="kb-table-cell"><b>{$key.displayName}</b></td>
                    <td class="kb-table-cell" align="right">{if $key.unit == 'typeID'}<a href="{$kb_host}/?a=invtype&amp;id={$key.value}">{$item->resolveTypeID($key.value)}</a>
                                                            {elseif $key.unit == 'groupID'}<a href="{$kb_host}/?a=groupdb&amp;id={$key.value}">{$item->resolveGroupID($key.value)}</a>
                                                            {elseif $key.unit == 'attributeID'}{$item->resolveAttributeID($key.value)}
                                                            {else}{$key.value} {$key.unit}{/if}</td>
                </tr>
            {/foreach}
            </table>
			{/if}
        </td>
    </tr>
</table>