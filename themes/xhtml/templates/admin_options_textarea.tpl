{strip}
<tr><td width="160"><b>{$opt.descr}:</b></td><td>
<textarea name="option[{$opt.name}]" id="option[{$opt.name}]" cols="{$options.cols}" rows="{$options.rows}">{$config->get($opt.name)}</textarea>
</td></tr>
{/strip}