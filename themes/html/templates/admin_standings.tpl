<form id="search" action="?a=admin_standings" method="post">
<table class="kb-subtable">
  <tr><td>Type:</td><td>Text: (3 letters minimum)</td></tr>
  <tr><td><select id="searchtype" name="searchtype">
    <option value="alliance">Alliance</option>
    <option value="corp">Corporation</option>
    </select></td><td><input id="searchphrase" name="searchphrase" type="text" size="30"/></td>
    <td><input type="submit" name="submit" value="Search"/></td>
  </tr>
</table>
</form>
{if $search}
<table class="kb-table" cellspacing="1">
  <tr class="kb-table-header"><td colspan="5">Search results</td></tr>
  <tr class="kb-table-header"><td>Typ</td><td>Result</td><td>Standing</td><td>Comment</td><td>&nbsp;</td></tr>
{section name=res loop=$results}
  <tr class="kb-table-row-even">
    <td><form id="search" action="?a=admin_standings" method="post">
      <input type="hidden" name="sta_id" value="{$results[res].link}">
      {$results[res].typ}</td><td>{$results[res].descr}</td>
    <td align="center"><input type="text" value="" size="3" name="standing"></td>
    <td align="center"><input type="text" value="" size="20" name="comment"></td>
    <td align="center"><input type="submit" name="submit" value="Add"></form></td>
  </tr>
{sectionelse}
  <tr class="kb-table-row-even"><td>No results.</td></tr>
{/section}
</table>
{/if}
<br/>
<!--
<script language="javascript">
{literal}
function geninput(object,id,value,orgval)
{
    if (document.getElementById('ship_'+id))
    {
        return;
    }
    object.innerHTML = '<input type="text" id="ship_'+id+'" name="ship['+id+']" value="'+value+'" onblur="checkinput(this,\''+value+'\',\''+orgval+'\',\''+id+'\');">';
    document.getElementById('ship_'+id).focus();
}

function checkinput(object,value,oldvalue,id)
{
    if (object.value == value)
    {
        document.getElementById('tbrid_'+id).innerHTML = oldvalue;
    }
}
{/literal}
</script>
-->
<div class="block-header2">Standings</div>
<form id="search" action="?a=admin_standings" method="POST">
<table class="kb-table">
{section name=opt loop=$standings}
  <tr class="kb-table-header"><td colspan="5">{$standings[opt].name}</td></tr>
  <tr class="kb-table-header"><td>Name</td><td>Standing</td><td>Comment</td><td>&nbsp;</td></tr>
{section name=idx loop=$standings[opt].list}
  <tr class="kb-table-row-even">
    <td><b>{$standings[opt].list[idx].text}</b>{$standings[opt].list[idx].descr}</td>
    <td align="right">{$standings[opt].list[idx].value}</td>
    <td align="center">{$standings[opt].list[idx].comment}</td>
    <td><a href="{$standings[opt].list[idx].link}">Delete</a></td>
<!--
    <td width="180" align="right" id="tbrid_{$standings[opt].list[idx].id}" onClick="geninput(this,'{$standings[opt].list[idx].id}','{$standings[opt].list[idx].value}','{$standings[opt].list[idx].value}');">{$standings[opt].list[idx].value}</td>
-->
{/section}
{sectionelse}
  <tr><td>None.</td></tr>
{/section}
</table>
<input type="submit" name="submit" value="Save">
</form>