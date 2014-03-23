{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div class="kl-detail-victim">
	<table class="kb-table">
		<col class="logo" />
		<col class="attribute-name" />
		<col class="attribute-data" />
		<tr class="{cycle name="ccl"}">
			<td rowspan="3" onclick="CCPEVE.showInfo(1377, {$victimExtID})"><img src="{$victimPortrait}" alt="victim" /></td>
			<td>Victim:</td>
			<td><a href="{$victimURL}">{$victimName}</a></td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td>Corp:</td>
			<td><a href="{$victimCorpURL}">{$victimCorpName}</a></td>
		</tr>
		<tr class="{cycle name="ccl"}">
			<td>Alliance:</td>
			<td><a href="{$victimAllianceURL}">{$victimAllianceName}</a></td>
		</tr>
	</table>
</div>