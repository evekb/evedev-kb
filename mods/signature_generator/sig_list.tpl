<table class="navigation" width="100%" border="0" cellspacing="1">
<tr class="kb-table-header"><td>Available Signatures</td></tr>
{section name=tpl loop=$signatures}
<tr class="kb-table-row-even">
<td align="center">&nbsp;<br/><img src="{$kb_host}?a=sig&i={$pilot}&s={$signatures[tpl]}"><br/>&nbsp;</td>
</tr><tr class="kb-table-row-odd">
<td align="center">
<textarea cols="80" rows="2">[url={$kb_host}?a=pilot_detail&plt_id={$pilot}]
[img]{$kb_host}?a=sig&i={$pilot}&s={$signatures[tpl]}[/img][/url]</textarea>
<br/>&nbsp;</td>
</tr>
<tr class="kb-table-row-odd">
<td align="center">This is the code you can try for phpBB<br/>
<textarea cols="80" rows="2">[url={$kb_host}?a=pilot_detail&plt_id={$pilot}]
[img]{$kb_host}/sig.php/{$pilot}/{$signatures[tpl]}/signature.jpg[/img][/url]</textarea>
<br/>&nbsp;</td>
</tr>
{/section}
</table>