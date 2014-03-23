<table class="kb-table">
  <tr class="kb-table-header"><td>User</td><td>&nbsp;</td></tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$user item=usr key=name}
  <tr class="{cycle name=ccl}"><td>{$usr}</td><td><form method="post" action="{$kb_host}/?a=admin_roles">
  <input type="hidden" name="a" value="admin_roles"/>
  <input type="hidden" name="role" value="{$role}"/>
  <input type="hidden" name="action" value="assign"/>
  <input type="hidden" name="user" value="{$usr}"/>
  <input type="submit" value="Assign"/></form></td></tr>
{/foreach}
</table>