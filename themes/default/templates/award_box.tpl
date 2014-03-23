<!-- award_box.tpl -->
<table class="kb-table awardbox">
	<tr>
		<td class="kb-table-header">{$title}</td>
	</tr>
	<tr class="kb-table-row-even">
		<td>
			<table class="kb-subtable awardbox-top">
				<tr class="kb-table-row-odd">
					<td><img src="{$pilot_portrait}" alt="pilot" height="64" width="64" /></td>
					<td><img src="{$award_img}" alt="award" height="64" width="64" /></td>
				</tr>
			</table>
			<table class="kb-subtable awardbox-list">
				<tr>
					<td class="awardbox-num">1.</td>
					<td colspan="2"><a class="kb-shipclass" href="{$url}">{$name}</a></td>
				</tr>
				<tr>
					<td></td>
					<td>{$bar}</td>
					<td class="awardbox-count">{$cnt}</td>
				</tr>
				{foreach from=$top key=key item=i}
				{strip}
				<tr>
					<td class="awardbox-num">{$key}.</td>
					<td colspan="2"><a class="kb-shipclass" href="{$i.url}">{$i.name}</a></td>
				</tr>
				<tr>
					<td></td>
					<td>{$i.bar}</td>
					<td class="awardbox-count">{$i.cnt}</td>
				</tr>
				{/strip}
				{/foreach}
				<tr>
					<td class="awardbox-comment" colspan="3">({$comment})</td>
				</tr>
			</table></td>
	</tr>
</table>
<!-- /award_box.tpl -->