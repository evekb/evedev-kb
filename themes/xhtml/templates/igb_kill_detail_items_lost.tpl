<div class="block-header">Ship details</div>
<table class="kb-table" width="398" border="0" cellspacing="1">
{foreach from=$slots item="slot" key="slotindex"}
{* set to true to show empty slots *}
{if $destroyed.$slotindex or $dropped.$slotindex}
	<tr class="kb-table-row-even">
		<td class="item-icon" width="32"><img width="32" height="32" src="{$img_url}/{$slot.img}" alt="{$slot.text}" border="0" /></td>
		<td colspan="2" class="kb-table-cell"><b>{$slot.text}</b> </td>
    {if $config->get('item_values')}
		<td align="center" class="kb-table-cell"><b>Value</b></td>
    {/if}
	</tr>
    {foreach from=$destroyed.$slotindex item="i"}
	<tr class="kb-table-row-odd">
		<td class="item-icon" width="32" height="34" valign="top" onclick="CCPEVE.showInfo({$i.itemID})">{$i.Icon}</td>
		<td class="kb-table-cell">{$i.Name}</td>
		<td width="30" align="center">{$i.Quantity}</td>
        {if $config->get('item_values')}
		<td align="center">{$i.Value}</td>
        {/if}
	</tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
	<tr class="kb-table-row-even">
	  <td height="34" colspan="4" valign="top" align="right"><form method="post" action=""><table><tr>
		<td>
			<div align="right">
				Current single Item Value:
				<input name="IID" value="{$i.itemID}" type="hidden" />
				<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.single_unit}" size="6" />
			</div></td>
		<td height="34" valign="top"><input type="submit" name="submit" value="UpdateValue" class="comment-button" /></td>
	  </tr></table></form></td>
	</tr>
        {/if}
        {if $admin and $i.slotID < 4 and $fixSlot}
	<tr class="kb-table-row-even">
	  <form method="post" action="">
		<td height="34" colspan="3" valign="top">
			<div align="right">
				Fix slot:
				<input name="IID" value="{$i.itemID}" type="hidden" />
				<input name="KID" value="{$killID}" type="hidden" />
				<input name="TYPE" value="destroyed" type="hidden" />
				<input name="OLDSLOT" value="{$i.slotID}" type="hidden" />
				<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6" />
			</div>
		<td height="34" valign="top"><input type="submit" name="submit" value="UpdateSlot" class="comment-button" /></td>
	  </form>
	</tr>
        {/if}
    {/foreach}
    {foreach from=$dropped.$slotindex item="i"}
	<tr class="kb-table-row-odd" style="background-color: #006000;">
		<td style="border: 1px solid green;" width="32" height="34" valign="top" onclick="CCPEVE.showInfo({$i.itemID})">{$i.Icon}</td>
		<td class="kb-table-cell">{$i.Name}</td>
		<td width="30" align="center">{$i.Quantity}</td>
        {if $config->get('item_values')}
		<td align="center">{$i.Value}</td>
        {/if}
	</tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
	<tr class="kb-table-row-even">
	  <td height="34" colspan="4" valign="top" align="right"><form method="post" action=""><table><tr>
		<td>
			<div align="right">
				Current single Item Value:
				<input name="IID" value="{$i.itemID}" type="hidden" />
				<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.single_unit}" size="8" />
			</div></td>
		<td height="34" valign="top"><input type="submit" name="submit" value="UpdateValue" class="comment-button" /></td>
	  </tr></table></form></td>
	</tr>
        {/if}
	{if $admin and $i.slotID < 4 and $fixSlot}
	<tr class="kb-table-row-even">
	  <form method="post" action="">
		<td height="34" colspan="3" valign="top">
			<div align="right">
				Fix slot:
				<input name="IID" value="{$i.itemID}" type="hidden" />
				<input name="KID" value="{$killID}" type="hidden" />
				<input name="TYPE" value="dropped" type="hidden" />
				<input name="OLDSLOT" value="{$i.slotID}" type="hidden" />
				<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6" />
			</div>
		<td height="34" valign="top"><input type="submit" name="submit" value="UpdateSlot" class="comment-button" /></td>
	  </form>
	</tr>
        {/if}
    {/foreach}
{/if}
{/foreach}
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{if $item_values}
	<tr class="{cycle name="ccl"}">
		<td align="right" colspan="3"><b>Damage taken:</b></td>
		<td align="right">{$victimDamageTaken|number_format}</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td colspan="3"><div align="right"><strong>Total Module Loss:</strong></div></td>
		<td align="right">{$itemValue}</td>
	</tr>
	<tr class="{cycle name="ccl"}" style="background-color: {$dropped_colour};">
		<td style="border: 1px solid {$dropped_colour};" colspan="3"><div align="right"><strong>Total Module Drop:</strong></div></td>
		<td style="border: 1px solid green;" align="right">{$dropValue}</td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td colspan="3"><div align="right"><strong>Ship Loss:</strong></div></td>
		<td align="right">{$shipValue}</td>
	</tr>
{if $admin and $config->get('item_values') and !$fixSlot}
	<tr class="kb-table-row-even">
	  <td height="34" colspan="4" valign="top" align="right"><form method="post" action=""><table><tr>
		<td>
			<div align="right">
				Current Ship Value:
				<input name="SID" value="{$ship->getExternalID()}" type="hidden" />
				<input name="{$ship->getExternalID()}" type="text" class="comment-button" value="{$ship->getPrice()}" size="10" />
			</div></td>
		<td height="34" valign="top"><input type="submit" name="submit" value="UpdateValue" class="comment-button" /></td>
	  </tr></table></form></td>
	</tr>
{/if}
	<tr class="{cycle name="ccl"}" style="background-color: #600000;">
		<td style="border: 1px solid #600000;" colspan="3"><div align="right"><strong>Total Loss:</strong></div></td>
		<td style="border: 1px solid #C00000;" align="right">{$totalLoss}</td>
	</tr>
{/if}
</table>