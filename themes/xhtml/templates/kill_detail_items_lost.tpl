<div id="kl-detail-shipdetails">
	<div class="block-header">Ship details</div>
	<table class="kb-table" width="100%" border="0" cellspacing="1">
{foreach from=$slots item="slot" key="slotindex"}
{* set to true to show empty slots *}
{if $destroyed.$slotindex or $dropped.$slotindex}
		<tr class="kb-table-row-even">
			<td class="item-icon" style="width:32px"><img style="width:32px; height:32px; border:0px" src="{$img_url}/{$slot.img}" alt="{$slot.text}" /></td>
			<td colspan="2" class="kb-table-cell"><b>{$slot.text}</b> </td>
    {if $config->get('item_values')}
			<td align="center" class="kb-table-cell"><b>Current Value</b></td>
    {/if}
		</tr>
    {foreach from=$destroyed.$slotindex item="i"}
		<tr class="kb-table-row-odd" style="height:32px;">
			<td class="item-icon" style="width:32px; vertical-align:top"><a href="{$kb_host}/?a=invtype&amp;id={$i.itemID}">{$i.Icon}</a></td>
			<td class="kb-table-cell">{$i.Name}</td>
			<td style="width:30px; text-align:center">{$i.Quantity}</td>
        {if $config->get('item_values')}
			<td align="center">{$i.Value}</td>
        {/if}
		</tr>
        {if $admin && $config->get('item_values') && !$fixSlot && !$i.bpc}
		<tr class="kb-table-row-even" style="height:34px;">
			<td colspan="4" style="vertical-align:top; text-align:right">
				<form method="post" action="">
					<div style="float:right">
						<input type="submit" name="submit" value="UpdateValue" class="comment-button" />
					</div>
					<div style="float:right; margin-right: 5px">
				Current single Item Value:
						<input name="IID" value="{$i.itemID}" type="hidden" />
						<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.single_unit}" size="6" />
					</div>
				</form>
			</td>
		</tr>
        {/if}
        {if $admin and $i.slotID < 4 and $fixSlot}
		<tr class="kb-table-row-even" style="height:34px">
			<td colspan="3" style="vertical-align:top">
				<form method="post" action="">
					<div style="text-align:right">
				Fix slot:
						<input name="IID" value="{$i.itemID}" type="hidden" />
						<input name="KID" value="{$killID}" type="hidden" />
						<input name="TYPE" value="destroyed" type="hidden" />
						<input name="OLDSLOT" value="{$i.slotID}" type="hidden" />
						<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6" />
					</div>
					<div>
						<input type="submit" name="submit" value="UpdateSlot" class="comment-button" />
					</div>
				</form>
			</td>
		</tr>
        {/if}
    {/foreach}
    {foreach from=$dropped.$slotindex item="i"}
		<tr class="kb-table-row-odd" style="background-color: #006000; height:34px">
			<td style="border: 1px solid green; width:32px; vertical-align:top"><a href="{$kb_host}/?a=invtype&amp;id={$i.itemID}">{$i.Icon}</a></td>
			<td class="kb-table-cell">{$i.Name}</td>
			<td style="width:30px; text-align:center">{$i.Quantity}</td>
        {if $config->get('item_values')}
			<td align="center">{$i.Value}</td>
        {/if}
		</tr>
        {if $admin && $config->get('item_values') && !$fixSlot && !$i.bpc}
		<tr class="kb-table-row-even" style="height:34px">
			<td colspan="4" style="vertical-align:top; text-align:right">
				<form method="post" action="">
					<div style="float:right">
						<input type="submit" name="submit" value="UpdateValue" class="comment-button" />
					</div>
					<div style="float:right; margin-right: 5px">
				Current single Item Value:
						<input name="IID" value="{$i.itemID}" type="hidden" />
						<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.single_unit}" size="8" />
					</div>
				</form>
			</td>
		</tr>
        {/if}
	{if $admin and $i.slotID < 4 and $fixSlot}
		<tr class="kb-table-row-even" style="height:34px;">
			<td colspan="3" valign="top">
				<form method="post" action="">
					<div style="text-align:right">
				Fix slot:
						<input name="IID" value="{$i.itemID}" type="hidden" />
						<input name="KID" value="{$killID}" type="hidden" />
						<input name="TYPE" value="dropped" type="hidden" />
						<input name="OLDSLOT" value="{$i.slotID}" type="hidden" />
						<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6" />
					</div>
					<div>
						<input type="submit" name="submit" value="UpdateSlot" class="comment-button" />
					</div>
				</form>
			</td>
		</tr>
        {/if}
    {/foreach}
{/if}
{/foreach}
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{if $item_values}
		<tr class="{cycle name="ccl"}">
			<td colspan="3"><div style="text-align:right"><strong>Total Module Loss:</strong></div></td>
			<td align="right">{$itemValue}</td>
		</tr>
		<tr class="{cycle name="ccl"}" style="background-color: #006000;">
			<td style="border: 1px solid #006000;" colspan="3"><div style="text-align:right"><strong>Total Module Drop:</strong></div></td>
			<td style="border: 1px solid green;" align="right">{$dropValue}</td>
		</tr>
	{if $BPOValue > 0}<tr class="{cycle name="ccl"}" style="background-color: {$dropped_colour};">
			<td style="border: 1px solid {$dropped_colour};" colspan="3"><div style="text-align:right"><strong>Blueprints (not counted in weekly total):</strong></div></td>
			<td style="border: 1px solid green;" align="right">{$BPOValue}</td>
		</tr>{/if}
		<tr class="{cycle name="ccl"}">
			<td colspan="3"><div style="text-align:right"><strong>Ship Loss:</strong></div></td>
			<td align="right">{$shipValue}</td>
		</tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
		<tr class="kb-table-row-even" style="height:34px">
			<td colspan="4" style="vertical-align:top; text-align:right">
				<form method="post" action="">
					<table>
						<tr style="height:34px;">
							<td>
								<div style="text-align:right">
				Current Ship Value:
									<input name="SID" value="{$ship->getExternalID()}" type="hidden" />
									<input name="{$ship->getExternalID()}" type="text" class="comment-button" value="{$ship->getPrice()}" size="10" />
								</div>
							</td>
							<td style="vertical-align:top">
								<input type="submit" name="submit" value="UpdateValue" class="comment-button" />
							</td>
						</tr>
					</table>
				</form>
			</td>
		</tr>
        {/if}
		<tr class="{cycle name="ccl"}" style="background-color: #600000;">
			<td style="border: 1px solid #600000;" colspan="3"><div style="text-align:right"><strong>Total Loss at current prices:</strong></div></td>
			<td style="border: 1px solid #C00000; text-align:right">{$totalLoss}</td>
		</tr>
{/if}
	</table>
</div>
