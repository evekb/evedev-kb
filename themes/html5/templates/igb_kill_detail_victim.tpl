{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table class="kb-table" width="360" cellpadding="0" cellspacing="1" border="0">
	<tr class="{cycle name="ccl"}">
		<td rowspan="3" width="64" onclick="CCPEVE.showInfo(1377, {$victimExtID})"><img src="{$victimPortrait}" width="64" height="64" alt="victim" /></td>
		<td width="64"><b>Victim:</b></td>
		<td><b><a href="{$victimURL}">{$victimName}</a></b></td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td width="64"><b>Corp:</b></td>
		<td><b><a href="{$victimCorpURL}">{$victimCorpName}</a></b></td>
	</tr>
	<tr class="{cycle name="ccl"}">
		<td width="64"><b>Alliance:</b></td>
		<td><b><a href="{$victimAllianceURL}">{$victimAllianceName}</a></b></td>
	</tr>
</table>