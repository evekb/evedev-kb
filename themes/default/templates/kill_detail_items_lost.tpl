<div class="kl-detail-shipdetails">
	<div class="block-header">Ship details</div>
	<table class="kb-table">
{foreach from=$slots item="slot" key="slotindex"}
{* set to true to show empty slots *}
{if $destroyed.$slotindex or $dropped.$slotindex}
		<tr class="kb-table-row-even">
			<th class="item-icon"><img src="{$img_url}/{$slot.img}" alt="{$slot.text}" /></th>
			<th colspan="2"><b>{$slot.text}</b> </th>
    {if $config->get('item_values')}
			<th><b>Current Value</b></th>
    {/if}
		</tr>
    {foreach from=$destroyed.$slotindex item="i"}
		<tr class="kb-table-row-odd">
			<td class="item-icon"><a href="{$i.url}">{$i.Icon}</a></td>
			<td>{$i.Name}</td>
			<td>{$i.Quantity}</td>
        {if $config->get('item_values')}
			<td>{$i.Value}</td>
        {/if}
		</tr>
        {if $admin && $config->get('item_values') && !$fixSlot && !$i.bpc}
		<tr class="kb-table-row-even">
			<td colspan="4">
				<form method="post" action="{$formURL}">
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
		<tr class="kb-table-row-even">
			<td colspan="4">
				<form method="post" action="{$formURL}">
					<div style="float:right;">
						<input type="submit" name="submit" value="UpdateSlot" class="comment-button" />
					</div>
					<div style="float:right;">
				Fix slot:
						<input name="IID" value="{$i.itemID}" type="hidden" />
						<input name="KID" value="{$killID}" type="hidden" />
						<input name="TYPE" value="destroyed" type="hidden" />
						<input name="OLDSLOT" value="{$i.slotID}" type="hidden" />
						<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6" />
					</div>
				</form>
			</td>
		</tr>
        {/if}
    {/foreach}
    {foreach from=$dropped.$slotindex item="i"}
		<tr class="kb-table-row-odd dropped">
			<td class="item-icon"><a href="{$i.url}">{$i.Icon}</a></td>
			<td>{$i.Name}</td>
			<td>{$i.Quantity}</td>
        {if $config->get('item_values')}
			<td>{$i.Value}</td>
        {/if}
		</tr>
        {if $admin && $config->get('item_values') && !$fixSlot && !$i.bpc}
		<tr class="kb-table-row-even">
			<td colspan="4">
				<form method="post" action="{$formURL}">
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
		<tr class="kb-table-row-even">
			<td colspan="4">
				<form method="post" action="{$formURL}">
					<div style="float:right;">
						<input type="submit" name="submit" value="UpdateSlot" class="comment-button" />
					</div>
					<div style="float:right;">
				Fix slot:
						<input name="IID" value="{$i.itemID}" type="hidden" />
						<input name="KID" value="{$killID}" type="hidden" />
						<input name="TYPE" value="dropped" type="hidden" />
						<input name="OLDSLOT" value="{$i.slotID}" type="hidden" />
						<input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6" />
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
		<tr class="{cycle name="ccl"} summary itemloss">
			<td colspan="3"><div>Total Module Loss:</div></td>
			<td>{$itemValue}</td>
		</tr>
		<tr class="{cycle name="ccl"} summary itemdrop">
			<td colspan="3"><div>Total Module Drop:</div></td>
			<td>{$dropValue}</td>
		</tr>
		{if $BPOValue > 0}
			<tr class="{cycle name="ccl"} summary bpo">
				<td colspan="3">
					<div>Blueprints (not counted in weekly total):</div>
				</td>
				<td>{$BPOValue}</td>
			</tr>
		{/if}
		<tr class="{cycle name="ccl"} summary shiploss">
			<td colspan="3"><div>Ship Loss:</div></td>
			<td>{$shipValue}</td>
		</tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
		<tr class="kb-table-row-even">
			<td colspan="4" style="vertical-align:top; text-align:right">
				<form method="post" action="{$formURL}">
					<div style="float:right">
						<input type="submit" name="submit" value="UpdateValue" class="comment-button" />
					</div>
					<div style="float:right; margin-right: 5px">
				Current Ship Value:
						<input name="SID" value="{$ship->getExternalID()}" type="hidden" />
						<input name="{$ship->getExternalID()}" type="text" class="comment-button" value="{$ship->getPrice()}" size="10" />
					</div>
				</form>
			</td>
		</tr>
        {/if}
		<tr class="{cycle name="ccl"} summary totalloss">
			<td colspan="3">Total Loss at current prices:</td>
			<td>{$totalLoss}</td>
		</tr>
{/if}
	</table>
</div>