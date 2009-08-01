<div class="block-header2">Titles</div>
Simply click on the part of the line you want to edit.<br>
  <a href=index.php?a=admin_roles&action=create_title>Create</a>

<table class="kb-table">
  <tr class="kb-table-header">
	<td>Title</td><td>Description</td><td>List of role</td><td>Delete</td>
  </tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$lsttitle item=title key=name}
  <tr class="{cycle name=ccl}">
  <td id="titleName{$title.ttl_id}" style="border:1px solid #CC9900;" valign=top><div  onclick="xajax_editTitleName({$title.ttl_id})">{$title.ttl_name}</div></td>
  <td id="titleDescr{$title.ttl_id}" style="border:1px solid #CC9900;" valign=top><div onclick="xajax_editTitleDescription({$title.ttl_id})">{$title.ttl_descr}</div></td>
  <td id="titleRoles{$title.ttl_id}" style="border:1px solid #CC9900;" valign=top><div  onclick="xajax_editTitleRoles({$title.ttl_id})">{$title.lstRoles}</div></td>
  <td><a href=?a=admin_roles&action=delete&id={$title.ttl_id}>Delete</a></td>
  </tr>
{/foreach}
</table>
<br/><br/>
<div class="block-header2">Softcoded Roles</div>
These roles could be edited if there was a gui for them.<br/><br/>
<table class="kb-table">
  <tr class="kb-table-header"><td>Role</td><td>Description</td></tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$sroles item=role key=name}
  <tr class="{cycle name=ccl}"><td>{$name}</td><td>{$role}</td></tr>
{/foreach}
</table>
<br/><br/>
<div class="block-header2">Hardcoded Roles</div>
You can't edit these roles because they are hardcoded into the core.<br/><br/>
<table class="kb-table">
  <tr class="kb-table-header"><td>Role</td><td>Description</td></tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$hroles item=role key=name}
  <tr class="{cycle name=ccl}"><td>{$name}</td><td>{$role}</td></tr>
{/foreach}
</table>