<br/>
<div class="kb-kills-header">Battle Summary for {$system}, {$firstts|date_format:"%Y-%m-%d %H:%M"} - {$lastts|date_format:"%H:%M"}</div>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr><td width="49%" valign="top">
<div class="kb-date-header">Friendly ({$friendlycnt})</div>
<br/>

{assign var='loop' value=$pilots_a}
{include file="battle_overview_table.tpl"}

</td><td width="55%" valign="top">
<div class="kb-date-header">Hostile ({$hostilecnt})</div>
<br/>

{assign var='loop' value=$pilots_e}
{include file="battle_overview_table.tpl"}

</td>
</tr>
</table>
<br/>