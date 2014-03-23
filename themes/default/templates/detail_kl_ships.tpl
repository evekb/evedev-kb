<!-- detail_kl_ships -->
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div class="ships-destroyed">
	<div class="block-header2">{$title}</div>
{section name=ship loop=$ships}
        {assign var="s" value=$ships[ship]}
		<div class="ship-destroyed" style="float:left; margin: 20px">
		<div class="block-header">{$s.name}</div>
			{$s.table}
		</div>
{/section}
</div>
<!-- /detail_kl_ships -->