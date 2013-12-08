<!-- detail_history -->
<div class="killlist">
	<div style="float: left; width: 306px; margin-left:10px">

    <table class="kb-table kb-table-rows">
		<thead>
			<tr class="kb-table-header ui-widget-header">
				<th>Year</th>
				<th>Month</th>
				<th>Kills</th>
				<th>Points</th>
				<th>ISK</th>
				<th>Loot</th>
				<th>Losses</th>
				<th>Points</th>
				<th>ISK</th>
				<th>Loot</th>
			</tr>
		</thead>	
{foreach from=$summary item=i}
  {foreach from=$i item=j}

  <tr class="kb-table-row-even">
	<td>{$j.date->format('Y')}</td>
	<td>{$j.date->format('M')}</td>
	<td>{$j.killcount}</td>
	<td>{$j.killpoints}</td>
	<td>{number_format($j.killisk,0)}</td>
	<td>{number_format($j.killloot,0)}</td>
	<td>{$j.losscount}</td>
	<td>{$j.losspoints}</td>
	<td>{number_format($j.lossisk,0)}</td>
	<td>{number_format($j.lossloot,0)}</td>
  </tr>

  {/foreach}
{/foreach}
  </table>
</div>
</div>
<!-- /detail_history -->