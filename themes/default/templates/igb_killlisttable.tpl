{section name=day loop=$killlist}
    {if $daybreak}
<br>{"l, F jS"|date:$killlist[day].date}
    {/if}
<table width="100%" cellspacing="1" cellpadding="1" bgcolor="#444444">
    <tr>
        <td bgcolor="#666666" width="182" colspan="2">Ship</td>
        <td bgcolor="#666666" width="200">Victim</td>
        <td bgcolor="#666666" width="200">Final blow</td>
        <td bgcolor="#666666" width="110" align="center">System</td>
        <td bgcolor="#666666" width="80" align="center">Time</td>
    </tr>
    {cycle reset=true print=false name=ccl values="#222222,#000000"}
    {section name=kill loop=$killlist[day].kills}
        {assign var="k" value=$killlist[day].kills[kill]}
    <tr bgcolor="{cycle advance=false name=ccl}">
        <td width="32" align="center"><img src="{$k.victimshipimage}" border="0" width="32" heigth="32"></td>
        <td height="34" width=150 valign="center"><b>{$k.victimshipname}</b><br>{$k.victimshipclass}<img src="{$k.victimshipindicator}" border="0"></td>
        <td><b>{if $k.plext}<a href="showinfo:1373//{$k.plext}">{$k.victim}</a>{else}{$k.victim}{/if}</b><br>{$k.victimcorp|truncate:30}</td>
        <td><b>{if $k.fbplext}<a href="showinfo:1373//{$k.fbplext}">{$k.fb}</a>{else}{$k.fb}{/if}</b><br>{$k.fbcorp|truncate:30}</td>
        <td align="center"><b>{$k.system|truncate:10}</b><br>({$k.systemsecurity|max:0|string_format:"%01.1f"})</td>
        {if $daybreak}
        <td align="center"><b>{$k.timestamp|date_format:"%H:%M"}</b></td>
        {else}
        <td align="center"><b>{$k.timestamp|date_format:"%Y-%m-%d"}<br>{$k.timestamp|date_format:"%H:%M"}</b></td>
        {/if}
    </tr>
    {/section}
</table>
{sectionelse}
<p>No data.
{/section}