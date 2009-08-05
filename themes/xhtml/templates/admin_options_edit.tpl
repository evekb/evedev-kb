{strip}
<tr><td width="160"><b>{$opt.descr}:</b></td><td>
<input type="edit" id="option[{$opt.name}]" name="option[{$opt.name}]"
value="{$config->get($opt.name)}" size="{$options.size}" maxlength="{$options.maxlength}">
{if $opt.hint}
&nbsp;({$opt.hint})
{/if}
</td></tr>
{/strip}
