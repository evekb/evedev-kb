<form id='search' action="{$kb_host}/?a=search" method='post'>
	<table class='kb-subtable'>
		<tr>
			<td>Type:</td>
			<td colspan="2">Text: (3 letters minimum)</td>
		</tr>
		<tr>
			<td>
				<select id='searchtype' name='searchtype' onchange="searchBuffer.bufferText='';if(this.value.length > 2) searchBuffer.modified('searchphrase');">
					<option value='pilot'>Pilot</option>
					<option value='corp'>Corporation</option>
					<option value='alliance'>Alliance</option>
					<option value='system'>System</option>
					<option value='item'>Items</option>
				</select>
			</td>
			<td>
				<input id='searchphrase' name='searchphrase' type='text' size='30' onkeyup="if(this.value.length > 2) searchBuffer.modified('searchphrase');"/>
			</td>
			<td>
				<input type='submit' name='submit' value='Search' />
			</td>
		</tr>
	</table>
</form>
