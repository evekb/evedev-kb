Please select pilots, corps or alliances to be exported.<br/>
<br/>
<form id="search" action="{$kb_host}/?a=admin_kill_export" method="post">
<table class="kb-subtable">
<tr><td>Type:</td><td>Text: (3 letters minimum)</td></tr>
<tr><td><select id="searchtype" name="searchtype"><option value="pilot">Pilot</option>
<option value="corp">Corporation</option>
<option value="alliance">Alliance</option>
</select></td><td><input id="searchphrase" name="searchphrase" type="text" size="30"/></td>
<td><input type="submit" name="submit" value="Search"/></td></tr></table></form>
{if $search}
<table class="kb-table" width="450" cellspacing="1">
<tr class="kb-table-header"><td>Search results</td></tr>
{section name=res loop=$results}
<tr class="kb-table-row-even"><td><a href="{$results[res].link}">{$results[res].descr}</a></td></tr>
{sectionelse}
<tr class="kb-table-row-even"><td>No results.</td></tr>
{/section}
</table>
{/if}
<br/>
<div class="block-header2">Included</div>
<table class="kb-table">
{section name=opt loop=$permissions}
    <tr class="kb-table-header"><td colspan="2">{$permissions[opt].name}</td></tr>
{section name=idx loop=$permissions[opt].list}
    <tr><td width="200"><b>{$permissions[opt].list[idx].text}</b></td><td><a href="{$permissions[opt].list[idx].link}">Delete</a></td></tr>
{/section}
{sectionelse}
<tr><td>None.</td></tr>
{/section}
</table>
{if $permissions}
<br/><a href="{$kb_host}/?a=admin_kill_export&sub=do">Export Mails</a>
{/if}
<br/>
