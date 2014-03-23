{strip}
<tr><td width="160"><b>{$opt.descr}:</b></td><td>
<input type="checkbox" id="option_{$opt.name}" name="option_{$opt.name}"
{if $config->get($opt.name)} checked="checked"{/if} />
{if $opt.hint}
&nbsp;({$opt.hint})
{/if}
</td></tr>
{/strip}
