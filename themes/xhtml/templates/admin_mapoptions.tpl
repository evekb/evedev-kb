<form id="options" name="options" method="post" action="{$kb_host}/?a=admin_mapoptions">
<b>Note:</b> You don't need to set colors if you don't want to overwrite the default.<br/>
The other settings do have an effect if they're touched though.<br/>
<b>Usage:</b> Input into a RGB-colorfield set of 3 values seperated by ','. If you want to have those values treated as hex then put an x in front of each.
You can omit the 'x' if you're using A-F in the number. It also accepts html codes in the format '#RRGGBB'. If you want to turn back to the default color simply delete everything in the particular editbox.<br/>
<b>Example:</b> "34,x13,xff" will be evaluated as rgb(34,19,255).
<br/>
{section name=id loop=$options}
<div class="block-header2">{$options[id].name}</div>
<table class="kb-subtable">
    {section name=opt loop=$options[id].option}
        {assign var="o" value=$options[id].option[opt]}
        <tr><td width="120"><b>{$o.descr}:</b></td><td><input type="checkbox" name="{$o.name}" id="{$o.name}"{if $config->get($o.name)} checked="checked"{/if} /></td></tr>
    {/section}
    {section name=opt loop=$options[id].color}
        {assign var="o" value=$options[id].color[opt]}
        <tr><td width="120"><b>{$o.descr}:</b></td><td><input type="text" name="{$o.name}" id="{$o.name}"{if $config->get($o.name)} value="{$config->get($o.name)}"{/if} /></td></tr>
    {/section}
</table>
{/section}
<div class="block-header2">Save changes</div>
<table class="kb-subtable">
<tr><td width="120"></td><td><input type="submit" name="submit" value="Save" /></td></tr>
</table>
</form>
