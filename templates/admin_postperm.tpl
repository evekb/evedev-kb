<b>Note:</b> This permission will be set to be additional to your corp/alliance id you've set up in the config.<br/>
<br/>
<form id="search" action="?a=admin_postperm" method="post">
<table class="kb-subtable">
  <tr><td>Type:</td><td>Text: (3 letters minimum)</td></tr>
  <tr><td><select id="searchtype" name="searchtype"><option value="pilot">Pilot</option>
    <option value="corp">Corporation</option>
    <option value="alliance">Alliance</option>
    </select></td><td><input id="searchphrase" name="searchphrase" type="text" size="30"/></td>
    <td><input type="submit" name="submit" value="Search"/></td>
  </tr>
</table>
</form>
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
{if $config->get('post_permission')=='all'}
Authorization checking is disabled, people still need to know the postpassword, though.<br/>
<a href="?a=admin_postperm&authall=0">Enable authorization checking.</a>
{else}
<div class="block-header2">Granted Permissions</div>
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
<br/>
<a href="?a=admin_postperm&authall=1">Disable authorization checking (and delete all granted permissions).</a>
{/if}