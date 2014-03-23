{strip}
<tr><td width="160"><b>{$opt.descr}:</b></td><td>
<select id="option_{$opt.name}" name="option_{$opt.name}">
{foreach from=$options key=key item=i}
<option value="{$i.value}"{if $i.state} selected="selected"{/if}>{$i.descr}</option>
{/foreach}
</select>
{if $opt.hint}
&nbsp;({$opt.hint})
{/if}
</td></tr>
{/strip}
