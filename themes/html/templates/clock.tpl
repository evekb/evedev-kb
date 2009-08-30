<!-- clock.tpl -->
{literal}<script type="text/javascript">
<!--
window.onload=updateClock;
setInterval("updateClock()", 60000 )
function updateClock ( )
{
  var currentTime = new Date ( );
  var currentHours = currentTime.getUTCHours ( );
  var currentMinutes = currentTime.getMinutes ( );

  currentHours = ( currentHours < 10 ? "0" : "" ) + currentHours;
  currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;

  var currentTimeString = currentHours + ":" + currentMinutes;

  document.getElementById("clock").firstChild.nodeValue = currentTimeString;
}

// -->
</script>{/literal}
<table class=kb-table width=150 cellspacing="1">
	<tr><td class=kb-table-header align=center>Eve Time</td></tr>
	<tr class=kb-table-row-even>
		<td>
			<table class=kb-subtable cellspacing=0 border=0 width="100%">
				<tr class=kb-table-row-odd  style="text-align: center; font-weight: bold;">
					<td id="clock">{$clocktime}</td>
				</tr></table></td>
	</tr>
</table><!-- /clock.tpl -->