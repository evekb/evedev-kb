<!-- award_box.tpl -->&nbsp;
<table class=kb-table width=150 cellspacing="1">
	<tr>
		<td class=kb-table-header align=center>{$title}</td>
	</tr>
	<tr class=kb-table-row-even>
		<td><table class=kb-subtable cellspacing=0 border=0 width="100%">
				<tr class=kb-table-row-odd>
					<td align=left><img src="{$pilot_portrait}" alt="pilot" height="64" width="64"></td>
					<td align=center><img src="{$award_img}" alt="award" height="64" width="64"></td>
				</tr>
			</table>
			<table class=kb-subtable cellspacing=0 border=0 width="100%">
				<tr>
					<td width=15><b>1.</b></td>
					<td align=left colspan=2><a class=kb-shipclass href="{$url}">{$name}</a></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td align=left>{$bar}</td>
					<td align=right><b>{$cnt}</b></td>
					<td></td>
				</tr>
				{foreach from=$top key=key item=i}
				{strip}
				<tr>
					<td><b>{$key}.</b></td>
					<td colspan=2 align="left"><a class=kb-shipclass href="{$i.url}">{$i.name}</a></td>
				</tr>
				<tr>
					<td></td>
					<td align=left>{$i.bar}</td>
					<td align=right><b>{$i.cnt}</b></td>
				</tr>
				{/strip}
				{/foreach}
				<tr>
					<td colspan=3 align=center>({$comment})</td>
				</tr>
			</table></td>
	</tr>
</table>
<!-- /award_box.tpl -->