<div class="block-header2">Hardcoded roles</div>
<table class="kb-table">
  <tr class="kb-table-header"><td>Role</td><td>Description</td><td>&nbsp;</td></tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$hroles item=role key=name}
  <tr class="{cycle name=ccl}"><td>{$name}</td><td>{$role}</td><td><form method="post" action="{$kb_host}/?a=admin_roles">
  <input type="hidden" name="a" value="admin_roles"/>
  <input type="hidden" name="role" value="{$name}"/>
  <input type="hidden" name="action" value="search"/>
  <input type="text" name="search" size="8"/>&nbsp;
  <input type="submit" value="Assign to"/></form></td></tr>
{/foreach}
</table>
<br/><br/>
<div class="block-header2">Softcoded roles</div>
<table class="kb-table">
  <tr class="kb-table-header"><td>Role</td><td>Description</td><td>&nbsp;</td></tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$sroles item=role key=name}
  <tr class="{cycle name=ccl}"><td>{$name}</td><td>{$role}</td><td><form method="post" action="{$kb_host}/?a=admin_roles">
  <input type="hidden" name="a" value="admin_roles"/>
  <input type="hidden" name="role" value="{$role}"/>
  <input type="text" name="search" size="8"/>&nbsp;
  <input type="submit" value="Assign to"/></form></td></tr>
{/foreach}
  <tr class="{cycle name=ccl}"><td colspan="3" align="center"><form method="post" action="{$kb_host}/?a=admin_roles">
  <input type="hidden" name="a" value="admin_roles"/>
  <input type="hidden" name="action" value="create"/><input type="submit" value="Create Role"></form></td></tr>
</table>