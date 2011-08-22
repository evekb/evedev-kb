{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div id="kl-detail-victim">
	<table class="kb-table" width="100%" cellpadding="0" cellspacing="1" border="0">
		<tr class="{cycle name="ccl"}">
			<td rowspan="3" style="width:64px;"><img src="{$victimPortrait}" style="border:0px; width:64px; height:64px" alt="victim" /></td>
			<td><b>Victim:</b></td>
			<td><b><a href="{$victimURL}">{$victimName}</a></b></td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td style="width:64px;"><b>Corp:</b></td>
			<td><b><a href="{$victimCorpURL}">{$victimCorpName}</a></b></td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td><b>Alliance:</b></td>
			<td><b><a href="{$victimAllianceURL}">{$victimAllianceName}</a></b></td>
		</tr>
	</table>
</div>